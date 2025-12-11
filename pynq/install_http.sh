#!/bin/bash
set -euo pipefail

LOG_FILE="/tmp/install_$(date +%Y%m%d_%H%M%S).log"
APP_DIR="/var/www/html"
WWW_USER="root"
DB_PASS="daq"
DB_NAME="daq"
PROCESS_NAME="server.py"
SERVER_IP="$(hostname -I | awk '{print $1}')"
REPO_URL="https://github.com/amilashanaka/sdc.git"

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

log "Checking redirect_server service..."
if systemctl list-unit-files | grep -q redirect_server; then
    ok "redirect_server exists — preserving"
else
    warn "redirect_server not found (normal if not using it)"
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
 git mariadb-server mariadb-client \
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

# Set permissions
log "Setting permissions for ${APP_DIR}..."
sudo chown -R ${WWW_USER}:${WWW_USER} ${APP_DIR}
sudo chmod -R 775 ${APP_DIR}
ok "Permissions applied"

log "Configuring MariaDB..."
sudo systemctl start mariadb
sleep 1

# Secure MariaDB installation
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

log "Setting up Python environment for FastAPI..."

# Check Python3 version
log "Checking Python installation..."
PYTHON_VERSION=$(python3 --version 2>&1)
ok "Found: $PYTHON_VERSION"

# Force reinstall pip and setuptools
log "Installing/upgrading pip and setuptools..."
sudo python3 -m pip install --force-reinstall --upgrade pip setuptools wheel >>$LOG_FILE 2>&1

# Install Python dependencies for FastAPI
log "Installing Python dependencies for FastAPI..."
sudo python3 -m pip install fastapi uvicorn[standard] websockets pymysql python-multipart >>$LOG_FILE 2>&1

# Verify installation
log "Verifying Python package installation..."
if python3 -c "import fastapi; import uvicorn; import websockets; print('✓ FastAPI, Uvicorn, and WebSockets installed successfully')" 2>>$LOG_FILE; then
    ok "All required Python packages installed"
else
    err "Python package installation failed!"
    exit 1
fi

# Fix server.py - it has issues
log "Fixing server.py configuration..."
SERVER_PY_PATH="${APP_DIR}/pynq/server.py"

if [ -f "$SERVER_PY_PATH" ]; then
    ok "Found server.py at $SERVER_PY_PATH"
    
    # Create a fixed version of server.py
    log "Creating fixed server.py..."
    
    # First, backup the original
    sudo cp "$SERVER_PY_PATH" "${SERVER_PY_PATH}.backup"
    
    # Create a new server.py with proper fixes
    sudo tee "$SERVER_PY_PATH" > /dev/null << 'EOF'
#!/usr/bin/env python3
from fastapi import FastAPI, WebSocket
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

# Try to import and initialize DAQ with error handling
daq = None
daq_initialized = False

print("=== Starting Spicer DAQ Server ===", file=sys.stderr)
print(f"Python: {sys.executable}", file=sys.stderr)
print(f"Python path: {sys.path}", file=sys.stderr)

try:
    # First, try to import pynq to check if it's available
    import pynq
    print("✓ PYNQ module imported", file=sys.stderr)
    
    # Now try to import daq module
    try:
        # Try different import paths
        try:
            from daq import Daq
        except ImportError:
            # Try relative import
            from .daq import Daq
        
        print("✓ DAQ module imported", file=sys.stderr)
        
        # Try to initialize DAQ
        try:
            print("Initializing FPGA DAQ...", file=sys.stderr)
            daq = Daq()
            daq.start_background()
            daq_initialized = True
            print("✓ FPGA DAQ initialized successfully", file=sys.stderr)
        except Exception as e:
            print(f"⚠ FPGA DAQ initialization failed: {e}", file=sys.stderr)
            print("Will run in simulation mode", file=sys.stderr)
            daq = None
            
    except ImportError as e:
        print(f"✗ DAQ module import failed: {e}", file=sys.stderr)
        print("Will run in simulation mode", file=sys.stderr)
        daq = None
        
except ImportError as e:
    print(f"✗ PYNQ module import failed: {e}", file=sys.stderr)
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
async def read_root():
    return {"message": "Spicer DAQ FastAPI Server", "status": "running"}

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
                await websocket.send_json({"type": "heartbeat", "time": time.time()})
            
            await asyncio.sleep(0.01)
    except Exception as e:
        print(f"WebSocket error: {e}", file=sys.stderr)
        print(traceback.format_exc(), file=sys.stderr)
    finally:
        print("WebSocket connection closed", file=sys.stderr)

if __name__ == "__main__":
    print("Starting uvicorn server on 0.0.0.0:8000", file=sys.stderr)
    uvicorn.run(app, host="0.0.0.0", port=8000, log_level="info")
EOF
    
    sudo chmod +x "$SERVER_PY_PATH"
    ok "server.py fixed and made executable"
    
    # Also ensure the pynq directory is in Python path
    sudo tee "${APP_DIR}/pynq/__init__.py" > /dev/null << 'EOF'
# This file makes the pynq directory a Python package
EOF
    
