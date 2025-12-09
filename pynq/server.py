#!/usr/bin/env python3
import sys
import os
import time
import traceback

print(f"=== Spicer DAQ Server Starting ===")
print(f"Time: {time.ctime()}")
print(f"Python: {sys.executable}")
print(f"Python version: {sys.version}")
print(f"Working directory: {os.getcwd()}")
print(f"User ID: {os.getuid()}")
print(f"XILINX_XRT: {os.environ.get('XILINX_XRT', 'Not set')}")
print(f"LD_LIBRARY_PATH: {os.environ.get('LD_LIBRARY_PATH', 'Not set')}")

# Check for XRT library on PYNQ
xrt_lib_paths = [
    "/usr/lib/libxrt_core.so.2.17.0",
    "/usr/lib/libxrt_core.so.2",
    "/usr/lib/libxrt_core.so",
]

xrt_found = False
for lib_path in xrt_lib_paths:
    if os.path.exists(lib_path):
        print(f"✓ XRT library found: {lib_path}")
        xrt_found = True
        break

if not xrt_found:
    print("✗ XRT library not found in standard locations")
    print("  Trying to find library...")
    import subprocess
    result = subprocess.run(['find', '/usr', '-name', 'libxrt_core.so*', '-type', 'f'], 
                          capture_output=True, text=True)
    if result.stdout:
        print("  Found libraries:")
        for line in result.stdout.strip().split('\n'):
            print(f"    {line}")

# Import pynq with error handling
pynq_available = False
try:
    print("\nAttempting to import PYNQ...")
    import pynq
    from pynq import Overlay, Device
    pynq_available = True
    print(f"✓ PYNQ imported successfully (version: {pynq.__version__})")
    
    # Check for devices
    try:
        print("Checking for FPGA devices...")
        devices = Device.devices
        if len(devices) > 0:
            print(f"✓ Found {len(devices)} FPGA device(s)")
            for i, dev in enumerate(devices):
                print(f"  Device {i}: {dev.name}")
                print(f"    Type: {type(dev)}")
        else:
            print("⚠ No FPGA devices found")
    except Exception as e:
        print(f"⚠ Could not list devices: {e}")
        if "No Devices Found" in str(e):
            print("  This may be normal if FPGA is not programmed")
        
except ImportError as e:
    print(f"✗ PYNQ import failed: {e}")
    print(traceback.format_exc())
except Exception as e:
    print(f"✗ Error during PYNQ import: {e}")
    print(traceback.format_exc())

# Now import the rest
from fastapi import FastAPI, WebSocket
import uvicorn
import asyncio

# Try to import daq
daq = None
try:
    print("\nAttempting to import DAQ module...")
    from daq import Daq
    print("✓ DAQ imported successfully")
    
    # Create DAQ object - with error handling
    if pynq_available:
        try:
            print("Initializing DAQ with FPGA...")
            daq = Daq()
            print("✓ DAQ object created")
            daq.start_background()
            print("✓ DAQ background started")
        except Exception as e:
            print(f"⚠ DAQ initialization failed: {e}")
            print(traceback.format_exc())
            print("⚠ Falling back to simulation mode")
            daq = None
    else:
        print("⚠ PYNQ not available, using simulation mode")
        daq = None
        
except ImportError as e:
    print(f"✗ DAQ import failed: {e}")
    print(traceback.format_exc())
    print("⚠ Will use simulation mode")

# Create simulation mode if needed
if daq is None:
    print("\nCreating simulated DAQ for testing...")
    import random
    import struct
    from threading import Thread
    
    class SimulatedDAQ:
        def __init__(self):
            self.running = False
            self.thread = None
            self.data_buffer = bytearray()
            print("✓ Simulated DAQ created")
        
        def start_background(self):
            self.running = True
            self.thread = Thread(target=self._background_task, daemon=True)
            self.thread.start()
            print("✓ Simulated DAQ background started")
        
        def _background_task(self):
            import time
            counter = 0
            while self.running:
                # Generate simulated data for 16 channels
                self.data_buffer = bytearray()
                for ch in range(16):
                    for sample in range(2500):
                        # Generate sine wave with different frequencies per channel
                        t = counter * 0.01 + sample * 0.001
                        freq = 1 + ch * 0.5
                        value = int(1500 * (0.5 + 0.5 * (0.3 * random.random() + 0.7 * (sample % 100) / 100)))
                        self.data_buffer.extend(struct.pack('h', value))
                counter += 1
                time.sleep(0.1)
        
        def read_streaming(self):
            if self.data_buffer:
                data = bytes(self.data_buffer)
                self.data_buffer = bytearray()
                return data
            return None
    
    daq = SimulatedDAQ()
    daq.start_background()

app = FastAPI()
print("\n✓ FastAPI app created")

@app.websocket("/ws")
async def websocket_data(websocket: WebSocket):
    await websocket.accept()
    print(f"WebSocket connection established from {websocket.client.host}")

    try:
        while True:
            # Read data from DAQ
            data = daq.read_streaming()
            
            if data:
                # Send raw samples as binary
                await websocket.send_bytes(data)
            else:
                # Send heartbeat to keep connection alive
                await websocket.send_json({"type": "heartbeat"})

            await asyncio.sleep(0.01)  # 10ms refresh
    except Exception as e:
        print(f"WebSocket error: {e}")
        print(traceback.format_exc())
    finally:
        print("WebSocket connection closed")
        await websocket.close()

# API endpoint for scope.php stats
@app.get("/api/stats")
async def get_stats():
    import psutil
    import os
    
    process = psutil.Process(os.getpid())
    memory_info = process.memory_info()
    
    return {
        "status": "running",
        "fpga_available": pynq_available and not isinstance(daq, SimulatedDAQ),
        "simulation_mode": isinstance(daq, SimulatedDAQ),
        "timestamp": time.time(),
        "process_id": os.getpid(),
        "memory_mb": memory_info.rss // (1024 * 1024),
        "cpu_percent": process.cpu_percent(),
        "version": "1.0",
        "platform": "PYNQ Linux"
    }

@app.get("/")
async def root():
    return {
        "message": "Spicer DAQ WebSocket Server", 
        "status": "running",
        "fpga_available": pynq_available and not isinstance(daq, SimulatedDAQ),
        "simulation_mode": isinstance(daq, SimulatedDAQ),
        "version": "1.0"
    }

if __name__ == "__main__":
    print("\n" + "="*50)
    print("Starting uvicorn server on 127.0.0.1:8000")
    print("="*50 + "\n")
    
    uvicorn.run(
        app, 
        host="127.0.0.1", 
        port=8000, 
        log_level="info",
        access_log=True,
        timeout_keep_alive=30
    )