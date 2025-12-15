#!/usr/bin/env python3
"""
Spicer DAQ WebSocket Server
Provides real-time data streaming via WebSocket only.
Web interface served by Apache/PHP on port 443.
"""

from fastapi import FastAPI, WebSocket, WebSocketDisconnect
import uvicorn
import asyncio
import sys
import traceback
import time

app = FastAPI()

# Global DAQ instance
daq = None
daq_initialized = False

print("=== Spicer DAQ WebSocket Server ===", file=sys.stderr)
print(f"Python: {sys.executable}", file=sys.stderr)
print(f"Python version: {sys.version}", file=sys.stderr)

# Initialize DAQ with graceful fallback
try:
    import pynq
    print("✓ PYNQ module loaded", file=sys.stderr)
    
    from daq import Daq
    print("✓ DAQ module loaded", file=sys.stderr)
    
    print("Initializing FPGA DAQ...", file=sys.stderr)
    daq = Daq()
    daq.start_background()
    daq_initialized = True
    print("✓ FPGA DAQ initialized successfully", file=sys.stderr)
    
except Exception as e:
    print(f"⚠ FPGA DAQ unavailable: {e}", file=sys.stderr)
    print("Running in simulation mode", file=sys.stderr)
    
    # Simulated DAQ for testing without hardware
    import random
    import numpy as np
    from threading import Thread, Lock
    
    class SimulatedDAQ:
        """Simulated 16-channel DAQ for testing"""
        def __init__(self):
            self.running = False
            self.thread = None
            self.data_buffer = bytearray()
            self.lock = Lock()
            self.sample_counter = 0
            
        def start_background(self):
            """Start background data generation"""
            self.running = True
            self.thread = Thread(target=self._generate_data, daemon=True)
            self.thread.start()
            print("✓ Simulated DAQ started", file=sys.stderr)
        
        def _generate_data(self):
            """Generate simulated 16-channel waveform data"""
            while self.running:
                buffer = bytearray()
                
                # Generate 2500 samples per channel (16 channels)
                for ch in range(16):
                    # Create realistic waveform: sine + noise
                    t = np.arange(2500) + self.sample_counter
                    frequency = 0.01 * (ch + 1)  # Different freq per channel
                    
                    # Sine wave + random noise
                    sine_wave = np.sin(2 * np.pi * frequency * t)
                    noise = 0.1 * np.random.randn(2500)
                    signal = sine_wave + noise
                    
                    # Scale to 16-bit integer range
                    amplitude = 1000 * (ch + 1)
                    values = (amplitude * signal).astype(np.int16)
                    
                    buffer.extend(values.tobytes())
                
                # Thread-safe buffer update
                with self.lock:
                    self.data_buffer = buffer
                
                self.sample_counter += 2500
                time.sleep(0.1)  # 10 Hz update rate
        
        def read_streaming(self):
            """Read and clear current buffer"""
            with self.lock:
                if self.data_buffer:
                    data = bytes(self.data_buffer)
                    self.data_buffer = bytearray()
                    return data
            return None
    
    daq = SimulatedDAQ()
    daq.start_background()

# Track active WebSocket connections
active_connections = set()

@app.websocket("/ws")
async def websocket_endpoint(websocket: WebSocket):
    """
    WebSocket endpoint for real-time DAQ data streaming.
    Accepts connection and streams binary data from DAQ.
    """
    client_id = f"{websocket.client.host}:{websocket.client.port}"
    
    try:
        await websocket.accept()
        active_connections.add(client_id)
        print(f"✓ WebSocket connected: {client_id} (total: {len(active_connections)})", file=sys.stderr)
        
        while True:
            # Read data from DAQ
            data = daq.read_streaming() if daq else None
            
            if data:
                # Send binary data (int16 samples)
                await websocket.send_bytes(data)
            else:
                # Send heartbeat to keep connection alive
                await websocket.send_json({"type": "heartbeat", "timestamp": time.time()})
            
            # 10ms refresh rate (100 Hz)
            await asyncio.sleep(0.01)
            
    except WebSocketDisconnect:
        print(f"✓ WebSocket disconnected: {client_id}", file=sys.stderr)
    except Exception as e:
        print(f"✗ WebSocket error ({client_id}): {e}", file=sys.stderr)
        print(traceback.format_exc(), file=sys.stderr)
    finally:
        active_connections.discard(client_id)
        print(f"  Active connections: {len(active_connections)}", file=sys.stderr)

@app.get("/health")
async def health_check():
    """Health check endpoint"""
    return {
        "status": "healthy",
        "daq_mode": "hardware" if daq_initialized else "simulation",
        "active_connections": len(active_connections),
        "timestamp": time.time()
    }

@app.get("/")
async def root():
    """Root endpoint - redirect info"""
    return {
        "service": "Spicer DAQ WebSocket Server",
        "version": "2.0",
        "websocket": "ws://127.0.0.1:8000/ws",
        "health": "/health",
        "note": "Web interface served by Apache on port 443"
    }

if __name__ == "__main__":
    print("Starting WebSocket server on 127.0.0.1:8000", file=sys.stderr)
    print("Web interface: https://[server-ip]/ (Apache/PHP)", file=sys.stderr)
    print("WebSocket endpoint: ws://127.0.0.1:8000/ws", file=sys.stderr)
    print("-" * 50, file=sys.stderr)
    
    # Run uvicorn server
    uvicorn.run(
        app, 
        host="127.0.0.1",  # Internal only - proxied by Apache
        port=8000,
        log_level="info",
        access_log=True
    )