from fastapi import FastAPI, WebSocket
from fastapi.staticfiles import StaticFiles
from fastapi.responses import FileResponse
import uvicorn
import asyncio
from daq import Daq

app = FastAPI()

# Mount static website
app.mount("/static", StaticFiles(directory="static"), name="static")

# Create DAQ object
daq = Daq()
daq.start_background()

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
    except Exception:
        pass
    finally:
        pass

if __name__ == "__main__":
    uvicorn.run(app, host="0.0.0.0", port=8000)