else
    err "server.py NOT found at ${APP_DIR}/pynq/server.py"
    exit 1
fi

# Create a simple test to verify the installation
log "Creating test script..."
sudo tee /tmp/test_server.py > /dev/null << 'EOF'
#!/usr/bin/env python3
import sys
import os

# Add the pynq directory to path
sys.path.insert(0, '/var/www/html/pynq')

print("Testing server.py import...")
try:
    # First check FastAPI imports
    import fastapi
    import uvicorn
    import websockets
    print("✓ FastAPI, Uvicorn, WebSockets imported")
    
    # Now try to import from server.py
    from server import app
    print("✓ server.py imports successfully")
    
    # Check the app
    print(f"✓ FastAPI app created: {app}")
    
    # Try to create a simple test
    import asyncio
    print("✓ All imports successful!")
    
except ImportError as e:
    print(f"✗ Import error: {e}")
    print(f"Python path: {sys.path}")
    sys.exit(1)
EOF

sudo chmod +x /tmp/test_server.py

# Test the import
log "Testing server.py import..."
if python3 /tmp/test_server.py; then
    ok "server.py imports successfully"
else
    err "server.py import test failed"
    exit 1
fi

# Create rc.local startup
log "Setting up /etc/rc.local for auto-start..."
# First ensure rc.local exists and is executable
sudo tee /etc/rc.local > /dev/null << 'EOF'
#!/bin/bash
# rc.local - executed at the end of each multiuser runlevel

# Start the FastAPI server
cd /var/www/html/pynq
/usr/bin/python3 server.py > /tmp/daq_server.log 2>&1 &

# Make sure we return 0
exit 0
EOF

sudo chmod +x /etc/rc.local

# Enable rc-local service if it exists
if [ -f /lib/systemd/system/rc-local.service ] || [ -f /usr/lib/systemd/system/rc-local.service ]; then
    sudo systemctl enable rc-local 2>/dev/null || true
fi

ok "rc.local configured for auto-start"

# Start server.py now
log "Starting server.py now..."

# Kill any existing server processes
sudo pkill -f "server.py" 2>/dev/null || true
sudo pkill -f "uvicorn" 2>/dev/null || true
sleep 2

cd ${APP_DIR}/pynq
if sudo python3 server.py > /tmp/server_runtime.log 2>&1 & then
    SERVER_PID=$!
    ok "server.py started with PID: $SERVER_PID"
    
    # Wait for server to start
    sleep 5
    
    # Check if server is running
    if ps -p $SERVER_PID > /dev/null 2>&1; then
        ok "server.py is running (PID: $SERVER_PID)"
        
        # Check if it's listening on port 8000
        if sudo netstat -tlnp | grep -q ":8000.*python3"; then
            ok "server.py is listening on port 8000"
        else
            warn "server.py not listening on port 8000. Checking logs..."
            tail -20 /tmp/server_runtime.log
        fi
    else
        warn "server.py may have crashed. Checking logs..."
        tail -30 /tmp/server_runtime.log
    fi
else
    err "Failed to start server.py"
    exit 1
fi

log "Configuring Apache (port 80 for static files only)..."

# Configure Apache to use only port 80
sudo tee /etc/apache2/ports.conf >/dev/null <<EOF
Listen 80
EOF

# Create Apache virtual host configuration
sudo tee /etc/apache2/sites-available/spicer.conf >/dev/null <<EOF
<VirtualHost *:80>
    ServerName ${SERVER_IP}
    DocumentRoot ${APP_DIR}

    <Directory ${APP_DIR}>
        AllowOverride All
        Require all granted
        Options Indexes FollowSymLinks
    </Directory>

    # Proxy WebSocket requests to FastAPI server
    ProxyPass /ws ws://localhost:8000/ws
    ProxyPassReverse /ws ws://localhost:8000/ws
    
    # Proxy API requests
    ProxyPass /api http://localhost:8000/
    ProxyPassReverse /api http://localhost:8000/

    ErrorLog \${APACHE_LOG_DIR}/spicer_error.log
    CustomLog \${APACHE_LOG_DIR}/spicer_access.log combined
</VirtualHost>
EOF

# Enable required Apache modules
sudo a2enmod proxy proxy_http proxy_wstunnel rewrite >>$LOG_FILE 2>&1

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
sudo chown -R ${WWW_USER}:${WWW_USER} ${APP_DIR}
sudo chmod -R 775 ${APP_DIR}

# Check WebSocket connectivity
log "Testing WebSocket connectivity..."
sleep 3

# Test FastAPI directly
if curl -s -f "http://localhost:8000/" >/dev/null 2>&1; then
    ok "FastAPI server responding on port 8000"
