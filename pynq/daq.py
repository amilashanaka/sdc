# ============================================================================
# UNIFIED daq.py - COMBINED FASTAPI AND LABVIEW VERSIONS
# ============================================================================
"""
Name:    daq.py - Unified version for both FastAPI and LabVIEW
Version: 1.3
Date:    06-01-2026
By:      Don Gunasinha, Spicer Consulting 
Note:    Combines parallel reading optimization from FastAPI version
         with proper buffer synchronization from LabVIEW version.
         Includes debug mode toggle for FastAPI/LabVIEW compatibility.
"""

from enum import Enum, auto
import numpy as np
import time
from pynq import Overlay
from threading import Thread
import struct
from concurrent.futures import ThreadPoolExecutor, as_completed


class DaqError(Exception):
    """Custom exception for DAQ errors."""


class DaqState(Enum):
    IDLE = auto()
    DETERMINE_BUFFER = auto()
    READ_ALL_CHANNELS = auto()
    PROCESS = auto()
    BUILD = auto()
    DONE = auto()


class Daq:
    MAX_CHANNELS = 16
    TOTAL_SAMPLES = 1250
    VREF = 1.5
    ADC_RESOLUTION = 1.0
    DECIM_FACTOR = 20
    
    VALID_DECIMATION = [1, 2, 4, 5, 10, 20, 25, 50, 100]

    # Register offsets (compatible with anchor.v v1.2)
    BUFFER_SELECT_ADDR = 0x00
    BUFFER_0_READY_ADDR = 0x04
    BUFFER_1_READY_ADDR = 0x08
    DATA_START_ADDR = 0x0C

    def __init__(self, bitstream="bram.bit", force_reboot=False, num_channels=MAX_CHANNELS, 
                 total_samples=TOTAL_SAMPLES, decim_factors=DECIM_FACTOR, vref=VREF, 
                 adc_resolution=VREF, scale_output=False, debug_mode=False):
        
        if not (1 <= num_channels <= self.MAX_CHANNELS):
            raise DaqError(f"num_channels must be 1–{self.MAX_CHANNELS}")

        self.NUM_CHANNELS = num_channels
        self.TOTAL_SAMPLES = total_samples
        self.VREF = float(vref)
        self.ADC_RESOLUTION = float(adc_resolution)
        self.DEBUG_MODE = debug_mode  # True for FastAPI debugging, False for LabVIEW
        
        self.scale_output = scale_output
        self.volt_scale = self.VREF / self.ADC_RESOLUTION if scale_output else 1.0

        # Decimation
        if isinstance(decim_factors, (int, np.integer)):
            decim_factors = [decim_factors] * self.NUM_CHANNELS
        self.decimation_factors = list(decim_factors)
        
        self.validate_decimation()

        # Buffer synchronization (from LabVIEW version)
        self.current_global_buffer = None  # Buffer to read for ALL channels
        self._last_read_buffer = -1  # Track last buffer read for ping-pong
        
        # Per-channel tracking
        self.channel_data = [np.zeros(self.TOTAL_SAMPLES, dtype=np.int16) 
                            for _ in range(self.NUM_CHANNELS)]
        self.channel_last_buffer = [-1] * self.NUM_CHANNELS
        self.channel_ready_count = [0] * self.NUM_CHANNELS
        self.first_valid_data = [False] * self.NUM_CHANNELS

        # Output buffer
        self.adc_buffer = np.zeros((self.NUM_CHANNELS, self.TOTAL_SAMPLES), dtype=np.int16)
        self.daq_buffer = b""

        # Calculate packet sizes
        self.block_bytes = self.TOTAL_SAMPLES * 2
        self.total_packet_size = (8 + (self.NUM_CHANNELS * self.block_bytes) + 
                                 (16 - self.NUM_CHANNELS) * 126 + 34)

        # Load bitstream
        self.ol = Overlay(bitstream)
        try:
            self.ol.download()
        except Exception:
            pass

        # Get IP handles
        self.anchors = []
        self.filter_ctrls = []
        for i in range(self.NUM_CHANNELS):
            try:
                ip = getattr(self.ol, f"adc_{i}")
                self.anchors.append(ip.anchor_0)
                self.filter_ctrls.append(ip.filter_ctrl_0)
            except Exception:
                self.anchors.append(None)
                self.filter_ctrls.append(None)

        # State
        self._state = DaqState.IDLE
        self._background_thread = None
        self._running = False

        # Diagnostics
        self.frame_count = 0
        self.stall_count = 0
        self.error_count = 0
        self.last_frame_time = time.time()
        self.frame_rate = 0.0
        self.read_time_ms = 0
        self.buffer_switches = 0

        self._init_hardware()
        self.set_decimation_factor(self.decimation_factors)

    def validate_decimation(self):
        """Validate and correct decimation factors"""
        corrected = False
        
        for i, f in enumerate(self.decimation_factors):
            if f not in self.VALID_DECIMATION:
                closest = min(self.VALID_DECIMATION, key=lambda x: abs(x - f))
                self.decimation_factors[i] = closest
                corrected = True
        
        return corrected

    def _compute_decim_bits(self, f):
        """Convert decimation factor to hardware control bits"""
        if f not in self.VALID_DECIMATION:
            closest = min(self.VALID_DECIMATION, key=lambda x: abs(x - f))
            f = closest
        
        f_temp = f
        power_of_2 = 0
        power_of_5 = 0
        
        while f_temp % 2 == 0:
            power_of_2 += 1
            f_temp //= 2
        
        while f_temp % 5 == 0:
            power_of_5 += 1
            f_temp //= 5
        
        if f_temp != 1:
            return 0
        
        power_of_2 = min(power_of_2, 2)
        power_of_5 = min(power_of_5, 2)
        
        b0 = 1 if power_of_2 >= 1 else 0
        b1 = 1 if power_of_2 >= 2 else 0
        b2 = 1 if power_of_5 >= 1 else 0
        b3 = 1 if power_of_5 >= 2 else 0
        
        bits = (b3 << 3) | (b2 << 2) | (b1 << 1) | b0
        
        return bits

    def _init_hardware(self):
        """Initialize all hardware"""
        for ch in range(self.NUM_CHANNELS):
            if self.anchors[ch] is not None:
                try:
                    a = self.anchors[ch]
                    a.write(self.BUFFER_SELECT_ADDR, 0)
                    a.write(self.BUFFER_0_READY_ADDR, 0)
                    a.write(self.BUFFER_1_READY_ADDR, 0)
                except Exception:
                    pass
        
        time.sleep(0.1)
        self.frame_count = 0
        self._state = DaqState.IDLE
        self._last_read_buffer = -1

    def _check_buffer_ready(self, ch, buf):
        """Check if buffer is ready for a specific channel"""
        if self.anchors[ch] is None:
            return False
            
        addr = self.BUFFER_0_READY_ADDR if buf == 0 else self.BUFFER_1_READY_ADDR
        try:
            return (self.anchors[ch].read(addr) & 1) == 1
        except:
            return False

    def _clear_buffer_ready(self, ch, buf):
        """Clear buffer ready flag"""
        if self.anchors[ch] is None:
            return
            
        addr = self.BUFFER_0_READY_ADDR if buf == 0 else self.BUFFER_1_READY_ADDR
        try:
            self.anchors[ch].write(addr, 0)
        except:
            pass

    def _determine_global_buffer(self):
        """
        Determine which buffer to read for ALL channels (LabVIEW synchronization logic)
        Returns buffer number (0 or 1) or None if no buffer is ready
        """
        # Check if buffer 0 is ready for ALL channels
        buffer_0_ready = all(self._check_buffer_ready(ch, 0) for ch in range(self.NUM_CHANNELS))
        
        # Check if buffer 1 is ready for ALL channels
        buffer_1_ready = all(self._check_buffer_ready(ch, 1) for ch in range(self.NUM_CHANNELS))
        
        if self.DEBUG_MODE:
            # FastAPI debug mode: More flexible, allow per-channel buffer selection
            # This prevents gaps but may have timing issues
            if self._last_read_buffer == 0:
                return 1 if buffer_1_ready else (0 if buffer_0_ready else None)
            else:
                return 0 if buffer_0_ready else (1 if buffer_1_ready else None)
        else:
            # LabVIEW production mode: Strict synchronization
            # Both buffers must be ready for all channels
            if buffer_0_ready and buffer_1_ready:
                # Ping-pong: prefer the opposite of last read
                if self._last_read_buffer == 0:
                    return 1
                else:
                    return 0
            elif buffer_0_ready:
                return 0
            elif buffer_1_ready:
                return 1
            else:
                return None

    def _read_channel_buffer_fast(self, ch, buf):
        """FAST read - minimal overhead with proper synchronization"""
        if self.anchors[ch] is None:
            return None
            
        try:
            # Set read buffer
            self.anchors[ch].write(self.BUFFER_SELECT_ADDR, buf)
            
            # Small delay to ensure buffer select propagates
            if not self.DEBUG_MODE:
                time.sleep(0.00001)
            
            # Read data - direct MMIO access
            start = self.DATA_START_ADDR // 4
            raw = self.anchors[ch].mmio.array[start:start + self.TOTAL_SAMPLES]
            
            # Convert to int16
            raw_masked = raw & 0xFFFF
            raw_int16 = np.where(raw_masked >= 32768, 
                                raw_masked - 65536, 
                                raw_masked).astype(np.int16)
            
            # Update tracking
            self.channel_last_buffer[ch] = buf
            self.channel_ready_count[ch] += 1
            
            return raw_int16
            
        except Exception as e:
            if self.DEBUG_MODE:
                print(f"DEBUG: Channel {ch} read error: {e}")
            return None

    def _read_all_channels_parallel(self, global_buffer):
        """
        Read ALL channels in parallel using threads with global buffer synchronization.
        This reduces total read time while maintaining data consistency.
        """
        read_start = time.perf_counter()
        channels_read = 0
        
        def read_single_channel(ch):
            if self.anchors[ch] is None:
                return 0
            
            data = self._read_channel_buffer_fast(ch, global_buffer)
            if data is not None:
                self.channel_data[ch] = data
                self.first_valid_data[ch] = True
                return 1
            return 0
        
        # Read all channels in parallel
        with ThreadPoolExecutor(max_workers=self.NUM_CHANNELS) as executor:
            future_to_ch = {executor.submit(read_single_channel, ch): ch 
                           for ch in range(self.NUM_CHANNELS)}
            for future in as_completed(future_to_ch):
                channels_read += future.result()
        
        # Clear ready flags for ALL channels after reading
        for ch in range(self.NUM_CHANNELS):
            if self.anchors[ch] is not None:
                self._clear_buffer_ready(ch, global_buffer)
        
        read_end = time.perf_counter()
        self.read_time_ms = (read_end - read_start) * 1000
        
        if self.DEBUG_MODE and channels_read != self.NUM_CHANNELS:
            print(f"DEBUG: Only {channels_read}/{self.NUM_CHANNELS} channels read successfully")
        
        return channels_read

    def _step(self):
        """State machine - read all channels with proper synchronization"""
        try:
            s = self._state

            if s == DaqState.IDLE:
                # Check if any global buffer is ready
                self.current_global_buffer = self._determine_global_buffer()
                
                if self.current_global_buffer is not None:
                    self._state = DaqState.READ_ALL_CHANNELS
                    if self.DEBUG_MODE:
                        print(f"DEBUG: Switching to buffer {self.current_global_buffer}")
                else:
                    self.stall_count += 1
                    time.sleep(0.0001)
                
                return None

            if s == DaqState.READ_ALL_CHANNELS:
                # Read ALL channels in parallel using synchronized buffer
                channels_read = self._read_all_channels_parallel(self.current_global_buffer)
                
                if channels_read > 0:
                    self._last_read_buffer = self.current_global_buffer
                    self.buffer_switches += 1
                    self._state = DaqState.PROCESS
                else:
                    # Nothing ready, go back to idle
                    self._state = DaqState.IDLE
                
                return None

            if s == DaqState.PROCESS:
                # Copy data to output buffer
                for ch in range(self.NUM_CHANNELS):
                    if self.first_valid_data[ch]:
                        if self.scale_output and self.volt_scale != 1.0:
                            scaled = self.channel_data[ch].astype(np.float32) * self.volt_scale
                            np.clip(scaled, -32768, 32767, out=scaled)
                            self.adc_buffer[ch] = scaled.astype(np.int16)
                        else:
                            self.adc_buffer[ch] = self.channel_data[ch].copy()
                    else:
                        self.adc_buffer[ch].fill(0)
                
                # Update frame rate
                current_time = time.time()
                if self.last_frame_time > 0:
                    delta = current_time - self.last_frame_time
                    if delta > 0:
                        self.frame_rate = 0.9 * self.frame_rate + 0.1 * (1.0 / delta)
                self.last_frame_time = current_time
                
                self.frame_count += 1
                self._state = DaqState.BUILD
                return None

            if s == DaqState.BUILD:
                packet_parts = []
                
                # Temp/RH
                packet_parts.append(struct.pack('>4h', 100, 100, 0, 100))
                
                # Channel data
                for ch in range(self.NUM_CHANNELS):
                    channel_data = np.clip(self.adc_buffer[ch], -32768, 32767).astype('<i2').tobytes()
                    packet_parts.append(channel_data)
                
                # Padding
                null_data = b'\x00' * 126
                for _ in range(self.NUM_CHANNELS, 16):
                    packet_parts.append(null_data)
                
                # Header
                header_parts = []
                header_parts.append(struct.pack('>h', self.NUM_CHANNELS))
                for ch in range(16):
                    size = self.block_bytes if ch < self.NUM_CHANNELS else 126
                    header_parts.append(struct.pack('>h', size))
                
                packet_parts.append(b''.join(header_parts))
                self.daq_buffer = b''.join(packet_parts)
                
                self._state = DaqState.DONE
                return self.daq_buffer

            if s == DaqState.DONE:
                self._state = DaqState.IDLE
                return self.daq_buffer

            return None
            
        except Exception as e:
            self.error_count += 1
            if self.DEBUG_MODE:
                print(f"DEBUG: State machine error: {e}")
            self._state = DaqState.IDLE
            return None

    def set_decimation_factor(self, factors):
        """Set decimation for each channel"""
        if isinstance(factors, (int, np.integer)):
            factors = [factors] * self.NUM_CHANNELS
        
        self.decimation_factors = list(factors)
        self.validate_decimation()
        
        for ch, f in enumerate(self.decimation_factors):
            if self.filter_ctrls[ch] is not None:
                val = self._compute_decim_bits(f)
                try:
                    self.filter_ctrls[ch].write(0x00, val)
                except Exception as e:
                    if self.DEBUG_MODE:
                        print(f"DEBUG: Channel {ch} decimation error: {e}")

    def start(self):
        """Start acquisition"""
        self._init_hardware()

    def read(self, timeout=None):
        """Blocking read"""
        start_time = time.time()
        while True:
            result = self._step()
            if result is not None:
                return result
            if timeout and time.time() - start_time > timeout:
                raise DaqError("Timeout")
            time.sleep(0.00001)

    def start_background(self):
        """Start background thread"""
        if self._running:
            return
        self.start()
        self._running = True
        self._background_thread = Thread(target=self._background_loop, daemon=True)
        self._background_thread.start()

    def _background_loop(self):
        """Background loop"""
        while self._running:
            try:
                self._step()
            except Exception as e:
                self.error_count += 1
                if self.DEBUG_MODE:
                    print(f"DEBUG: Background loop error: {e}")
            time.sleep(0.00001)

    def stop_background(self):
        """Stop background thread"""
        self._running = False
        if self._background_thread:
            self._background_thread.join(timeout=2.0)

    def read_streaming(self):
        """Non-blocking read"""
        result = self._step()
        return result if result is not None else self.daq_buffer

    def get_stats(self):
        """Get statistics"""
        active = sum(1 for ch in range(self.NUM_CHANNELS) 
                    if self.first_valid_data[ch] and self.anchors[ch] is not None)
        
        stats = {
            'frames': self.frame_count,
            'stalls': self.stall_count,
            'errors': self.error_count,
            'frame_rate': self.frame_rate,
            'active': active,
            'read_time_ms': self.read_time_ms,
            'buffer_switches': self.buffer_switches,
            'last_buffer': self._last_read_buffer,
            'debug_mode': self.DEBUG_MODE
        }
        
        if self.DEBUG_MODE:
            # Add per-channel statistics in debug mode
            channel_stats = []
            for ch in range(self.NUM_CHANNELS):
                channel_stats.append({
                    'channel': ch,
                    'ready_count': self.channel_ready_count[ch],
                    'last_buffer': self.channel_last_buffer[ch],
                    'has_data': self.first_valid_data[ch]
                })
            stats['channels'] = channel_stats
        
        return stats

    def close(self):
        """Shutdown"""
        self.stop_background()
        if hasattr(self, 'ol'):
            del self.ol

    def __enter__(self):
        return self

    def __exit__(self, *args):
        self.close()

    def get_version(self):
        """Get version information"""
        if self.DEBUG_MODE:
            return "1.3-debug", "0", "0"
        else:
            return "1.3-prod", "0", "0"

    # Compatibility methods from LabVIEW version
    def get_dataloss(self):
        """Return data loss indicator"""
        return self.error_count

    def read_channel(self, channel):
        """Read single channel (compatibility method)"""
        if channel < 0 or channel >= self.NUM_CHANNELS:
            raise DaqError(f"Invalid channel: {channel}")
        
        # Use the same logic but for single channel
        buf = self._determine_global_buffer()
        if buf is None:
            # Check per-channel buffers
            if self._check_buffer_ready(channel, 0):
                buf = 0
            elif self._check_buffer_ready(channel, 1):
                buf = 1
            else:
                return np.zeros(self.TOTAL_SAMPLES, dtype=np.int16)
        
        data = self._read_channel_buffer_fast(channel, buf)
        if data is not None:
            self._clear_buffer_ready(channel, buf)
            return data
        else:
            return np.zeros(self.TOTAL_SAMPLES, dtype=np.int16)

    # Stubbed legacy methods for compatibility
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
        """Stop acquisition"""
        self.stop_background()