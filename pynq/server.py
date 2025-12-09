from fastapi import FastAPI, WebSocket
import uvicorn
import asyncio
from daq import Daq

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

# API endpoint for scope.php stats
@app.get("/api/stats")
async def get_stats():
    import time
    import psutil
    import os
    
    return {
        "status": "running",
        "timestamp": time.time(),
        "process_id": os.getpid(),
        "cpu_percent": psutil.cpu_percent(),
        "memory_percent": psutil.Process().memory_percent(),
        "connections": 1  # You can track active WebSocket connections
    }

if __name__ == "__main__":
    # Run on localhost only for security
    uvicorn.run(app, host="127.0.0.1", port=8000, log_level="info")