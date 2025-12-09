#!/usr/bin/env python3
import sys
import os

# Print debug info
print(f"Starting server with Python: {sys.executable}")
print(f"Python version: {sys.version}")
print(f"Working directory: {os.getcwd()}")

# First try to import pynq directly
try:
    from pynq import Overlay
    print("✓ PYNQ imported successfully")
except ImportError as e:
    print(f"✗ PYNQ import failed: {e}")
    # Try to add virtual environment path
    venv_path = "/usr/local/share/pynq-venv/lib/python3.10/site-packages"
    if os.path.exists(venv_path):
        sys.path.insert(0, venv_path)
        print(f"Added virtual environment path: {venv_path}")
        try:
            from pynq import Overlay
            print("✓ PYNQ imported after path adjustment")
        except ImportError as e2:
            print(f"✗ Still failed: {e2}")
            sys.exit(1)
    else:
        print(f"Virtual environment path not found: {venv_path}")
        sys.exit(1)

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
    # If daq.py is in the same directory, it should work
    sys.exit(1)

app = FastAPI()
print("✓ FastAPI app created")

# Create DAQ object
try:
    daq = Daq()
    print("✓ DAQ object created")
    daq.start_background()
    print("✓ DAQ background started")
except Exception as e:
    print(f"✗ DAQ initialization failed: {e}")
    sys.exit(1)

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
    finally:
        print("WebSocket connection closed")
        await websocket.close()

# API endpoint for scope.php stats
@app.get("/api/stats")
async def get_stats():
    import time
    import os
    try:
        import psutil
        process = psutil.Process(os.getpid())
        memory_info = process.memory_info()
        return {
            "status": "running",
            "timestamp": time.time(),
            "process_id": os.getpid(),
            "memory_rss": memory_info.rss,
            "memory_percent": process.memory_percent(),
            "cpu_percent": process.cpu_percent(),
        }
    except ImportError:
        return {
            "status": "running",
            "timestamp": time.time(),
            "process_id": os.getpid(),
        }

@app.get("/")
async def root():
    return {"message": "Spicer DAQ WebSocket Server", "status": "running"}

if __name__ == "__main__":
    print("Starting uvicorn server on 127.0.0.1:8000")
    uvicorn.run(app, host="127.0.0.1", port=8000, log_level="info")