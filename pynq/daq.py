# ============================================================================
# FIXED daq.py - WORKS WITH HALT-PROTECTED HARDWARE
# ============================================================================
"""
Name:    daq.py (Hardware Halt Protection)
Version: 1.15
Date:    01-12-2025
By:      Don Gunasinha, Spicer Consulting
Fix:     Parallel channel reading to reduce read time and prevent buffer overruns
         Requires anchor.v v3.2 with halt protection
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
    READ_ALL_CHANNELS = auto()
    PROCESS = auto()
    BUILD = auto()
    DONE = auto()


class Daq:
    MAX_CHANNELS = 16
    TOTAL_SAMPLES = 2500
    VREF = 1.5
    ADC_RESOLUTION = 1.0
    DECIM_FACTOR = 20
    
    VALID_DECIMATION = [1, 2, 4, 5, 10, 20, 25, 50, 100]

    # Register offsets
    BUFFER_SELECT_ADDR = 0x00
    BUFFER_0_READY_ADDR = 0x04
    BUFFER_1_READY_ADDR = 0x08
    DATA_START_ADDR = 0x0C
    OVERRUN_COUNT_ADDR = 0x10

    def __init__(self, bitstream="bram.bit", force_reboot=False, num_channels=MAX_CHANNELS, 
                 total_samples=TOTAL_SAMPLES, decim_factors=DECIM_FACTOR, vref=VREF, 
                 adc_resolution=VREF, scale_output=False):
        
        if not (1 <= num_channels <= self.MAX_CHANNELS):
            raise DaqError(f"num_channels must be 1–{self.MAX_CHANNELS}")

        self.NUM_CHANNELS = num_channels
        self.TOTAL_SAMPLES = total_samples
        self.VREF = float(vref)
        self.ADC_RESOLUTION = float(adc_resolution)
        
        self.scale_output = scale_output
        self.volt_scale = self.VREF / self.ADC_RESOLUTION if scale_output else 1.0

        # Decimation
        if isinstance(decim_factors, (int, np.integer)):
            decim_factors = [decim_factors] * self.NUM_CHANNELS
        self.decimation_factors = list(decim_factors)
        
        self.validate_decimation()

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
        self._channels_to_read = []

        self._background_thread = None
        self._running = False

        # Diagnostics
        self.frame_count = 0
        self.overrun_count = 0
        self.stall_count = 0
        self.error_count = 0
        self.last_frame_time = time.time()
        self.frame_rate = 0.0
        self.read_time_ms = 0

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
                    a.write(self.OVERRUN_COUNT_ADDR, 0)
                except Exception:
                    pass
        
        time.sleep(0.1)
        self.frame_count = 0
        self._state = DaqState.IDLE

    def _check_buffer_ready(self, ch, buf):
        """Check if buffer is ready"""
        if self.anchors[ch] is None:
            return False
            
        addr = self.BUFFER_0_READY_ADDR if buf == 0 else self.BUFFER_1_READY_ADDR
        try:
            return (self.anchors[ch].read(addr) & 1) == 1
        except:
            return False

    def _find_ready_buffer(self, ch):
        """Find which buffer is ready for this channel"""
        if self.anchors[ch] is None:
            return None
            
        b0 = self._check_buffer_ready(ch, 0)
        b1 = self._check_buffer_ready(ch, 1)
        
        # Prefer the buffer we haven't read
        if self.channel_last_buffer[ch] == 0 and b1:
            return 1
        elif self.channel_last_buffer[ch] == 1 and b0:
            return 0
        elif b0:
            return 0
        elif b1:
            return 1
        else:
            return None

    def _read_channel_buffer_fast(self, ch, buf):
        """FAST read - minimal overhead"""
        if self.anchors[ch] is None:
            return None
            
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
            self.channel_ready_count[ch] += 1
            
            return raw_int16
            
        except Exception:
            return None

    def _read_all_channels_parallel(self):
        """
        Read ALL channels in parallel using threads.
        This reduces total read time to prevent buffer overruns.
        """
        read_start = time.perf_counter()
        channels_read = 0
        
        def read_single_channel(ch):
            if self.anchors[ch] is None:
                return 0
            
            buf = self._find_ready_buffer(ch)
            if buf is None:
                return 0
            
            data = self._read_channel_buffer_fast(ch, buf)
            if data is not None:
                self.channel_data[ch] = data
                self.first_valid_data[ch] = True
                return 1
            return 0
        
        with ThreadPoolExecutor(max_workers=self.NUM_CHANNELS) as executor:
            future_to_ch = {executor.submit(read_single_channel, ch): ch for ch in range(self.NUM_CHANNELS)}
            for future in as_completed(future_to_ch):
                channels_read += future.result()
                    
        read_end = time.perf_counter()
        self.read_time_ms = (read_end - read_start) * 1000
        
        return channels_read

    def _check_overruns(self):
        """Check for buffer overruns"""
        total_overruns = 0
        for ch in range(self.NUM_CHANNELS):
            if self.anchors[ch] is not None:
                try:
                    count = self.anchors[ch].read(self.OVERRUN_COUNT_ADDR)
                    if count > 0:
                        total_overruns += count
                        # Reset counter
                        self.anchors[ch].write(self.OVERRUN_COUNT_ADDR, 0)
                except:
                    pass
        
        self.overrun_count += total_overruns
        return total_overruns

    def _step(self):
        """State machine - read all channels quickly"""
        try:
            s = self._state

            if s == DaqState.IDLE:
                # Check if any channels are ready
                any_ready = False
                for ch in range(self.NUM_CHANNELS):
                    if self.anchors[ch] is not None:
                        if self._find_ready_buffer(ch) is not None:
                            any_ready = True
                            break
                
                if any_ready:
                    self._state = DaqState.READ_ALL_CHANNELS
                else:
                    self.stall_count += 1
                    time.sleep(0.0001)
                
                return None

            if s == DaqState.READ_ALL_CHANNELS:
                # Read ALL channels in parallel
                channels_read = self._read_all_channels_parallel()
                
                # Check for overruns
                overruns = self._check_overruns()
                
                if channels_read > 0:
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
            
        except Exception:
            self.error_count += 1
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
                except Exception:
                    pass

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
            except Exception:
                self.error_count += 1
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
        
        return {
            'frames': self.frame_count,
            'overruns': self.overrun_count,
            'stalls': self.stall_count,
            'errors': self.error_count,
            'frame_rate': self.frame_rate,
            'active': active,
            'read_time_ms': self.read_time_ms
        }

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
        return "1.15", "0", "0"