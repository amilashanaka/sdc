from fastapi import FastAPI, WebSocket
import uvicorn
import asyncio
from daq import Daq
import os

# Set the XRT environment for PYNQ-Z1
os.environ['XILINX_XRT'] = '/usr'
os.environ['LD_LIBRARY_PATH'] = '/usr/lib:' + os.environ.get('LD_LIBRARY_PATH', '')

app = FastAPI()

# Create DAQ object
daq = Daq()
daq.start_background()

@app.websocket("/ws")
async def websocket_data(websocket: WebSocket):
    await websocket.accept()

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
        await websocket.close()

# We don't need the static routes anymore because Apache will handle them.
# But if you want to keep the API server serving some API endpoints, you can add them here.

if __name__ == "__main__":
    uvicorn.run(app, host="127.0.0.1", port=8000, log_level="info")