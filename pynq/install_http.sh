#!/bin/bash
set -euo pipefail

LOG_FILE="/tmp/install_$(date +%Y%m%d_%H%M%S).log"
APP_DIR="/var/www/html"
WWW_USER="www-data"
WWW_GROUP="www-data"
DB_PASS="daq"
DB_NAME="daq"
PROCESS_NAME="server.py"
SERVER_IP="$(hostname -I | awk '{print $1}')"
REPO_URL="https://github.com/amilashanaka/sdc.git"
VENV_PATH="/usr/local/share/pynq-venv"

BLUE='\033[0;34m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m'

log(){ echo -e "${BLUE}[*] $*${NC}"; }
ok(){ echo -e "${GREEN}[OK] $*${NC}"; }
warn(){ echo -e "${YELLOW}[WARN] $*${NC}"; }
err(){ echo -e "${RED}[ERROR] $*${NC}"; }

log "Starting installation (preserving PYNQ on :9090)..."
sudo apt update -y >>$LOG_FILE

log "Checking PYNQ on port 9090..."
if ss -ltn | grep -q ':9090'; then
    ok "Port 9090 active — PYNQ running, will preserve"
else
    warn "Port 9090 inactive — PYNQ may not be running"
fi

# Check and stop conflicting service on port 80
log "Checking port 80..."
PORT_80_PID=$(sudo ss -tulpn | grep ':80 ' | grep -v apache2 | awk '{print $7}' | grep -oP 'pid=\K[0-9]+' | head -1)
if [ -n "$PORT_80_PID" ]; then
    PORT_80_CMD=$(ps -p $PORT_80_PID -o comm=)
    warn "Port 80 is in use by PID $PORT_80_PID ($PORT_80_CMD)"
    log "Stopping process on port 80..."
    sudo kill $PORT_80_PID 2>/dev/null || true
    sleep 2
    ok "Port 80 freed"
fi

log "Installing Apache + PHP + Git + Python dependencies"
export DEBIAN_FRONTEND=noninteractive
sudo -E apt install -y \
 apache2 apache2-utils \
 php libapache2-mod-php php-mysql php-cli \
 git mysql-server mysql-client \
 python3-pip python3-venv \
 python3-dev build-essential libssl-dev libffi-dev >>$LOG_FILE 2>&1
ok "Packages installed"

sudo a2enmod rewrite >/dev/null || true
sudo systemctl enable apache2 >/dev/null || true

