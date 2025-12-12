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
sudo apt update -y >>$LOG_FILE 2>&1

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

log "Installing Apache + PHP + Git + Python2 dependencies"
export DEBIAN_FRONTEND=noninteractive

# First, try to add universe repository (for older Python2 packages)
sudo add-apt-repository universe -y >>$LOG_FILE 2>&1 || true
sudo apt update -y >>$LOG_FILE 2>&1

# Install base packages first
sudo -E apt install -y \
 apache2 apache2-utils \
 php libapache2-mod-php php-mysql php-cli \
 git mariadb-server mariadb-client \
 build-essential libssl-dev libffi-dev >>$LOG_FILE 2>&1
ok "Base packages installed"

# Now try to install Python2 packages
log "Installing Python2 and pip..."

# Check if python2 is available
if command -v python2 &> /dev/null; then
    ok "Python2 already installed"
    PYTHON_CMD="python2"
elif command -v python2.7 &> /dev/null; then
    ok "Python2.7 already installed"
    PYTHON_CMD="python2.7"
else
    # Try to install python2
    if sudo apt install -y python2 >>$LOG_FILE 2>&1; then
        ok "Python2 installed"
        PYTHON_CMD="python2"
    elif sudo apt install -y python2.7 >>$LOG_FILE 2>&1; then
        ok "Python2.7 installed"
        PYTHON_CMD="python2.7"
    else
        err "Could not install Python2. Trying to download pip manually..."
        PYTHON_CMD="python2"
    fi
fi

# Display Python version
PYTHON_VERSION=$($PYTHON_CMD --version 2>&1)
ok "Found: $PYTHON_VERSION"

# Install pip for Python2 manually using get-pip.py
log "Installing pip for Python2..."
if ! $PYTHON_CMD -m pip --version &> /dev/null; then
    cd /tmp
    wget -q https://bootstrap.pypa.io/pip/2.7/get-pip.py -O get-pip.py >>$LOG_FILE 2>&1 || \
    curl -s https://bootstrap.pypa.io/pip/2.7/get-pip.py -o get-pip.py >>$LOG_FILE 2>&1
    
    if [ -f get-pip.py ]; then
        sudo $PYTHON_CMD get-pip.py >>$LOG_FILE 2>&1
        ok "pip installed for Python2"
    else
        err "Failed to download get-pip.py"
        exit 1
    fi
else
    ok "pip already installed for Python2"
fi

# Verify pip is working
if ! $PYTHON_CMD -m pip --version &> /dev/null; then
    err "pip installation failed for Python2"
    exit 1
fi

# Upgrade pip, setuptools, and wheel
log "Upgrading pip and setuptools for Python2..."
sudo $PYTHON_CMD -m pip install --upgrade pip setuptools wheel >>$LOG_FILE 2>&1

# Install Python2 dependencies for FastAPI
# Note: FastAPI doesn't officially support Python 2, so we'll install compatible alternatives
log "Installing Python2 dependencies..."

# These are Python2-compatible packages
sudo $PYTHON_CMD -m pip install \
    tornado==5.1.1 \
    pymysql \
    futures \
    enum34 \
    typing >>$LOG_FILE 2>&1

ok "Python2 dependencies installed"

# Important note about FastAPI
warn "NOTE: FastAPI requires Python 3.6+. Creating Python2-compatible WebSocket server instead..."

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

log "Creating Python2-compatible WebSocket server..."

# Create a Python2-compatible server using Tornado
SERVER_PY_PATH="${APP_DIR}/pynq/server.py"

if [ -d "${APP_DIR}/pynq" ]; then
    ok "Found pynq directory"
else
    log "Creating pynq directory..."
    sudo mkdir -p "${APP_DIR}/pynq"
fi

# Backup original if exists
if [ -f "$SERVER_PY_PATH" ]; then
    sudo cp "$SERVER_PY_PATH" "${SERVER_PY_PATH}.backup"
fi

# Create Python2-compatible Tornado WebSocket server
sudo tee "$SERVER_PY_PATH" > /dev/null << 'EOFSERVER'
#!/usr/bin/env python2
# -*- coding: utf-8 -*-
from __future__ import print_function
import tornado.ioloop
import tornado.web
import tornado.websocket
import json
import time
import random
import struct
import sys
import os
from threading import Thread

print("=== Starting Spicer DAQ Server (Python2) ===", file=sys.stderr)
print("Python: %s" % sys.executable, file=sys.stderr)

