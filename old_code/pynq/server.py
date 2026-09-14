#!/usr/bin/env python3
"""
Spicer DAQ WebSocket Server

The HTTPS web interface is served by Apache on port 443 and proxies /ws to
this FastAPI process. When the web interface is used, this process asks
main.py to enter debug mode so the legacy TCP protocol stops and releases DAQ.
"""

from fastapi import FastAPI, WebSocket, WebSocketDisconnect
import uvicorn
import asyncio
import sys
import traceback
import time
from threading import Lock

app = FastAPI()

# Global DAQ instance. It is intentionally lazy so starting this service does
# not take DAQ away from the legacy TCP process until the web UI is opened.
daq = None
daq_initialized = False
daq_lock = Lock()
last_debug_request = 0

# Track active WebSocket connections
active_connections = set()

print("=== Spicer DAQ WebSocket Server ===", file=sys.stderr)
print(f"Python: {sys.executable}", file=sys.stderr)
print(f"Python version: {sys.version}", file=sys.stderr)


def request_main_debug_mode(force=False):
    """Ask main.py to close legacy TCP and release DAQ for web streaming."""
    global last_debug_request
    now = time.time()
    if not force and now - last_debug_request < 5:
        return True

    last_debug_request = now
    try:
        import ipc
        client = ipc.Ipc(ipc.IPC_CLIENT)
        client.send("DebugOn")
        reply, _ = client.receive()
        client.close()
        return reply == "1"
    except Exception as e:
        print(f"Unable to request main debug mode: {e}", file=sys.stderr)
        return False


def release_main_debug_mode():
    """Tell main.py the web UI has released DAQ and TCP can resume."""
    try:
        import ipc
        client = ipc.Ipc(ipc.IPC_CLIENT)
        client.send("DebugOff")
        client.receive()
        client.close()
    except Exception as e:
        print(f"Unable to release main debug mode: {e}", file=sys.stderr)


class SimulatedDAQ:
    """Simulated 16-channel DAQ for testing without FPGA hardware."""

    def __init__(self):
        self.running = False
        self.thread = None
        self.data_buffer = bytearray()
        self.lock = Lock()
        self.sample_counter = 0

    def start_background(self):
        if self.running:
            return
        from threading import Thread

        self.running = True
        self.thread = Thread(target=self._generate_data, daemon=True)
        self.thread.start()
        print("Simulated DAQ started", file=sys.stderr)

    def _generate_data(self):
        import numpy as np

        while self.running:
            buffer = bytearray()
            for ch in range(16):
                t = np.arange(1250) + self.sample_counter
                frequency = 0.01 * (ch + 1)
                signal = np.sin(2 * np.pi * frequency * t) + 0.1 * np.random.randn(1250)
                values = (1000 * (ch + 1) * signal).astype(np.int16)
                buffer.extend(values.tobytes())

            with self.lock:
                self.data_buffer = buffer

            self.sample_counter += 1250
            time.sleep(0.1)

    def read_streaming(self):
        with self.lock:
            if self.data_buffer:
                data = bytes(self.data_buffer)
                self.data_buffer = bytearray()
                return data
        return None

    def close(self):
        self.running = False
        if self.thread:
            self.thread.join(timeout=2.0)


def get_daq():
    """Lazy-start DAQ after web access has put main.py into debug mode."""
    global daq
    global daq_initialized

    if daq is not None:
        return daq

    with daq_lock:
        if daq is not None:
            return daq

        request_main_debug_mode(force=True)
        try:
            import pynq
            print("PYNQ module loaded", file=sys.stderr)

            from daq import Daq
            print("DAQ module loaded", file=sys.stderr)

            print("Initializing FPGA DAQ in web debug mode...", file=sys.stderr)
            daq = Daq(debug_mode=True)
            daq.start_background()
            daq_initialized = True
            print("FPGA DAQ initialized for web debug mode", file=sys.stderr)
        except Exception as e:
            print(f"FPGA DAQ unavailable: {e}", file=sys.stderr)
            print("Running in simulation mode", file=sys.stderr)
            daq = SimulatedDAQ()
            daq.start_background()
            daq_initialized = False

        return daq


def release_daq_if_idle():
    """Close the web DAQ when no browser signal stream remains open."""
    global daq
    global daq_initialized

    if active_connections or daq is None:
        return

    with daq_lock:
        if active_connections or daq is None:
            return
        try:
            daq.close()
        except Exception as e:
            print(f"Unable to close web DAQ: {e}", file=sys.stderr)
        daq = None
        daq_initialized = False
        release_main_debug_mode()


@app.websocket("/ws")
async def websocket_endpoint(websocket: WebSocket):
    """
    WebSocket endpoint for real-time DAQ data streaming.
    Accepts connection and streams binary data from DAQ.
    """
    client_id = f"{websocket.client.host}:{websocket.client.port}"

    try:
        request_main_debug_mode(force=True)
        stream_daq = get_daq()
        await websocket.accept()
        active_connections.add(client_id)
        print(f"WebSocket connected: {client_id} (total: {len(active_connections)})", file=sys.stderr)

        while True:
            request_main_debug_mode()
            data = stream_daq.read_streaming() if stream_daq else None

            if data:
                await websocket.send_bytes(data)
            else:
                await websocket.send_json({"type": "heartbeat", "timestamp": time.time()})

            await asyncio.sleep(0.01)

    except WebSocketDisconnect:
        print(f"WebSocket disconnected: {client_id}", file=sys.stderr)
    except Exception as e:
        print(f"WebSocket error ({client_id}): {e}", file=sys.stderr)
        print(traceback.format_exc(), file=sys.stderr)
    finally:
        active_connections.discard(client_id)
        release_daq_if_idle()
        print(f"Active connections: {len(active_connections)}", file=sys.stderr)


@app.get("/health")
async def health_check():
    """Health check endpoint."""
    request_main_debug_mode()
    return {
        "status": "healthy",
        "daq_mode": "hardware" if daq_initialized else "simulation",
        "active_connections": len(active_connections),
        "web_debug_mode": daq is not None,
        "timestamp": time.time(),
    }


@app.get("/")
async def root():
    """Root endpoint - redirect info."""
    request_main_debug_mode()
    return {
        "service": "Spicer DAQ WebSocket Server",
        "version": "2.0",
        "websocket": "wss://<device-ip>/ws",
        "health": "/health",
        "note": "HTTPS web access enables debug mode and suspends legacy TCP",
    }


if __name__ == "__main__":
    print("Starting WebSocket server on 127.0.0.1:8000", file=sys.stderr)
    print("Web interface: https://[server-ip]/ (Apache/PHP)", file=sys.stderr)
    print("WebSocket endpoint: wss://[server-ip]/ws", file=sys.stderr)
    print("-" * 50, file=sys.stderr)

    uvicorn.run(
        app,
        host="127.0.0.1",
        port=8000,
        log_level="info",
        access_log=True,
    )
