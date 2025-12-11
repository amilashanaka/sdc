#!/usr/bin/env python3
from fastapi import FastAPI, WebSocket
from fastapi.staticfiles import StaticFiles
from fastapi.responses import FileResponse
import uvicorn
import asyncio
import os
import sys
import traceback

# Set PYNQ environment variables
os.environ['XILINX_XRT'] = '/usr'
os.environ['LD_LIBRARY_PATH'] = '/usr/lib:' + os.environ.get('LD_LIBRARY_PATH', '')

app = FastAPI()

# Mount static website (for standalone testing)
app.mount("/static", StaticFiles(directory="static"), name="static")

# Try to import and initialize DAQ with error handling
daq = None
daq_initialized = False

print("=== Starting Spicer DAQ Server ===", file=sys.stderr)
print(f"Python: {sys.executable}", file=sys.stderr)

try:
    # First, try to import pynq to check if it's available
    import pynq
    print("✓ PYNQ module imported", file=sys.stderr)
    
    # Now try to import daq module
    from daq import Daq
    print("✓ DAQ module imported", file=sys.stderr)
    
    # Try to initialize DAQ - this is where it might fail
    try:
        print("Initializing FPGA DAQ...", file=sys.stderr)
        daq = Daq()
        daq.start_background()
        daq_initialized = True
        print("✓ FPGA DAQ initialized successfully", file=sys.stderr)
    except Exception as e:
        print(f"⚠ FPGA DAQ initialization failed: {e}", file=sys.stderr)
        print("Will run in simulation mode", file=sys.stderr)
        # Create a simulated DAQ
        daq = None
        
except ImportError as e:
    print(f"✗ Module import failed: {e}", file=sys.stderr)
    print("Will run in simulation mode", file=sys.stderr)
    daq = None

# If DAQ initialization failed, create a simulated one
if not daq_initialized:
    print("Creating simulated DAQ...", file=sys.stderr)
    import random
    import struct
    import time
    from threading import Thread
    
    class SimulatedDAQ:
        def __init__(self):
            self.running = False
            self.thread = None
            self.data_buffer = bytearray()
            
        def start_background(self):
            self.running = True
            self.thread = Thread(target=self._background_task, daemon=True)
            self.thread.start()
            print("✓ Simulated DAQ background started", file=sys.stderr)
        
        def _background_task(self):
            counter = 0
            while self.running:
                self.data_buffer = bytearray()
                for ch in range(16):
                    for sample in range(2500):
                        # Generate simulated data
                        value = int(1000 * (ch + 1) * 
                                  (0.3 * random.random() + 
                                   0.7 * (sample % 100) / 100))
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

# Static file routes (these will be served by Apache in production)
@app.get("/")
async def serve_index():
    return FileResponse("static/index.php", media_type="text/html")

@app.get("/scope")
async def serve_scope():
    return FileResponse("static/scope.php", media_type="text/html")

@app.get("/dash")
async def serve_dash():
    return FileResponse("static/dash.php", media_type="text/html")

@app.websocket("/ws")
async def websocket_data(websocket: WebSocket):
    await websocket.accept()
    print(f"WebSocket connection established", file=sys.stderr)
    
    try:
        while True:
            if daq:
                data = daq.read_streaming()
            else:
                data = None
            
            if data:
                await websocket.send_bytes(data)
            else:
                await websocket.send_json({"type": "heartbeat"})
            
            await asyncio.sleep(0.01)
    except Exception as e:
        print(f"WebSocket error: {e}", file=sys.stderr)
        print(traceback.format_exc(), file=sys.stderr)
    finally:
        print("WebSocket connection closed", file=sys.stderr)

if __name__ == "__main__":
    print("Starting uvicorn server on 0.0.0.0:8000", file=sys.stderr)
    # Change from 127.0.0.1 to 0.0.0.0 to accept connections from Apache
    uvicorn.run(app, host="0.0.0.0", port=8000, log_level="info")