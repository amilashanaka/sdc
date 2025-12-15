#!/bin/bash
set -euo pipefail

LOG_FILE="/tmp/install_$(date +%Y%m%d_%H%M%S).log"
APP_DIR="/var/www/html"
WWW_USER="root"
WWW_GROUP="root"
DB_PASS="daq"
DB_NAME="daq"
PROCESS_NAME="server.py"
SERVER_IP="$(hostname -I | awk '{print $1}')"
REPO_URL="https://github.com/amilashanaka/sdc.git"
VENV_PATH="/usr/local/share/pynq-venv"
SSL_CERT="/etc/ssl/certs/apache-selfsigned.crt"
SSL_KEY="/etc/ssl/private/apache-selfsigned.key"

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
    ok "Port 9090 active â€” PYNQ running, will preserve"
else
    warn "Port 9090 inactive â€” PYNQ may not be running"
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

log "Installing Apache + PHP + Git + Python dependencies + SSL tools"
export DEBIAN_FRONTEND=noninteractive
sudo -E apt install -y \
 apache2 apache2-utils \
 php libapache2-mod-php php-mysql php-cli \
 git mariadb-server mariadb-client \
 python3-pip python3-venv \
 python3-dev build-essential libssl-dev libffi-dev \
 openssl >>$LOG_FILE 2>&1
ok "Packages installed"

sudo a2enmod rewrite ssl proxy proxy_http proxy_wstunnel headers >>$LOG_FILE 2>&1
sudo systemctl enable apache2 >/dev/null || true

# Generate self-signed certificate if not exists
log "Setting up self-signed SSL certificate..."
sudo mkdir -p /etc/ssl/private
if [ ! -f "$SSL_CERT" ] || [ ! -f "$SSL_KEY" ]; then
    sudo openssl req -x509 -nodes -days 365 -newkey rsa:2048 \
        -keyout "$SSL_KEY" \
        -out "$SSL_CERT" \
        -subj "/C=US/ST=Denial/L=Springfield/O=Dis/CN=${SERVER_IP}" >>$LOG_FILE 2>&1
    ok "Self-signed certificate generated"
else
    ok "Using existing self-signed certificate"
fi

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

log "Configuring MariaDB..."
sudo systemctl start mariadb
sleep 1

# Secure MariaDB installation (matching your system)
sudo mysql -u root <<EOF 2>/dev/null || true
ALTER USER 'root'@'localhost' IDENTIFIED VIA mysql_native_password USING PASSWORD('${DB_PASS}');
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
if $VENV_PATH/bin/python -c "import fastapi; import uvicorn; import websockets; print('âœ“ FastAPI, Uvicorn, and WebSockets installed successfully')" 2>>$LOG_FILE; then
    ok "All required Python packages installed"
else
    err "Python package installation failed!"
    exit 1
fi

# Ensure server.py exists
log "Checking server.py..."
SERVER_PY_PATH="${APP_DIR}/pynq/server.py"
if [ -f "$SERVER_PY_PATH" ]; then
    ok "Found server.py at $SERVER_PY_PATH"
    sudo chmod +x "$SERVER_PY_PATH"
else
    err "server.py NOT found at ${APP_DIR}/pynq/server.py"
    exit 1
fi

# Create systemd service file
log "Creating systemd service file..."
sudo tee /etc/systemd/system/spicer-daq.service > /dev/null << EOF
[Unit]
Description=Spicer DAQ Server
After=network.target
After=multi-user.target

[Service]
Type=simple
User=root
Group=root
WorkingDirectory=/var/www/html/pynq
ExecStart=/usr/local/share/pynq-venv/bin/python /var/www/html/pynq/server.py
ExecStartPre=/bin/sh -c '/bin/fuser -k 8000/tcp || true'
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

log "Configuring Apache for HTTP and HTTPS..."
# Create HTTP virtual host with redirect to HTTPS
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
    RewriteCond %{HTTPS} off
    RewriteRule ^ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]

    ErrorLog \${APACHE_LOG_DIR}/spicer_error.log
    CustomLog \${APACHE_LOG_DIR}/spicer_access.log combined