# Try to import and initialize DAQ with error handling
daq = None
daq_initialized = False

try:
    # First, try to import pynq
    import pynq
    print("✓ PYNQ module imported", file=sys.stderr)
    
    try:
        # Try to import daq module
        sys.path.insert(0, os.path.dirname(__file__))
        from daq import Daq
        print("✓ DAQ module imported", file=sys.stderr)
        
        # Try to initialize DAQ
        try:
            print("Initializing FPGA DAQ...", file=sys.stderr)
            daq = Daq()
            daq.start_background()
            daq_initialized = True
            print("✓ FPGA DAQ initialized successfully", file=sys.stderr)
        except Exception as e:
            print("⚠ FPGA DAQ initialization failed: %s" % str(e), file=sys.stderr)
            print("Will run in simulation mode", file=sys.stderr)
            daq = None
            
    except ImportError as e:
        print("✗ DAQ module import failed: %s" % str(e), file=sys.stderr)
        print("Will run in simulation mode", file=sys.stderr)
        daq = None
        
except ImportError as e:
    print("✗ PYNQ module import failed: %s" % str(e), file=sys.stderr)
    print("Will run in simulation mode", file=sys.stderr)
    daq = None

# If DAQ initialization failed, create a simulated one
if not daq_initialized:
    print("Creating simulated DAQ...", file=sys.stderr)
    
    class SimulatedDAQ(object):
        def __init__(self):
            self.running = False
            self.thread = None
            self.data_buffer = bytearray()
            
        def start_background(self):
            self.running = True
            self.thread = Thread(target=self._background_task)
            self.thread.daemon = True
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
                                   0.7 * (sample % 100) / 100.0))
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

class MainHandler(tornado.web.RequestHandler):
    def get(self):
        self.set_header("Content-Type", "application/json")
        self.write(json.dumps({
            "message": "Spicer DAQ Tornado Server (Python2)",
            "status": "running",
            "python_version": sys.version
        }))

class WebSocketHandler(tornado.websocket.WebSocketHandler):
    def check_origin(self, origin):
        return True
    
    def open(self):
        print("WebSocket connection established", file=sys.stderr)
        self.callback = tornado.ioloop.PeriodicCallback(
            self.send_data, 10)  # 10ms
        self.callback.start()
    
    def send_data(self):
        try:
            if daq:
                data = daq.read_streaming()
            else:
                data = None
            
            if data:
                self.write_message(data, binary=True)
            else:
                self.write_message(json.dumps({
                    "type": "heartbeat",
                    "time": time.time()
                }))
        except Exception as e:
            print("WebSocket send error: %s" % str(e), file=sys.stderr)
    
    def on_message(self, message):
        pass
    
    def on_close(self):
        print("WebSocket connection closed", file=sys.stderr)
        self.callback.stop()

def make_app():
    return tornado.web.Application([
        (r"/", MainHandler),
        (r"/ws", WebSocketHandler),
    ])

if __name__ == "__main__":
    app = make_app()
    app.listen(8000, address="0.0.0.0")
    print("Starting Tornado server on 0.0.0.0:8000", file=sys.stderr)
    tornado.ioloop.IOLoop.current().start()
EOFSERVER

sudo chmod +x "$SERVER_PY_PATH"
ok "Python2 Tornado server created"

# Create __init__.py
sudo tee "${APP_DIR}/pynq/__init__.py" > /dev/null << 'EOF'
# This file makes the pynq directory a Python package
EOF

# Create test script
log "Creating test script..."
sudo tee /tmp/test_server.py > /dev/null << EOFTEST
#!/usr/bin/env $PYTHON_CMD
# -*- coding: utf-8 -*-
from __future__ import print_function
import sys
import os

sys.path.insert(0, '/var/www/html/pynq')

print("Testing server.py import...")
try:
    import tornado
    import tornado.websocket
    print("✓ Tornado imported")
    
    from server import make_app
    print("✓ server.py imports successfully")
    
    app = make_app()
    print("✓ Tornado app created: %s" % app)
    
    print("✓ All imports successful!")
    
except ImportError as e:
    print("✗ Import error: %s" % str(e))
    print("Python path: %s" % sys.path)
    sys.exit(1)
EOFTEST

sudo chmod +x /tmp/test_server.py

# Test the import
log "Testing server.py import..."
if $PYTHON_CMD /tmp/test_server.py; then
    ok "server.py imports successfully"