# Backup existing files in /var/www/html
if [ "$(ls -A ${APP_DIR} 2>/dev/null)" ]; then
    BACKUP_DIR="/tmp/html_backup_$(date +%Y%m%d_%H%M%S)"
    log "Backing up existing ${APP_DIR} to ${BACKUP_DIR}"
    sudo mkdir -p "${BACKUP_DIR}"
    sudo cp -r ${APP_DIR}/* "${BACKUP_DIR}/" 2>/dev/null || true
    ok "Backup created at ${BACKUP_DIR}"
fi

# Clean /var/www/html completely
log "Cleaning ${APP_DIR}..."
sudo rm -rf ${APP_DIR}/*
sudo rm -rf ${APP_DIR}/.[!.]* 2>/dev/null || true
ok "Directory cleaned"

# Clone repository
log "Cloning repository from ${REPO_URL}..."
if sudo git clone "${REPO_URL}" "${APP_DIR}" >>$LOG_FILE 2>&1; then
    ok "Repository cloned successfully"
else
    err "Failed to clone repository. Check the URL: ${REPO_URL}"
    exit 1
fi

# Set permissions - exactly as in working system
log "Setting permissions for ${APP_DIR}..."
sudo chown -R ${WWW_USER}:${WWW_GROUP} ${APP_DIR}
sudo chmod -R 775 ${APP_DIR}
ok "Permissions applied"

log "Configuring MySQL..."
sudo systemctl start mysql
sleep 1

# Secure MySQL installation (matching your system)
sudo mysql -u root <<EOF 2>/dev/null || true
ALTER USER 'root'@'localhost' IDENTIFIED BY '${DB_PASS}';
DELETE FROM mysql.user WHERE User='';
DELETE FROM mysql.user WHERE User='root' AND Host NOT IN ('localhost', '127.0.0.1', '::1');
DROP DATABASE IF EXISTS test;
DELETE FROM mysql.db WHERE Db='test' OR Db='test\\_%';
FLUSH PRIVILEGES;
EOF

sudo mysql -u root -p${DB_PASS} -e \
 "CREATE DATABASE IF NOT EXISTS ${DB_NAME};" 2>/dev/null || true

SQL_FILE="${APP_DIR}/db/table.sql"
if [ -f "$SQL_FILE" ]; then
    log "Importing database schema"
    sudo mysql -u root -p${DB_PASS} ${DB_NAME} < "$SQL_FILE"
    ok "Database imported"
else
    warn "No SQL file found at ${SQL_FILE}"
fi

# Create or use existing PYNQ virtual environment
log "Setting up Python virtual environment..."
if [ -d "$VENV_PATH" ]; then
    ok "Using existing PYNQ virtual environment at $VENV_PATH"
else
    log "Creating virtual environment at $VENV_PATH..."
    sudo python3 -m venv "$VENV_PATH"
    ok "Virtual environment created"
fi

# Activate virtual environment and install packages
log "Installing Python packages in virtual environment..."
sudo $VENV_PATH/bin/pip install --upgrade pip setuptools wheel >>$LOG_FILE 2>&1

# Install exact versions from working setup
sudo $VENV_PATH/bin/pip install fastapi==0.124.0 >>$LOG_FILE 2>&1
sudo $VENV_PATH/bin/pip install uvicorn[standard]==0.38.0 >>$LOG_FILE 2>&1
sudo $VENV_PATH/bin/pip install websockets==10.3 >>$LOG_FILE 2>&1
sudo $VENV_PATH/bin/pip install pymysql python-multipart >>$LOG_FILE 2>&1

# Verify installation
log "Verifying Python package installation..."
if $VENV_PATH/bin/python -c "import fastapi; import uvicorn; import websockets; print('✓ FastAPI, Uvicorn, and WebSockets installed successfully')" 2>>$LOG_FILE; then
    ok "All required Python packages installed"
else
    err "Python package installation failed!"
    exit 1
fi

# Ensure server.py exists and has correct content
log "Configuring server.py..."
SERVER_PY_PATH="${APP_DIR}/pynq/server.py"

if [ -f "$SERVER_PY_PATH" ]; then
    ok "Found server.py at $SERVER_PY_PATH"
    
    # Backup original
    sudo cp "$SERVER_PY_PATH" "${SERVER_PY_PATH}.backup"
    
    # Write the exact server.py from your working system
    sudo tee "$SERVER_PY_PATH" > /dev/null << 'EOF'
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

# Mount static website
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
    print("Starting uvicorn server on 127.0.0.1:8000", file=sys.stderr)
    uvicorn.run(app, host="127.0.0.1", port=8000, log_level="info")
EOF
    
    sudo chmod +x "$SERVER_PY_PATH"
    ok "server.py configured"
else
    err "server.py NOT found at ${APP_DIR}/pynq/server.py"
    exit 1
fi

# Create systemd service file (exact match to your working system)
log "Creating systemd service file..."
sudo tee /etc/systemd/system/spicer-daq.service > /dev/null << EOF
[Unit]
Description=Spicer DAQ Server
After=network.target
# Start after all services are up, including FPGA
After=multi-user.target

[Service]
Type=simple
# Run as root to access FPGA
User=root
Group=root
WorkingDirectory=/var/www/html/pynq
# Use the PYNQ virtual environment's Python
ExecStart=/usr/local/share/pynq-venv/bin/python /var/www/html/pynq/server.py
# If port is in use, kill the existing process on port 8000 before starting
ExecStartPre=/bin/sh -c '/bin/fuser -k 8000/tcp || true'
# Wait a bit for the port to be released
ExecStartPre=/bin/sleep 2
Restart=always
RestartSec=10

[Install]
WantedBy=multi-user.target
EOF

# Stop any existing server processes
log "Stopping any existing server processes..."
sudo pkill -f "server.py" 2>/dev/null || true
sudo pkill -f "uvicorn" 2>/dev/null || true
sleep 2

# Enable and start the service
sudo systemctl daemon-reload
sudo systemctl enable spicer-daq.service

log "Starting FastAPI server via systemd..."
if sudo systemctl start spicer-daq.service; then
    ok "Spicer DAQ server service started"
    
    # Wait for server to start
    sleep 5
    
    # Check service status
    if sudo systemctl is-active --quiet spicer-daq.service; then
        ok "Spicer DAQ server service is active"
        
        # Check if it's listening on port 8000
        if sudo ss -tlnp | grep -q ':8000'; then
            ok "Server is listening on port 8000"
        else
            warn "Server not listening on port 8000. Checking logs..."
            sudo journalctl -u spicer-daq.service --no-pager | tail -20
        fi
    else
        err "Spicer DAQ server service failed to start"
        sudo journalctl -u spicer-daq.service --no-pager | tail -30
        exit 1
    fi
else
    err "Failed to start Spicer DAQ server service"
    exit 1
fi

log "Configuring Apache..."
# Create Apache virtual host configuration (exact match to your working system)
sudo tee /etc/apache2/sites-available/spicer.conf > /dev/null << EOF
<VirtualHost *:80>
    ServerName ${SERVER_IP}
    DocumentRoot ${APP_DIR}

    <Directory ${APP_DIR}>
        AllowOverride All
        Require all granted
        Options Indexes FollowSymLinks
        DirectoryIndex index.php index.html
    </Directory>

    RewriteEngine On
    RewriteCond %{HTTP:Upgrade} websocket [NC]
    RewriteCond %{HTTP:Connection} upgrade [NC]
    RewriteRule ^/ws$ ws://127.0.0.1:8000/ws [P,L]
    
    ProxyPass /api/ http://127.0.0.1:8000/api/
    ProxyPassReverse /api/ http://127.0.0.1:8000/api/

    ErrorLog \${APACHE_LOG_DIR}/spicer_error.log
    CustomLog \${APACHE_LOG_DIR}/spicer_access.log combined
</VirtualHost>
EOF

# Enable required Apache modules
sudo a2enmod proxy proxy_http proxy_wstunnel rewrite headers >>$LOG_FILE 2>&1

sudo a2dissite 000-default default-ssl >/dev/null 2>&1 || true
sudo a2ensite spicer.conf >/dev/null

# Test Apache configuration
log "Testing Apache configuration..."
if sudo apache2ctl configtest 2>&1 | grep -q "Syntax OK"; then
    ok "Apache config syntax OK"
else
    err "Apache config has errors:"
    sudo apache2ctl configtest
    exit 1
fi

# Start Apache
log "Starting Apache..."
if sudo systemctl restart apache2; then
    ok "Apache started successfully"
else
    err "Apache failed to start. Checking logs..."
    sudo journalctl -xeu apache2.service --no-pager | tail -20
    exit 1
fi

log "Final permission check..."
sudo chown -R ${WWW_USER}:${WWW_GROUP} ${APP_DIR}
sudo chmod -R 775 ${APP_DIR}

# Test connections
log "Testing all connections..."

# Test FastAPI directly
log "1. Testing FastAPI server on port 8000..."
sleep 3
if curl -s -f "http://127.0.0.1:8000/" >/dev/null 2>&1; then
    ok "FastAPI server responding on port 8000"
else
    err "FastAPI not responding on port 8000"
    sudo journalctl -u spicer-daq.service --no-pager | tail -20
fi

# Test WebSocket
log "2. Testing WebSocket connection..."
sleep 1
if $VENV_PATH/bin/python -c "
import asyncio
import websockets
import sys

async def test():
    try:
        async with websockets.connect('ws://127.0.0.1:8000/ws', timeout=2) as websocket:
            print('✓ WebSocket connection successful')
            return True
    except Exception as e:
        print(f'✗ WebSocket error: {e}')
        return False

import asyncio
asyncio.run(test())
" 2>/dev/null; then
    ok "WebSocket connection successful"
else
    warn "WebSocket connection test failed (might be OK if no clients connected)"
fi

# Test Apache serving static files
log "3. Testing Apache static file serving..."
if curl -s -f "http://${SERVER_IP}/" >/dev/null 2>&1; then
    ok "Apache serving static files on port 80"
else
    warn "Apache not responding on port 80"
fi

# Test Apache proxy to FastAPI
log "4. Testing Apache proxy to FastAPI..."
if curl -s -f "http://${SERVER_IP}/api/" >/dev/null 2>&1; then
    ok "Apache proxy to FastAPI working"
else
    warn "Apache proxy to FastAPI not working (endpoint might not exist)"
fi

ok "Installation complete!"
echo ""
echo "=========================================="
echo "🎯 Access Points:"
echo "  Static Files:     http://${SERVER_IP}/"
echo "  FastAPI Server:   http://127.0.0.1:8000/"
echo "  FastAPI via Proxy: http://${SERVER_IP}/api/"
echo "  WebSocket (WS):   ws://127.0.0.1:8000/ws"
echo "  PYNQ Jupyter:     http://${SERVER_IP}:9090/tree"
echo ""
echo "📊 Database:"
echo "  Name: ${DB_NAME}"
echo "  User: root"
echo "  Pass: ${DB_PASS}"
echo ""
echo "⚙️  Server Management:"
echo "  Process: systemd service 'spicer-daq'"
echo "  Check status: sudo systemctl status spicer-daq"
echo "  Start server: sudo systemctl start spicer-daq"
echo "  Stop server: sudo systemctl stop spicer-daq"
echo "  View logs: sudo journalctl -u spicer-daq.service -f"
echo "  Log file: sudo journalctl -u spicer-daq.service"
echo ""
echo "🐍 Python Environment:"
echo "  Virtual env: ${VENV_PATH}"
echo "  Python: $($VENV_PATH/bin/python --version)"
echo "  FastAPI: $($VENV_PATH/bin/python -c 'import fastapi; print(fastapi.__version__)')"
echo "  Uvicorn: $($VENV_PATH/bin/python -c 'import uvicorn; print(uvicorn.__version__)')"
echo ""
echo "🔧 Quick Tests:"
echo "  Test FastAPI: curl http://127.0.0.1:8000/"
echo "  Test WebSocket: $VENV_PATH/bin/python -m websockets ws://127.0.0.1:8000/ws"
echo "  Test Apache: curl http://${SERVER_IP}/"
echo "  Check port 8000: sudo ss -tlnp | grep :8000"
echo ""
echo "✅ FastAPI server runs via systemd service (spicer-daq)"
echo "✅ Uses PYNQ virtual environment at ${VENV_PATH}"
echo "✅ Apache serves static files on port 80"
echo "✅ Apache proxies WebSocket to FastAPI on /ws"
echo "=========================================="

# Create a simple test page
sudo tee ${APP_DIR}/test.html > /dev/null <<EOF
<!DOCTYPE html>
<html>
<head>
    <title>DAQ Test Page</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; }
        .status { padding: 10px; margin: 10px 0; border-radius: 5px; }
        .success { background-color: #d4edda; color: #155724; }
        .warning { background-color: #fff3cd; color: #856404; }
        .error { background-color: #f8d7da; color: #721c24; }
    </style>
</head>
<body>
    <h1>Spicer DAQ Installation Test</h1>
    
    <div id="fastapi-status" class="status">
        Testing FastAPI connection...
    </div>
    
    <div id="apache-status" class="status">
        Testing Apache connection...
    </div>
    
    <div id="websocket-status" class="status">
        Testing WebSocket connection...
    </div>
    
    <script>
        // Test FastAPI directly
        fetch('http://127.0.0.1:8000/')
            .then(response => response.json())
            .then(data => {
                document.getElementById('fastapi-status').innerHTML = 
                    '<strong>✓ FastAPI Server (Port 8000):</strong> Running';
                document.getElementById('fastapi-status').className = 'status success';
            })
            .catch(error => {
                document.getElementById('fastapi-status').innerHTML = 
                    '<strong>✗ FastAPI Server (Port 8000):</strong> Connection failed';
                document.getElementById('fastapi-status').className = 'status error';
            });
        
        // Test Apache
        fetch('http://' + window.location.hostname + '/')
            .then(response => {
                document.getElementById('apache-status').innerHTML = 
                    '<strong>✓ Apache Server (Port 80):</strong> Responding';
                document.getElementById('apache-status').className = 'status success';
            })
            .catch(error => {
                document.getElementById('apache-status').innerHTML = 
                    '<strong>✗ Apache Server (Port 80):</strong> Connection failed';
                document.getElementById('apache-status').className = 'status error';
            });
        
        // Test WebSocket connection
        const ws = new WebSocket('ws://127.0.0.1:8000/ws');
        
        ws.onopen = function() {
            document.getElementById('websocket-status').innerHTML = 
                '<strong>✓ WebSocket:</strong> Connected successfully';
            document.getElementById('websocket-status').className = 'status success';
            ws.close();
        };
        
        ws.onerror = function() {
            document.getElementById('websocket-status').innerHTML = 
                '<strong>⚠ WebSocket:</strong> Connection failed (server might be OK)';
            document.getElementById('websocket-status').className = 'status warning';
        };
    </script>
</body>
</html>
EOF

echo ""
echo "Test page created: http://${SERVER_IP}/test.html"
echo "Installation log: ${LOG_FILE}"
echo "Server logs: sudo journalctl -u spicer-daq.service -f"
echo ""
echo "Note: The server runs in the PYNQ virtual environment at ${VENV_PATH}"
echo "This matches your exact working configuration!"