from fastapi import FastAPI, WebSocket
import uvicorn
import asyncio
from daq import Daq

app = FastAPI()

daq = Daq()
daq.start_background()

@app.websocket("/ws")
async def websocket_data(websocket: WebSocket):
    await websocket.accept()

    try:
        while True:
            data = daq.read_streaming()
            
            if data:
                await websocket.send_bytes(data)
            else:
                await websocket.send_json({"type": "heartbeat"})

            await asyncio.sleep(0.01)
    except Exception:
        pass
    finally:
        pass

if __name__ == "__main__":
    uvicorn.run(app, host="127.0.0.1", port=8000)