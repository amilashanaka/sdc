#!/usr/bin/env python3
"""
Spicer DAQ WebSocket Server (DEBUG mode only)

mode_manager.py starts this process only when flags.run_mode = 1 and starts
main.py + udp.py only when flags.run_mode = 0, so the two never run together
and this process is the sole owner of the DAQ while it is alive.

The HTTPS web interface is served by Apache on port 443 and proxies /ws here.
"""

from fastapi import FastAPI, WebSocket, WebSocketDisconnect
import uvicorn
import asyncio
import os
import sys
import traceback
import time
from threading import Lock

app = FastAPI()

# Global DAQ instance. It stays lazy so the FPGA is only programmed once a
# browser actually connects, but it is never shared with the RUN mode
# processes: those are not running while this process is up.
daq = None
daq_initialized = False
daq_lock = Lock()

# Fake data is only used for bench testing without FPGA hardware.
SIMULATE = os.environ.get("SPICER_SIMULATE", "0") == "1"

# Track active WebSocket connections
active_connections = set()

print("=== Spicer DAQ WebSocket Server ===", file=sys.stderr)
print(f"Python: {sys.executable}", file=sys.stderr)
print(f"Python version: {sys.version}", file=sys.stderr)


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
    """Start DAQ once a browser connects. No other process holds the FPGA here."""
    global daq
    global daq_initialized

    if daq is not None:
        return daq

    with daq_lock:
        if daq is not None:
            return daq

        if SIMULATE:
            daq = SimulatedDAQ()
            daq.start_background()
            daq_initialized = False
            print("Running in simulation mode (SPICER_SIMULATE=1)", file=sys.stderr)
            return daq

        try:
            from daq import Daq
            print("Initializing FPGA DAQ...", file=sys.stderr)
            daq = Daq(debug_mode=True)
            daq.start_background()
            daq_initialized = True
            print("FPGA DAQ initialized", file=sys.stderr)
        except Exception as e:
            print(f"FPGA DAQ unavailable: {e}", file=sys.stderr)
            daq = None
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


@app.websocket("/ws")
async def websocket_endpoint(websocket: WebSocket):
    """
    WebSocket endpoint for real-time DAQ data streaming.
    Accepts connection and streams binary data from DAQ.
    """
    client_id = f"{websocket.client.host}:{websocket.client.port}"

    try:
        stream_daq = get_daq()
        await websocket.accept()
        active_connections.add(client_id)
        print(f"WebSocket connected: {client_id} (total: {len(active_connections)})", file=sys.stderr)

        while True:
            if stream_daq is None:
                await websocket.send_json({"type": "error", "message": "DAQ unavailable"})
                await asyncio.sleep(1.0)
                continue

            data = stream_daq.read_streaming()

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
    return {
        "service": "Spicer DAQ WebSocket Server",
        "version": "2.0",
        "websocket": "wss://<device-ip>/ws",
        "health": "/health",
        "note": "DEBUG mode service; main.py and udp.py run only in RUN mode",
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
