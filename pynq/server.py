#!/usr/bin/env python3
"""
Spicer DAQ WebSocket Server for PYNQ-Z1
Serves real-time/simulated data to a web-based oscilloscope.
"""
import sys
import os
import time
import traceback
from fastapi import FastAPI, WebSocket
import uvicorn
import asyncio
import random
import struct
from threading import Thread

print(f"=== Spicer DAQ Server Starting ===")
print(f"Python: {sys.executable}")

# --- Set PYNQ-specific paths for XRT (Xilinx Runtime) ---
# This is crucial for PYNQ-Z1 v3.1.1 to find FPGA libraries
os.environ['XILINX_XRT'] = '/usr'
os.environ['LD_LIBRARY_PATH'] = '/usr/lib:' + os.environ.get('LD_LIBRARY_PATH', '')

# --- FPGA/DAQ Initialization (Simulated) ---
class SimulatedDAQ:
    """Fallback DAQ class that generates simulated waveform data."""
    def __init__(self):
        self.running = False
        self.thread = None
        self.data_buffer = bytearray()
        print("✓ Simulated DAQ initialized")

    def start_background(self):
        self.running = True
        self.thread = Thread(target=self._background_task, daemon=True)
        self.thread.start()
        print("✓ Simulated DAQ background started")

    def _background_task(self):
        counter = 0
        while self.running:
            self.data_buffer = bytearray()
            for ch in range(16):  # 16 channels
                for sample in range(2500):  # 2500 samples per channel
                    # Generate a simple simulated waveform (sine + noise)
                    t = counter * 0.01 + sample * 0.001
                    freq = 1 + ch * 0.5
                    # Simulated ADC value (12-16 bit range)
                    sine_value = int(1500 * (0.5 + 0.5 * (0.3 * random.random())))
                    self.data_buffer.extend(struct.pack('h', sine_value))  # Pack as int16
            counter += 1
            time.sleep(0.1)  # Generate new data every 100ms

    def read_streaming(self):
        """Returns the latest generated data block as bytes."""
        if self.data_buffer:
            data = bytes(self.data_buffer)
            self.data_buffer = bytearray()  # Clear buffer after reading
            return data
        return None

# --- Initialize the DAQ ---
# Using simulated mode for guaranteed, portable startup
daq = SimulatedDAQ()
daq.start_background()

# --- FastAPI Application ---
app = FastAPI(title="Spicer DAQ Server")

@app.websocket("/ws")
async def websocket_data(websocket: WebSocket):
    """WebSocket endpoint for streaming live data to the web client."""
    await websocket.accept()
    print(f"WebSocket client connected from {websocket.client.host}")
    try:
        while True:
            # 1. Get data (real or simulated)
            data = daq.read_streaming()
            # 2. Send it to the web client
            if data:
                await websocket.send_bytes(data)  # Send binary data for oscilloscope
            else:
                # Send a heartbeat to keep the connection alive if no data
                await websocket.send_json({"type": "heartbeat"})
            # 3. Control the refresh rate (~100 Hz)
            await asyncio.sleep(0.01)
    except Exception as e:
        print(f"WebSocket connection error: {e}")
    finally:
        print("WebSocket client disconnected")
        await websocket.close()

@app.get("/api/stats")
async def get_stats():
    """Simple API endpoint for service health and status."""
    import psutil
    process = psutil.Process(os.getpid())
    return {
        "status": "running",
        "timestamp": time.time(),
        "memory_mb": process.memory_info().rss // (1024 * 1024),
        "version": "1.0",
        "mode": "simulation"
    }

@app.get("/")
async def root():
    """Root endpoint returns a simple status message."""
    return {"message": "Spicer DAQ WebSocket Server", "status": "ok"}

if __name__ == "__main__":
    print("Starting uvicorn server on 127.0.0.1:8000")
    # Start the ASGI server. 'reload=True' is useful for development only.
    uvicorn.run(
        app,
        host="127.0.0.1",  # Listen only locally (Apache will proxy public traffic)
        port=8000,
        log_level="info"
    )