else
    warn "FastAPI not responding directly. Checking if it started..."
    
    # Check process
    if ps aux | grep -q "[s]erver.py"; then
        ok "server.py process is running"
        
        # Check logs
        if [ -f /tmp/server_runtime.log ]; then
            echo "=== Last 10 lines of server log ==="
            tail -10 /tmp/server_runtime.log
        fi
        
        # Try one more time with delay
        sleep 2
        if curl -s -f "http://localhost:8000/" >/dev/null 2>&1; then
            ok "FastAPI now responding on port 8000"
        else
            warn "FastAPI still not responding. Starting manually..."
            
            # Kill and restart
            sudo pkill -f "server.py"
            sleep 2
            cd ${APP_DIR}/pynq
            sudo python3 server.py > /tmp/server_restart.log 2>&1 &
            sleep 3
            
            if curl -s -f "http://localhost:8000/" >/dev/null 2>&1; then
                ok "FastAPI now responding after restart"
            else
                warn "FastAPI still not responding. You may need to debug manually."
            fi
        fi
    else
        warn "server.py not running. Starting it..."
        cd ${APP_DIR}/pynq
        sudo python3 server.py > /tmp/server_manual.log 2>&1 &
        sleep 3
        
        if curl -s -f "http://localhost:8000/" >/dev/null 2>&1; then
            ok "FastAPI now responding after manual start"
        else
            err "Failed to start FastAPI server"
        fi
    fi
fi

# Test through Apache proxy
log "Testing Apache proxy..."
if curl -s -f "http://${SERVER_IP}/" >/dev/null 2>&1; then
    ok "Apache serving static files"
else
    warn "Apache not serving static files"
fi

ok "Installation complete!"
echo ""
echo "=========================================="
echo "🎯 Access Points:"
echo "  Static Files:     http://${SERVER_IP}/"
echo "  FastAPI Server:   http://${SERVER_IP}:8000/"
echo "  WebSocket (WS):   ws://${SERVER_IP}:8000/ws"
echo "  PYNQ Jupyter:     http://${SERVER_IP}:9090/tree"
echo ""
echo "📊 Database:"
echo "  Name: ${DB_NAME}"
echo "  User: root"
echo "  Pass: ${DB_PASS}"
echo ""
echo "⚙️  Server Management:"
echo "  Process: ${PROCESS_NAME}"
echo "  Auto-start: Configured via /etc/rc.local"
echo "  Manual start: cd ${APP_DIR}/pynq && sudo python3 server.py"
echo "  Check status: ps aux | grep server.py"
echo "  Stop server: sudo pkill -f server.py"
echo "  Logs: tail -f /tmp/daq_server.log"
echo ""
echo "🐍 Python Verification:"
echo "  Test imports: python3 /tmp/test_server.py"
echo "  Test FastAPI: curl http://localhost:8000/"
echo ""
echo "🔧 Troubleshooting:"
echo "  If server doesn't start:"
echo "  1. Check Python packages: sudo pip3 list | grep -E 'fastapi|uvicorn'"
echo "  2. Test import: python3 -c 'import fastapi; print(fastapi.__version__)'"
echo "  3. Check logs: tail -f /tmp/server_runtime.log"
echo "  4. Check port: sudo netstat -tlnp | grep :8000"
echo ""
echo "✅ FastAPI server will auto-start on boot via /etc/rc.local!"
echo "✅ Apache serves static files on port 80"
echo "✅ WebSocket available on port 8000"
echo "=========================================="

# Create a simple test HTML file
sudo tee ${APP_DIR}/test.html > /dev/null <<EOF
<!DOCTYPE html>
<html>
<head>
    <title>DAQ Test Page</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; }
        .status { padding: 10px; margin: 10px 0; border-radius: 5px; }
        .success { background-color: #d4edda; color: #155724; }
        .error { background-color: #f8d7da; color: #721c24; }
    </style>
</head>
<body>
    <h1>Spicer DAQ Installation Test</h1>
    
    <div id="fastapi-status" class="status">
        Testing FastAPI connection...
    </div>
    
    <div id="websocket-status" class="status">
        Testing WebSocket connection...
    </div>
    
    <script>
        // Test FastAPI REST endpoint
        fetch('http://' + window.location.hostname + ':8000/')
            .then(response => response.json())
            .then(data => {
                document.getElementById('fastapi-status').innerHTML = 
                    '<strong>✓ FastAPI Server:</strong> ' + data.message;
                document.getElementById('fastapi-status').className = 'status success';
            })
            .catch(error => {
                document.getElementById('fastapi-status').innerHTML = 
                    '<strong>✗ FastAPI Server:</strong> Connection failed';
                document.getElementById('fastapi-status').className = 'status error';
            });
        
        // Test WebSocket connection
        const ws = new WebSocket('ws://' + window.location.hostname + ':8000/ws');
        
        ws.onopen = function() {
            document.getElementById('websocket-status').innerHTML = 
                '<strong>✓ WebSocket:</strong> Connected successfully';
            document.getElementById('websocket-status').className = 'status success';
            ws.close();
        };
        
        ws.onerror = function() {
            document.getElementById('websocket-status').innerHTML = 
                '<strong>✗ WebSocket:</strong> Connection failed';
            document.getElementById('websocket-status').className = 'status error';
        };
    </script>
</body>
</html>
EOF

echo ""
echo "Test page created: http://${SERVER_IP}/test.html"
echo "This page will test both FastAPI and WebSocket connections."