else
    err "server.py import test failed"
    exit 1
fi

# Create rc.local startup
log "Setting up /etc/rc.local for auto-start..."
sudo tee /etc/rc.local > /dev/null << EOFRC
#!/bin/bash
# rc.local - executed at the end of each multiuser runlevel

# Start the Tornado server
cd /var/www/html/pynq
$PYTHON_CMD server.py > /tmp/daq_server.log 2>&1 &

# Make sure we return 0
exit 0
EOFRC

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
sudo pkill -f "tornado" 2>/dev/null || true
sleep 2

cd ${APP_DIR}/pynq
if sudo $PYTHON_CMD server.py > /tmp/server_runtime.log 2>&1 & then
    SERVER_PID=$!
    ok "server.py started with PID: $SERVER_PID"
    
    # Wait for server to start
    sleep 5
    
    # Check if server is running
    if ps -p $SERVER_PID > /dev/null 2>&1; then
        ok "server.py is running (PID: $SERVER_PID)"
        
        # Check if it's listening on port 8000
        if sudo netstat -tlnp | grep -q ":8000.*$PYTHON_CMD"; then
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

    # Proxy WebSocket requests to Tornado server
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

# Test connectivity
log "Testing connectivity..."
sleep 3

# Test Tornado directly
if curl -s -f "http://localhost:8000/" >/dev/null 2>&1; then
    ok "Tornado server responding on port 8000"
else
    warn "Tornado not responding. Checking logs..."
    tail -10 /tmp/server_runtime.log
fi

ok "Installation complete!"
echo ""
echo "=========================================="
echo "🎯 Access Points:"
echo "  Static Files:     http://${SERVER_IP}/"
echo "  Tornado Server:   http://${SERVER_IP}:8000/"
echo "  WebSocket (WS):   ws://${SERVER_IP}:8000/ws"
echo "  PYNQ Jupyter:     http://${SERVER_IP}:9090/tree"
echo ""
echo "📊 Database:"
echo "  Name: ${DB_NAME}"
echo "  User: root"
echo "  Pass: ${DB_PASS}"
echo ""
echo "⚙️ Server Management:"
echo "  Process: ${PROCESS_NAME}"
echo "  Python: $PYTHON_CMD ($PYTHON_VERSION)"
echo "  Auto-start: Configured via /etc/rc.local"
echo "  Manual start: cd ${APP_DIR}/pynq && sudo $PYTHON_CMD server.py"
echo "  Check status: ps aux | grep server.py"
echo "  Stop server: sudo pkill -f server.py"
echo "  Logs: tail -f /tmp/daq_server.log"
echo ""
echo "🔧 Python2 Verification:"
echo "  Test imports: $PYTHON_CMD /tmp/test_server.py"
echo "  Test server: curl http://localhost:8000/"
echo ""
echo "⚠️  IMPORTANT NOTE:"
echo "  Using Python2 with Tornado instead of FastAPI"
echo "  FastAPI requires Python 3.6+, not compatible with Python2"
echo ""
echo "✅ Tornado server will auto-start on boot via /etc/rc.local!"
echo "✅ Apache serves static files on port 80"
echo "✅ WebSocket available on port 8000"
echo "=========================================="

# Create a simple test HTML file
sudo tee ${APP_DIR}/test.html > /dev/null <<EOFHTML
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
    <h1>Spicer DAQ Installation Test (Python2)</h1>
    
    <div id="tornado-status" class="status">
        Testing Tornado server connection...
    </div>
    
    <div id="websocket-status" class="status">
        Testing WebSocket connection...
    </div>
    
    <script>
        // Test Tornado REST endpoint
        fetch('http://' + window.location.hostname + ':8000/')
            .then(response => response.json())
            .then(data => {
                document.getElementById('tornado-status').innerHTML = 
                    '<strong>✓ Tornado Server:</strong> ' + data.message + '<br><small>Python: ' + data.python_version + '</small>';
                document.getElementById('tornado-status').className = 'status success';
            })
            .catch(error => {
                document.getElementById('tornado-status').innerHTML = 
                    '<strong>✗ Tornado Server:</strong> Connection failed';
                document.getElementById('tornado-status').className = 'status error';
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
EOFHTML

echo ""
echo "Test page created: http://${SERVER_IP}/test.html"
echo "This page will test both Tornado and WebSocket connections."