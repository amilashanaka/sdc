# ============================================================================
# FIXED daq.py - COMPATIBLE WITH anchor.v v1.2 (NO OVERRUN COUNTER)
# ============================================================================
"""
Name:    daq.py
Version: 1.2
Date:    06-01-2026
By:      Don Gunasinha, Spicer Consulting 
Note:    PYNQ-based DAQ class with simplified FSM for data acquisition.
         Compatible with anchor.v v1.2
         Optimized version with parallel channel reading to eliminate discontinuities
"""

from enum import Enum, auto
import numpy as np
import time
from pynq import Overlay
from concurrent.futures import ThreadPoolExecutor
from threading import Lock, Event, Thread
from queue import Queue


class DaqError(Exception):
    """Custom exception for DAQ errors."""


class DaqState(Enum):
    IDLE = auto()
    READ_ALL_CHANNELS = auto()
    PROCESS = auto()
    BUILD = auto()
    DONE = auto()


class Daq:
    # Background thread flags
    _fsm_thread = False
    _fsm_thread_running = False
    
    # Constants
    DEBUG = False
    MAX_CHANNELS = 16
    NUM_CHANNELS = 16 if DEBUG else 8  # Number of channels (active) to read at once (default: 8)   
    TOTAL_SAMPLES = 0
    DATA_WIDTH = 0
    VREF = 1.5
    ADC_RESOLUTION = 1.0
    DECIM_FACTOR = 20

    
    VALID_DECIMATION = [1, 2, 4, 5, 10, 20, 25, 50, 100]

    # Register addresses (must match Verilog)
    BUFFER_SELECT_ADDR = 0x00
    BUFFER_0_READY_ADDR = 0x04
    BUFFER_1_READY_ADDR = 0x08
    DATA_START_ADDR = 0x0C  # Start of sample data

    def __init__(self, bitstream="bram.bit", force_reboot=False, decim_factors=DECIM_FACTOR, num_channels=NUM_CHANNELS, total_samples=TOTAL_SAMPLES, data_width=16, vref=1.5, adc_resolution=1.0):

        # Runtime configuration
        self.NUM_CHANNELS = num_channels
        self.TOTAL_SAMPLES = total_samples
        self.DATA_WIDTH = data_width
        self.VREF = vref
        self.ADC_RESOLUTION = adc_resolution
        self.DECIM_FACTORS = decim_factors

        # Thread management - use for parallel channel reading
        self.executor = ThreadPoolExecutor(max_workers=self.NUM_CHANNELS, thread_name_prefix='daq')
        self.buffer_lock = Lock()
        self.process_queue = Queue(maxsize=2)
        self.stop_processing = Event()

        # Double buffering for ping-pong operation
        self.capture_buffers = [
            np.zeros((self.NUM_CHANNELS, self.TOTAL_SAMPLES), dtype=np.int16),
            np.zeros((self.NUM_CHANNELS, self.TOTAL_SAMPLES), dtype=np.int16)
        ]
        self.active_capture_buffer = 0
        self.read_buffer = 0
        
        # Per-channel tracking for parallel reads
        self.channel_last_buffer = [-1] * self.NUM_CHANNELS

        # Load FPGA bitstream and initialize IP blocks
        try:
            self.ol = Overlay(bitstream)
            try:
                self.ol.download()
            except Exception:
                pass

            # Initialize channel anchors and filter controls
            self.anchors = [getattr(self.ol, f'adc_{i}').anchor_0 for i in range(self.NUM_CHANNELS)]
            self.filter_ctrls = [getattr(self.ol, f'adc_{i}').filter_ctrl_0 for i in range(self.NUM_CHANNELS)]

        except Exception as e:
            raise DaqError(f"Failed to load bitstream or access IP: {e}")

        # Output buffers
        self.adc_buffer = np.zeros((self.NUM_CHANNELS, self.TOTAL_SAMPLES), dtype=np.int16)
        self.daq_buffer = b""

        # Pre-computed constants for voltage scaling
        self.volt_scale = np.float32(self.VREF / self.ADC_RESOLUTION)

        # Build static output buffer components
        self._init_static_buffers()

        # State machine internals
        self._state = DaqState.IDLE
        self._current_ch = 0
        self._buffer_idx = self.active_capture_buffer
        self._last_read_buffer = -1  # Track last buffer read for ping-pong

        # Initialize hardware
        self._init_hardware()

        # Set decimation factors
        if isinstance(decim_factors, (int, np.integer)):
            decim_factors = [decim_factors] * self.NUM_CHANNELS
        self.set_decimation_factor(decim_factors)

    def _init_static_buffers(self):
        """Initialize static portions of output buffer"""
        # Temperature/humidity header
        self.temprh_array = np.zeros(4, dtype='>i2')
        self.temprh_array[0] = 100
        self.temprh_array[1] = 100
        self.temprh_array[2] = 0
        self.temprh_array[3] = 100
        self.temprh_bytes = self.temprh_array.tobytes()

        # Size array for output formatting
        self.daq_size_array = np.zeros(10, dtype='>i2')
        self.daq_size_array[0] = 8
        sample_size = self.TOTAL_SAMPLES * 2
        for chan in range(1, self.NUM_CHANNELS + 1):
            self.daq_size_array[chan] = sample_size
        for chan in range(self.NUM_CHANNELS + 1, 10):
            self.daq_size_array[chan] = 126
        self.daq_size_array[9] = 10

        # Null buffer for unused channels
        self.null_buffer = b'\x01' + b'\x00' * 125

    def _init_hardware(self):
        """Initialize all channels - clear ready flags and set read buffer to 0"""
        for ch in range(self.NUM_CHANNELS):
            try:
                self.anchors[ch].write(self.BUFFER_SELECT_ADDR, 0)
                self.anchors[ch].write(self.BUFFER_0_READY_ADDR, 0)
                self.anchors[ch].write(self.BUFFER_1_READY_ADDR, 0)
            except Exception as e:
                raise DaqError(f"Failed to init hardware for channel {ch}: {e}")

        self.read_buffer = 0

    # --------------------------
    # Buffer Management
    # --------------------------
    def _check_buffer_ready(self, channel, buffer_num):
        """Check if a specific buffer is ready for a specific channel"""
        try:
            ready_addr = self.BUFFER_0_READY_ADDR if buffer_num == 0 else self.BUFFER_1_READY_ADDR
            return (self.anchors[channel].read(ready_addr) & 0x1) == 1
        except Exception as e:
            raise DaqError(f"Failed to check buffer ready for ch{channel}, buf{buffer_num}: {e}")

    def _clear_buffer_ready(self, channel, buffer_num):
        """Clear the ready flag for a specific buffer and channel"""
        try:
            ready_addr = self.BUFFER_0_READY_ADDR if buffer_num == 0 else self.BUFFER_1_READY_ADDR
            self.anchors[channel].write(ready_addr, 0)
        except Exception as e:
            raise DaqError(f"Failed to clear buffer ready for ch{channel}, buf{buffer_num}: {e}")

    def _set_read_buffer(self, channel, buffer_num):
        """Set which buffer to read from for a specific channel"""
        try:
            self.anchors[channel].write(self.BUFFER_SELECT_ADDR, buffer_num)
        except Exception as e:
            raise DaqError(f"Failed to set read buffer for ch{channel}: {e}")

    def _determine_next_buffer(self):
        """
        Determine which buffer to read next based on ready flags.
        Returns buffer number (0 or 1) or None if no buffer is ready.
        Uses parallel checking for speed.
        """
        def check_all_ready(buf_num):
            """Check if all channels have this buffer ready"""
            return all(self._check_buffer_ready(ch, buf_num) for ch in range(self.NUM_CHANNELS))
        
        # Check both buffers in parallel using executor
        with self.executor as executor:
            future_0 = executor.submit(check_all_ready, 0)
            future_1 = executor.submit(check_all_ready, 1)
            
            buffer_0_ready = future_0.result()
            buffer_1_ready = future_1.result()

        # Ping-pong logic: prefer the other buffer from last read
        if self._last_read_buffer == 0:
            return 1 if buffer_1_ready else (0 if buffer_0_ready else None)
        else:
            return 0 if buffer_0_ready else (1 if buffer_1_ready else None)

    def _read_single_channel_fast(self, ch, buf):
        """FAST parallel-safe channel read"""
        try:
            # Set read buffer
            self.anchors[ch].write(self.BUFFER_SELECT_ADDR, buf)
            
            # Read data - direct MMIO access
            start = self.DATA_START_ADDR // 4
            raw = self.anchors[ch].mmio.array[start:start + self.TOTAL_SAMPLES]
            
            # Convert to int16
            raw_masked = raw & 0xFFFF
            raw_int16 = np.where(raw_masked >= 32768, 
                                raw_masked - 65536, 
                                raw_masked).astype(np.int16)
            
            # Clear ready flag IMMEDIATELY
            addr = self.BUFFER_0_READY_ADDR if buf == 0 else self.BUFFER_1_READY_ADDR
            self.anchors[ch].write(addr, 0)
            
            # Update tracking
            self.channel_last_buffer[ch] = buf
            
            return raw_int16
            
        except Exception:
            return None

    def _read_all_channels_parallel(self, buffer_num):
        """
        CRITICAL: Read ALL channels in parallel to prevent discontinuities.
        All channels MUST be read from the SAME buffer at the SAME time.
        """
        def read_channel(ch):
            """Worker function for parallel channel read"""
            data = self._read_single_channel_fast(ch, buffer_num)
            if data is not None:
                return (ch, data)
            return (ch, None)
        
        # Submit all channel reads simultaneously
        futures = {self.executor.submit(read_channel, ch): ch 
                  for ch in range(self.NUM_CHANNELS)}
        
        # Collect results
        success_count = 0
        for future in futures:
            ch, data = future.result()
            if data is not None:
                self.capture_buffers[self._buffer_idx][ch] = data
                success_count += 1
        
        return success_count

    def _clear_all_ready_flags(self):
        """Clear ready flags for the current read buffer across all channels"""
        for ch in range(self.NUM_CHANNELS):
            try:
                self._clear_buffer_ready(ch, self.read_buffer)
            except Exception as e:
                print(f"Warning: Failed to clear ready flag for ch{ch}: {e}")

    # --------------------------
    # Decimation Management
    # --------------------------
    def _compute_decim_bits(self, factor):
        """
        Compute 4-bit decimation control value based on divisibility.
        Bit mapping: bit0=div2, bit1=div4, bit2=div5, bit3=div25
        """
        bit0 = 1 if (factor % 2 == 0) else 0
        bit1 = 1 if (factor % 4 == 0) else 0
        bit2 = 1 if (factor % 5 == 0) else 0
        bit3 = 1 if (factor % 25 == 0) else 0
        return (bit3 << 3) | (bit2 << 2) | (bit1 << 1) | bit0

    def _is_supported_factor(self, factor):
        """Validate factor: must be of form 2^a * 5^b with a,b >= 0"""
        if not isinstance(factor, int) or factor < 1:
            return False
        temp = factor
        while temp % 2 == 0:
            temp //= 2
        while temp % 5 == 0:
            temp //= 5
        return temp == 1

    # --------------------------
    # State Machine - WITH PARALLEL CHANNEL READING
    # --------------------------
    def _step_state_machine(self):
        """
        Execute one FSM step with parallel channel reading.
        Returns daq_buffer when complete cycle finishes, otherwise returns None.
        """
        state = self._state

        if state == DaqState.IDLE:
            # Determine which buffer is ready for ALL channels
            next_buffer = self._determine_next_buffer()
            if next_buffer is None:
                return None
            
            self.read_buffer = next_buffer
            self._buffer_idx = self.active_capture_buffer
            self._state = DaqState.ARM
            return None

        if state == DaqState.ARM:
            # Skip to WAIT_READY since we'll set buffer during parallel read
            self._state = DaqState.WAIT_READY
            return None

        if state == DaqState.WAIT_READY:
            # Double-check all buffers are still ready
            all_ready = all(self._check_buffer_ready(ch, self.read_buffer) 
                           for ch in range(self.NUM_CHANNELS))
            if not all_ready:
                self._state = DaqState.IDLE
                return None
            
            self._state = DaqState.READ_DATA
            return None

        if state == DaqState.READ_DATA:
            # CRITICAL: Read ALL channels in parallel from the SAME buffer
            channels_read = self._read_all_channels_parallel(self.read_buffer)
            
            if channels_read == self.NUM_CHANNELS:
                self._state = DaqState.PROCESS
            else:
                # If not all channels read successfully, retry
                self._state = DaqState.IDLE
            return None

        if state == DaqState.PROCESS:
            # Apply voltage scaling to all channels
            buff = self.capture_buffers[self._buffer_idx]
            self.adc_buffer[:] = (buff.astype(np.float32) * self.volt_scale).astype(np.int16)
            self._state = DaqState.BUILD
            return None

        if state == DaqState.BUILD:
            self.daq_buffer = self._build_output_buffer()
            self._state = DaqState.DONE
            return None

        if state == DaqState.DONE:
            self._last_read_buffer = self.read_buffer
            self.active_capture_buffer ^= 1
            self._state = DaqState.IDLE
            return self.daq_buffer

        return None

    def _build_output_buffer(self):
        """Build final output buffer efficiently"""
        buffers = [self.temprh_bytes]
        for chan in range(self.NUM_CHANNELS):
            buffers.append(self.adc_buffer[chan].tobytes())
        for _ in range(9 - self.NUM_CHANNELS):
            buffers.append(self.null_buffer)
        buffers.append(b'\x00' * 10)
        return b''.join(buffers)

    # --------------------------
    # Background Thread
    # --------------------------
    def _fsm_thread_loop(self):
        """FSM running continuously in a background thread"""
        while self._fsm_thread_running:
            try:
                self._step_state_machine()
            except Exception as e:
                print(f"FSM thread error: {e}")
            time.sleep(0.0002)

    def start_background(self):
        """Start the FSM in a background thread for continuous acquisition"""
        if self._fsm_thread_running:
            return
        self.start()
        self._fsm_thread_running = True
        self._fsm_thread = Thread(target=self._fsm_thread_loop, daemon=True)
        self._fsm_thread.start()

    def stop_background(self):
        """Stop the background FSM thread"""
        self._fsm_thread_running = False
        if self._fsm_thread is not None:
            self._fsm_thread.join(timeout=1.0)
        self._fsm_thread = None

    # --------------------------
    # Public API
    # --------------------------

    def start(self):
        """Start acquisition (reset state machine and hardware flags)"""
        for ch in range(self.NUM_CHANNELS):
            self.anchors[ch].write(self.BUFFER_0_READY_ADDR, 0)
            self.anchors[ch].write(self.BUFFER_1_READY_ADDR, 0)
            self.anchors[ch].write(self.BUFFER_SELECT_ADDR, 0)

        self.read_buffer = 0
        self.active_capture_buffer = 0
        self._last_read_buffer = -1
        self._state = DaqState.IDLE

    def read(self, timeout=None):
        """
        Blocking read using state machine. Returns when buffer is ready.
        
        Args:
            timeout: optional timeout in seconds
        Returns:
            bytes: daq_buffer when ready
        """
        start = time.time()
        while True:
            result = self._step_state_machine()
            if result is not None:
                return result

            if timeout is not None and (time.time() - start) >= timeout:
                raise DaqError("DAQ read timed out")

            time.sleep(0.0005)

    def read_streaming(self):
        """
        Non-blocking read. Returns new buffer if available, otherwise returns
        last buffer to maintain continuous data flow.
        """
        result = self._step_state_machine()
        if result is not None:
            return result
        return self.daq_buffer if self.daq_buffer else None

    def read_channel(self, channel):
        """
        Read single channel immediately (blocking for that channel only).
        Independent of main FSM operation.
        """
        if channel < 0 or channel >= self.NUM_CHANNELS:
            raise DaqError(f"Invalid channel: {channel}")

        try:
            output = np.zeros((self.TOTAL_SAMPLES,), dtype=np.int16)
            anchor = self.anchors[channel]
            
            # Wait for buffer ready
            buf0_ready = self._check_buffer_ready(channel, 0)
            buf1_ready = self._check_buffer_ready(channel, 1)
            
            if not buf0_ready and not buf1_ready:
                t0 = time.time()
                while not buf0_ready and not buf1_ready:
                    if time.time() - t0 > 1.0:
                        raise DaqError(f"Timeout waiting for channel {channel} ready")
                    time.sleep(0.0005)
                    buf0_ready = self._check_buffer_ready(channel, 0)
                    buf1_ready = self._check_buffer_ready(channel, 1)
            
            # Prefer buffer 0 if both ready
            read_buf = 0 if buf0_ready else 1
            
            # Set buffer select and read
            self._set_read_buffer(channel, read_buf)
            time.sleep(0.0001)
            
            data_start = self.DATA_START_ADDR // 4
            raw = anchor.mmio.array[data_start: data_start + self.TOTAL_SAMPLES]
            output[:] = (raw & 0xFFFF).astype(np.int16)

            # Clear ready flag
            self._clear_buffer_ready(channel, read_buf)

            # Apply voltage scaling
            return (output.astype(np.float32) * self.volt_scale).astype(np.int16)

        except Exception as e:
            raise DaqError(f"Channel {channel} read failed: {e}")

    def set_decimation_factor(self, factors):
        """Set decimation factor for each channel independently"""
        if isinstance(factors, (int, np.integer)):
            factors = [factors] * self.NUM_CHANNELS

        if len(factors) != self.NUM_CHANNELS:
            raise DaqError(f"Expected {self.NUM_CHANNELS} decimation factors, got {len(factors)}")

        self.decimation_factors = list(factors)
        for ch, factor in enumerate(self.decimation_factors):
            if not self._is_supported_factor(factor):
                raise DaqError(
                    f"Channel {ch}: Decimation factor {factor} not supported. "
                    f"Valid factors are of form 2^a * 5^b (e.g. 1,2,4,5,10,20,25,50,100)."
                )
            value = self._compute_decim_bits(factor)
            self.filter_ctrls[ch].write(0x00, value)

    def set_decimation_factor_single(self, channel, decim_factor):
        """Set decimation factor for a single channel"""
        if channel < 0 or channel >= self.NUM_CHANNELS:
            raise DaqError(f"Channel {channel} out of range [0, {self.NUM_CHANNELS-1}]")

        if not self._is_supported_factor(decim_factor):
            raise DaqError(
                f"Decimation factor {decim_factor} not supported. "
                f"Valid values: 2^a * 5^b (e.g. 1,2,4,5,10,20...)."
            )

        if not hasattr(self, 'decimation_factors'):
            self.decimation_factors = [20] * self.NUM_CHANNELS
        self.decimation_factors[channel] = decim_factor

        decim_value = self._compute_decim_bits(decim_factor)
        self.filter_ctrls[channel].write(0x00, decim_value)

    def get_decimation_factors(self):
        """Return current decimation factors"""
        if hasattr(self, 'decimation_factors'):
            return self.decimation_factors.copy()
        return [20] * self.NUM_CHANNELS

    def get_dataloss(self):
        """Return data loss indicator"""
        return 0

    def get_version(self):
        """Return version information"""
        return ("2.13", "0", "0")

    # Legacy compatibility stubs
    def boot_sc11(self):
        pass

    def get_response(self):
        return ("", "", [""])

    def send_command(self, command):
        pass

    def config(self):
        pass

    def trigger(self):
        pass

    def dcreset(self):
        pass

    def mic_check(self, mic_check_state):
        pass

    def uncompress_all(self):
        pass

    def stop(self):
        """Stop acquisition and background processing"""
        self.stop_processing.set()
        self.stop_background()

    def close(self):
        """Clean shutdown"""
        self.stop_processing.set()
        self.stop_background()
        self.executor.shutdown(wait=True)
        if hasattr(self, 'ol'):
            del self.ol

    def __enter__(self):
        return self

    def __exit__(self, exc_type, exc_val, exc_tb):
        self.close()
        return False