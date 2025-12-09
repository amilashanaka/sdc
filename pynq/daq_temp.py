# Version of daq for TH meter that does not access SC11/SI

import numpy as np
import time
from pynq import Overlay
from pynq import allocate

DEBUG_ON = False

class DaqError(Exception):
    def __init__(self, message):
        self.message = message
        print(self.message)

class Daq:
    def __init__(self, force_reboot=False):
        # Initialise object variables
        #self.daq_buffer = None
        # Allocate arrays - all Bigendian signed 2-byte integer
        self.temprh_array = np.zeros(4, dtype=np.dtype('>i2'))
        self.daq_size_array = np.zeros(9, np.dtype('>i2'))
        # Allocate dummy buffer as a one followed by 125 zeros
        self.null_buffer = b'\01' + b'\00'*125
        # Initialise timer

        # Load the bitstream
        ol = Overlay("adcv3.bit")  # Replace with your bitstream file path
        ol.download()
        # Load IPs
        dma1 = ol.adc_0.dma
        data_anchor1 = ol.adc_0.anchor

        dma2 = ol.adc_1.dma
        data_anchor2 = ol.adc_1.anchor

        chan1_ctrl= ol.adc_0.filter_ctrl.channel1 
        chan2_ctrl= ol.adc_1.filter_ctrl.channel1 

        # DMA receive channels
        self.dma_recv1 = dma1.recvchannel
        self.dma_recv2 = dma2.recvchannel

        # Define constants
        data_size = 2500  # Total samples per DMA buffer
        adc_resolution = 4095  # Max ADC value for 12-bit data

        # Allocate buffers for DMA data
        output_buffer1 = allocate(shape=(data_size,), dtype=np.uint16)
        output_buffer2 = allocate(shape=(data_size,), dtype=np.uint16)

        output_buffer3 = allocate(shape=(data_size,), dtype=np.uint16)
        output_buffer4 = allocate(shape=(data_size,), dtype=np.uint16)

        self.current_buffer1 = output_buffer1
        self.next_buffer1 = output_buffer2

        self.current_buffer2 = output_buffer3
        self.next_buffer2 = output_buffer4

 



        self.last_time = time.time()
        # Initialise temperature and humidity
        (ok, self.temperature, self.humidity) =  (True,1,1)
        
    def boot_sc11(self):
        pass
        
    def get_response(self):
        return ("", "", [""])
     
    def send_command(self, command):
        pass

    def get_version(self):
        return ("2.10", "0", "0")

    def config(self):
        pass

    def trigger(self):
        pass
        
    def dcreset(self):
        pass

    def mic_check(self, mic_check_state):
        pass

    def start(self):
        pass

    def read(self):

      

        # Simulate data acquisition
        self.dma_recv1.transfer(self.current_buffer1)
        self.dma_recv1.wait()
        self.current_buffer1, self.next_buffer1 = self.next_buffer1, self.current_buffer1

        # Get temperature and humidity
        (ok, self.temperature, self.humidity)=  (True,1,1)
        self.temprh_array[0] = 100 * self.temperature
        self.temprh_array[1] = 100 * self.humidity
        # Spare slot
        self.temprh_array[2] = 0
        # Get CPU temperature
        cpu =100
        self.temprh_array[3] = 100  
        if DEBUG_ON:
            print("Temp:", self.temprh_array[0], "Hum:", self.temprh_array[1], "CPU:", self.temprh_array[3])
        # Pack temperture and humidity in buffer
        self.daq_buffer = self.temprh_array.tobytes()
        
        self.daq_size_array[0] = 8
        self.daq_size_array[1] = 1024
        self.daq_size_array[2] = 8
   
        # Add dummy data for the other sensors
        for chan in range(3,9):
            self.daq_buffer += self.null_buffer
            self.daq_size_array[chan] = 126
           
        # Wait to simulate data acquisition time
        time.sleep(0.4)
               
    def get_dataloss(self):
        return 0

    def uncompress_all(self):
        pass

    def stop(self):
        pass

    def close(self):
        del(self)