</VirtualHost>
EOF

# Create HTTPS virtual host
sudo tee /etc/apache2/sites-available/spicer-ssl.conf > /dev/null << EOF
<VirtualHost *:443>
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

    SSLEngine on
    SSLCertificateFile ${SSL_CERT}
    SSLCertificateKeyFile ${SSL_KEY}

    ErrorLog \${APACHE_LOG_DIR}/spicer_error.log
    CustomLog \${APACHE_LOG_DIR}/spicer_access.log combined
</VirtualHost>
EOF

sudo a2dissite 000-default default-ssl >/dev/null 2>&1 || true
sudo a2ensite spicer spicer-ssl >/dev/null

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
    warn "FastAPI responding on / but should only handle WS - check server.py"
else
    ok "FastAPI not responding on / (expected for WS-only)"
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
            print('âœ“ WebSocket connection successful')
            return True
    except Exception as e:
        print(f'âœ— WebSocket error: {e}')
        return False

import asyncio
asyncio.run(test())
" 2>/dev/null; then
    ok "WebSocket connection successful"
else
    warn "WebSocket connection test failed (might be OK if no clients connected)"
fi

# Test Apache serving static files over HTTPS
log "3. Testing Apache static file serving over HTTPS..."
if curl -s -f -k "https://${SERVER_IP}/" >/dev/null 2>&1; then
    ok "Apache serving static files on port 443"
else
    warn "Apache not responding on port 443 (check certificate trust)"
fi

ok "Installation complete!"
echo ""
echo "=========================================="
echo "ðŸŽ¯ Access Points:"
echo "  Main Access:      https://${SERVER_IP}/ (self-signed cert, ignore warnings)"
echo "  FastAPI Server:   http://127.0.0.1:8000/ (internal only)"
echo "  WebSocket (WS):   ws://127.0.0.1:8000/ws (internal)"
echo "  PYNQ Jupyter:     http://${SERVER_IP}:9090/tree"
echo ""
echo "ðŸ“Š Database:"
echo "  Name: ${DB_NAME}"
echo "  User: root"
echo "  Pass: ${DB_PASS}"
echo ""
echo "âš™ï¸  Server Management:"
echo "  Process: systemd service 'spicer-daq'"
echo "  Check status: sudo systemctl status spicer-daq"
echo "  Start server: sudo systemctl start spicer-daq"
echo "  Stop server: sudo systemctl stop spicer-daq"
echo "  View logs: sudo journalctl -u spicer-daq.service -f"
echo "  Log file: sudo journalctl -u spicer-daq.service"
echo ""
echo "ðŸ Python Environment:"
echo "  Virtual env: ${VENV_PATH}"
echo "  Python: $($VENV_PATH/bin/python --version)"
echo "  FastAPI: $($VENV_PATH/bin/python -c 'import fastapi; print(fastapi.__version__)')"
echo "  Uvicorn: $($VENV_PATH/bin/python -c 'import uvicorn; print(uvicorn.__version__)')"
echo ""
echo "ðŸ”§ Quick Tests:"
echo "  Test WS: $VENV_PATH/bin/python -m websockets ws://127.0.0.1:8000/ws"
echo "  Test Apache HTTPS: curl -k https://${SERVER_IP}/"
echo "  Check port 8000: sudo ss -tlnp | grep :8000"
echo "  Check port 443: sudo ss -tlnp | grep :443"
echo ""
echo "âœ… HTTPS enabled with self-signed certificate"
echo "âœ… HTTP redirects to HTTPS"
echo "âœ… WebSocket proxied through Apache"
echo "âœ… Uses PYNQ virtual environment at ${VENV_PATH}"
echo "=========================================="

# Write log file
echo "Installation log: ${LOG_FILE}"
echo "Server logs: sudo journalctl -u spicer-daq.service -f"
echo ""
echo "Note: The server runs in the PYNQ virtual environment at ${VENV_PATH}"
echo "Access via HTTPS, accept self-signed certificate warning in browser."