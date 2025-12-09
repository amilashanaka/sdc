#!/usr/bin/env python3
import sys
import os
import time
import traceback

print(f"Starting server with Python: {sys.executable}")
print(f"Python version: {sys.version}")
print(f"Working directory: {os.getcwd()}")
print(f"User ID: {os.getuid()}")
print(f"XILINX_XRT: {os.environ.get('XILINX_XRT', 'Not set')}")
print(f"LD_LIBRARY_PATH: {os.environ.get('LD_LIBRARY_PATH', 'Not set')}")

# Check for XRT library
xrt_lib_path = "/opt/xilinx/xrt/lib/libxrt_core.so.2"
if not os.path.exists(xrt_lib_path):
    print(f"Warning: XRT library not found at {xrt_lib_path}")
    # Look for alternative locations
    alt_paths = [
        "/usr/lib/libxrt_core.so",
        "/usr/lib/libxrt_core.so.2",
        "/usr/local/lib/libxrt_core.so",
        "/usr/lib/aarch64-linux-gnu/libxrt_core.so",
        "/usr/lib/x86_64-linux-gnu/libxrt_core.so",
    ]
    
    for path in alt_paths:
        if os.path.exists(path):
            print(f"Found alternative XRT library at: {path}")
            # Update LD_LIBRARY_PATH
            lib_dir = os.path.dirname(path)
            if lib_dir not in os.environ.get('LD_LIBRARY_PATH', ''):
                os.environ['LD_LIBRARY_PATH'] = f"{lib_dir}:{os.environ.get('LD_LIBRARY_PATH', '')}"
            break
    else:
        print("No XRT library found. Will run in simulation mode.")

# Import pynq with error handling
pynq_available = False
try:
    print("Attempting to import PYNQ...")
    from pynq import Overlay, Device
    pynq_available = True
    print("✓ PYNQ imported successfully")
    
    # Check for devices
    try:
        devices = Device.devices
        if len(devices) > 0:
            print(f"✓ Found {len(devices)} FPGA device(s)")
            for i, dev in enumerate(devices):
                print(f"  Device {i}: {dev.name}")
        else:
            print("⚠ No FPGA devices found")
    except Exception as e:
        print(f"⚠ Could not list devices: {e}")
        print(traceback.format_exc())
        
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
try:
    from daq import Daq
    print("✓ DAQ imported successfully")
except ImportError as e:
    print(f"✗ DAQ import failed: {e}")
    print(traceback.format_exc())
    sys.exit(1)

app = FastAPI()
print("✓ FastAPI app created")

# Create DAQ object - with error handling
daq = None
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
        print("⚠ Running in simulation mode")
        daq = None
else:
    print("⚠ PYNQ not available, running in simulation mode")
    daq = None

# Create a simulation mode if needed
if daq is None:
    print("Creating simulated DAQ for testing...")
    class SimulatedDAQ:
        def __init__(self):
            self.running = True
            print("Simulated DAQ created")
        
        def start_background(self):
            print("Simulated DAQ background started")
        
        def read_streaming(self):
            import random
            import struct
            # Generate simulated data
            data = bytearray()
            for _ in range(2500):
                value = random.randint(-2000, 2000)
                data.extend(struct.pack('h', value))
            return bytes(data)
    
    daq = SimulatedDAQ()
    daq.start_background()

@app.websocket("/ws")
async def websocket_data(websocket: WebSocket):
    await websocket.accept()
    print(f"WebSocket connection established")

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
    import time
    import os
    return {
        "status": "running",
        "fpga_available": pynq_available and daq is not None,
        "simulation_mode": not pynq_available,
        "timestamp": time.time(),
        "process_id": os.getpid(),
        "xrt_library_found": os.path.exists(xrt_lib_path),
    }

@app.get("/")
async def root():
    return {
        "message": "Spicer DAQ WebSocket Server", 
        "status": "running",
        "fpga_available": pynq_available and daq is not None,
        "simulation_mode": not pynq_available
    }

if __name__ == "__main__":
    print("Starting uvicorn server on 127.0.0.1:8000")
    uvicorn.run(
        app, 
        host="127.0.0.1", 
        port=8000, 
        log_level="info",
        # Longer timeout for debugging
        timeout_keep_alive=30
    )