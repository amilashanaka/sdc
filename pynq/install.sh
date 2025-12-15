#!/bin/bash
set -euo pipefail

# ============================================================================
# Spicer DAQ Installation Script for PYNQ Z1
# ============================================================================
# This script installs:
#   - Apache2 + PHP web server (HTTPS on port 443)
#   - Python FastAPI WebSocket server (internal port 8000)
#   - MariaDB database
#   - Self-signed SSL certificate
# 
# Architecture:
#   - Apache serves PHP web interface on port 443 (HTTPS)
#   - Python WebSocket server provides real-time DAQ data
#   - Uses SYSTEM Python3 with existing PYNQ libraries (no venv conflicts)
# ============================================================================

LOG_FILE="/tmp/install_$(date +%Y%m%d_%H%M%S).log"
APP_DIR="/var/www/html"
WWW_USER="www-data"
WWW_GROUP="www-data"
DB_PASS="daq"
DB_NAME="daq"
SERVER_IP="$(hostname -I | awk '{print $1}')"
REPO_URL="https://github.com/amilashanaka/sdc.git"
SSL_CERT="/etc/ssl/certs/apache-selfsigned.crt"
SSL_KEY="/etc/ssl/private/apache-selfsigned.key"

# Colors for output
BLUE='\033[0;34m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m'

log()  { echo -e "${BLUE}[*] $*${NC}"; }
ok()   { echo -e "${GREEN}[OK] $*${NC}"; }
warn() { echo -e "${YELLOW}[WARN] $*${NC}"; }
err()  { echo -e "${RED}[ERROR] $*${NC}"; }

echo "============================================================================"
echo "  Spicer DAQ Installation for PYNQ Z1"
echo "============================================================================"
echo ""

log "Starting installation (preserving PYNQ on port 9090)..."
sudo apt update -y >>$LOG_FILE 2>&1

# ============================================================================
# Check PYNQ Status
# ============================================================================
log "Checking PYNQ Jupyter on port 9090..."
if ss -ltn | grep -q ':9090'; then
    ok "Port 9090 active - PYNQ Jupyter is running"
else
    warn "Port 9090 inactive - PYNQ Jupyter may not be running"
fi

# ============================================================================
# Check and Free Port 80
# ============================================================================
log "Checking port 80..."
PORT_80_PID=$(sudo ss -tulpn | grep ':80 ' | grep -v apache2 | awk '{print $7}' | grep -oP 'pid=\K[0-9]+' | head -1)
if [ -n "$PORT_80_PID" ]; then
    PORT_80_CMD=$(ps -p $PORT_80_PID -o comm=)
    warn "Port 80 occupied by PID $PORT_80_PID ($PORT_80_CMD)"
    
    # Safety check - don't kill PYNQ services
    if [[ "$PORT_80_CMD" == *"jupyter"* ]] || [[ "$PORT_80_CMD" == *"pynq"* ]]; then
        err "Port 80 is used by PYNQ system service - ABORTING"
        exit 1
    fi
    
    log "Stopping process on port 80..."
    sudo kill $PORT_80_PID 2>/dev/null || true
    sleep 2
    ok "Port 80 freed"
fi

# ============================================================================
# Install System Packages
# ============================================================================
log "Installing Apache, PHP, MariaDB, Git, and SSL tools..."
export DEBIAN_FRONTEND=noninteractive
sudo -E apt install -y \
    apache2 apache2-utils \
    php libapache2-mod-php php-mysql php-cli \
    git mariadb-server mariadb-client \
    python3-pip \
    openssl >>$LOG_FILE 2>&1
ok "System packages installed"

# ============================================================================
# Install Python Packages to SYSTEM Python (not venv)
# ============================================================================
log "Installing Python packages to system Python3..."
# Use system Python3 to avoid breaking PYNQ environment
sudo python3 -m pip install --upgrade pip >>$LOG_FILE 2>&1

# Install only FastAPI/WebSocket dependencies (PYNQ already installed)
sudo python3 -m pip install fastapi==0.124.0 >>$LOG_FILE 2>&1
sudo python3 -m pip install 'uvicorn[standard]==0.38.0' >>$LOG_FILE 2>&1
sudo python3 -m pip install websockets==10.3 >>$LOG_FILE 2>&1
sudo python3 -m pip install pymysql python-multipart >>$LOG_FILE 2>&1

# Verify installation
log "Verifying Python packages..."
if python3 -c "import fastapi; import uvicorn; import websockets; print('OK')" 2>>$LOG_FILE; then
    ok "Python packages installed successfully"
else
    err "Python package installation failed!"
    exit 1
fi

# ============================================================================
# Enable Apache Modules
# ============================================================================
log "Enabling Apache modules..."
sudo a2enmod rewrite ssl proxy proxy_http proxy_wstunnel headers >>$LOG_FILE 2>&1
sudo systemctl enable apache2 >/dev/null 2>&1 || true
ok "Apache modules enabled"

# ============================================================================
# Generate SSL Certificate
# ============================================================================
log "Setting up self-signed SSL certificate..."
sudo mkdir -p /etc/ssl/private
if [ ! -f "$SSL_CERT" ] || [ ! -f "$SSL_KEY" ]; then
    sudo openssl req -x509 -nodes -days 365 -newkey rsa:2048 \
        -keyout "$SSL_KEY" \
        -out "$SSL_CERT" \
        -subj "/C=US/ST=State/L=City/O=Organization/CN=${SERVER_IP}" >>$LOG_FILE 2>&1
    ok "Self-signed SSL certificate generated"
else
    ok "Using existing SSL certificate"
fi

# ============================================================================
# Backup and Clean Web Directory
# ============================================================================
if [ "$(ls -A ${APP_DIR} 2>/dev/null)" ]; then
    BACKUP_DIR="/tmp/html_backup_$(date +%Y%m%d_%H%M%S)"
    log "Backing up ${APP_DIR} to ${BACKUP_DIR}..."
    sudo mkdir -p "${BACKUP_DIR}"
    sudo cp -r ${APP_DIR}/* "${BACKUP_DIR}/" 2>/dev/null || true
    ok "Backup created"
fi

log "Cleaning ${APP_DIR}..."
sudo rm -rf ${APP_DIR}/*
sudo rm -rf ${APP_DIR}/.[!.]* 2>/dev/null || true
ok "Directory cleaned"

# ============================================================================
# Clone Repository
# ============================================================================
log "Cloning repository from ${REPO_URL}..."
if sudo git clone "${REPO_URL}" "${APP_DIR}" >>$LOG_FILE 2>&1; then
    ok "Repository cloned successfully"
else
    err "Failed to clone repository: ${REPO_URL}"
    exit 1
fi

# ============================================================================
# Set Permissions
# ============================================================================
log "Setting permissions for ${APP_DIR}..."
sudo chown -R ${WWW_USER}:${WWW_GROUP} ${APP_DIR}
sudo chmod -R 755 ${APP_DIR}
ok "Permissions set"

# ============================================================================
# Configure MariaDB
# ============================================================================
log "Configuring MariaDB..."
sudo systemctl start mariadb
sleep 2

# Secure MariaDB
sudo mysql -u root <<EOF 2>/dev/null || true
ALTER USER 'root'@'localhost' IDENTIFIED VIA mysql_native_password USING PASSWORD('${DB_PASS}');
DELETE FROM mysql.user WHERE User='';
DELETE FROM mysql.user WHERE User='root' AND Host NOT IN ('localhost', '127.0.0.1', '::1');
DROP DATABASE IF EXISTS test;
DELETE FROM mysql.db WHERE Db='test' OR Db='test\\_%';
FLUSH PRIVILEGES;
EOF

# Create database
sudo mysql -u root -p${DB_PASS} -e \
    "CREATE DATABASE IF NOT EXISTS ${DB_NAME};" 2>/dev/null || true

# Import schema if exists
SQL_FILE="${APP_DIR}/db/table.sql"
if [ -f "$SQL_FILE" ]; then
    log "Importing database schema..."
    sudo mysql -u root -p${DB_PASS} ${DB_NAME} < "$SQL_FILE"
    ok "Database schema imported"
else
    warn "No SQL file found at ${SQL_FILE}"
fi

ok "MariaDB configured"

# ============================================================================
# Verify server.py
# ============================================================================
log "Verifying server.py..."
SERVER_PY_PATH="${APP_DIR}/pynq/server.py"
if [ -f "$SERVER_PY_PATH" ]; then
    ok "Found server.py at $SERVER_PY_PATH"
    sudo chmod +x "$SERVER_PY_PATH"
else
    err "server.py NOT found at ${SERVER_PY_PATH}"
    exit 1
fi

# ============================================================================
# Create systemd Service (Using System Python)
# ============================================================================
log "Creating systemd service for WebSocket server..."
sudo tee /etc/systemd/system/spicer-daq.service > /dev/null <<EOF
[Unit]
Description=Spicer DAQ WebSocket Server
After=network.target multi-user.target
StartLimitIntervalSec=60
StartLimitBurst=3

[Service]
Type=simple
User=root
Group=root
WorkingDirectory=${APP_DIR}/pynq

# Use SYSTEM Python3 (has PYNQ libraries)
ExecStart=/usr/bin/python3 ${APP_DIR}/pynq/server.py

# Kill any process on port 8000 before starting
ExecStartPre=/bin/sh -c '/bin/fuser -k 8000/tcp || true'
ExecStartPre=/bin/sleep 2

# Auto-restart on failure
Restart=always
RestartSec=10

# Environment variables for PYNQ
Environment="PYTHONUNBUFFERED=1"
Environment="XILINX_XRT=/usr"

# Logging
StandardOutput=journal
StandardError=journal
SyslogIdentifier=spicer-daq

[Install]
WantedBy=multi-user.target
EOF

# ============================================================================
# Stop Existing Processes
# ============================================================================
log "Stopping any existing server processes..."
sudo pkill -f "server.py" 2>/dev/null || true
sudo pkill -f "uvicorn" 2>/dev/null || true
sudo fuser -k 8000/tcp 2>/dev/null || true
sleep 3

# ============================================================================
# Start WebSocket Service
# ============================================================================
sudo systemctl daemon-reload
sudo systemctl enable spicer-daq.service >>$LOG_FILE 2>&1

log "Starting WebSocket server..."
if sudo systemctl start spicer-daq.service; then
    ok "Service started"
    sleep 5
    
    if sudo systemctl is-active --quiet spicer-daq.service; then
        ok "Service is active"
        
        if sudo ss -tlnp | grep -q ':8000'; then
            ok "WebSocket server listening on port 8000"
        else
            warn "Port 8000 not listening yet. Check logs: sudo journalctl -u spicer-daq -f"
        fi
    else
        err "Service failed to start"
        sudo journalctl -u spicer-daq.service --no-pager -n 30
        exit 1
    fi
else
    err "Failed to start service"
    exit 1
fi

# ============================================================================
# Configure Apache Virtual Hosts
# ============================================================================
log "Configuring Apache virtual hosts..."

# HTTP (port 80) - redirect to HTTPS
sudo tee /etc/apache2/sites-available/spicer.conf > /dev/null <<EOF
<VirtualHost *:80>
    ServerName ${SERVER_IP}
    DocumentRoot ${APP_DIR}

    # Redirect all HTTP to HTTPS
    RewriteEngine On
    RewriteCond %{HTTPS} off
    RewriteRule ^ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]

    ErrorLog \${APACHE_LOG_DIR}/spicer_error.log
    CustomLog \${APACHE_LOG_DIR}/spicer_access.log combined
</VirtualHost>
EOF

# HTTPS (port 443) - main site + WebSocket proxy
sudo tee /etc/apache2/sites-available/spicer-ssl.conf > /dev/null <<EOF
<VirtualHost *:443>
    ServerName ${SERVER_IP}
    DocumentRoot ${APP_DIR}

    # PHP web interface
    <Directory ${APP_DIR}>
        AllowOverride All
        Require all granted
        Options Indexes FollowSymLinks
        DirectoryIndex index.php index.html
    </Directory>

    # WebSocket proxy to Python backend
    RewriteEngine On
    RewriteCond %{HTTP:Upgrade} websocket [NC]
    RewriteCond %{HTTP:Connection} upgrade [NC]
    RewriteRule ^/ws$ ws://127.0.0.1:8000/ws [P,L]

    # Proxy for health check
    ProxyPass /health http://127.0.0.1:8000/health
    ProxyPassReverse /health http://127.0.0.1:8000/health

    # SSL Configuration
    SSLEngine on
    SSLCertificateFile ${SSL_CERT}
    SSLCertificateKeyFile ${SSL_KEY}

    # Security headers
    Header always set Strict-Transport-Security "max-age=31536000"
    Header always set X-Content-Type-Options "nosniff"
    Header always set X-Frame-Options "SAMEORIGIN"

    ErrorLog \${APACHE_LOG_DIR}/spicer_error.log
    CustomLog \${APACHE_LOG_DIR}/spicer_access.log combined
</VirtualHost>
EOF

# Disable default sites
sudo a2dissite 000-default default-ssl 2>/dev/null || true

# Enable Spicer sites
sudo a2ensite spicer spicer-ssl >>$LOG_FILE 2>&1

# ============================================================================
# Test and Start Apache
# ============================================================================
log "Testing Apache configuration..."
if sudo apache2ctl configtest 2>&1 | grep -q "Syntax OK"; then
    ok "Apache configuration valid"
else
    err "Apache configuration has errors:"
    sudo apache2ctl configtest
    exit 1
fi

log "Restarting Apache..."
if sudo systemctl restart apache2; then
    ok "Apache started successfully"
else
    err "Apache failed to start"
    sudo journalctl -xeu apache2.service --no-pager -n 20
    exit 1
fi

# ============================================================================
# Final Permissions Check
# ============================================================================
log "Final permissions check..."
sudo chown -R ${WWW_USER}:${WWW_GROUP} ${APP_DIR}
sudo chmod -R 755 ${APP_DIR}
ok "Permissions verified"

# ============================================================================
# System Tests
# ============================================================================
log "Running system tests..."
sleep 3

# Test 1: WebSocket health check
if curl -s -f "http://127.0.0.1:8000/health" >/dev/null 2>&1; then
    ok "WebSocket server health check passed"
else
    warn "WebSocket health check failed (may need more time)"
fi

# Test 2: Apache HTTPS
if curl -s -f -k "https://${SERVER_IP}/" >/dev/null 2>&1; then
    ok "Apache HTTPS responding"
else
    warn "Apache HTTPS not responding (certificate trust issue?)"
fi

# Test 3: Check ports
if sudo ss -tlnp | grep -q ':8000'; then
    ok "Port 8000 (WebSocket) is listening"
else
    warn "Port 8000 not listening"
fi

if sudo ss -tlnp | grep -q ':443'; then
    ok "Port 443 (HTTPS) is listening"
else
    warn "Port 443 not listening"
fi

# ============================================================================
# Installation Complete
# ============================================================================
echo ""
echo "============================================================================"
ok "Installation Complete!"
echo "============================================================================"
echo ""
echo "🌐 Access Points:"
echo "   Main Web Interface:  https://${SERVER_IP}/"
echo "   WebSocket Server:    ws://127.0.0.1:8000/ws (internal)"
echo "   Health Check:        https://${SERVER_IP}/health"
echo "   PYNQ Jupyter:        http://${SERVER_IP}:9090/"
echo ""
echo "📊 Database:"
echo "   Database: ${DB_NAME}"
echo "   User:     root"
echo "   Password: ${DB_PASS}"
echo ""
echo "⚙️  Service Management:"
echo "   Status:   sudo systemctl status spicer-daq"
echo "   Start:    sudo systemctl start spicer-daq"
echo "   Stop:     sudo systemctl stop spicer-daq"
echo "   Restart:  sudo systemctl restart spicer-daq"
echo "   Logs:     sudo journalctl -u spicer-daq -f"
echo ""
echo "🐍 Python Environment:"
echo "   Python:   /usr/bin/python3 (system Python with PYNQ)"
echo "   Version:  $(python3 --version)"
echo "   FastAPI:  $(python3 -c 'import fastapi; print(fastapi.__version__)')"
echo "   Uvicorn:  $(python3 -c 'import uvicorn; print(uvicorn.__version__)')"
echo ""
echo "🔒 Security:"
echo "   ✓ HTTPS enabled (self-signed certificate)"
echo "   ✓ HTTP redirects to HTTPS"
echo "   ✓ WebSocket proxied through Apache"
echo ""
echo "📝 Architecture:"
echo "   Apache/PHP → Web Interface (port 443)"
echo "   Python     → WebSocket Server (port 8000, internal only)"
echo "   MariaDB    → Database (port 3306)"
echo "   PYNQ       → FPGA/Hardware Access (system libraries)"
echo ""
echo "🧪 Quick Tests:"
echo "   curl -k https://${SERVER_IP}/"
echo "   curl http://127.0.0.1:8000/health"
echo "   sudo ss -tlnp | grep ':8000\\|:443'"
echo ""
echo "⚠️  Note: Accept self-signed certificate warning in browser"
echo "============================================================================"
echo ""
echo "Installation log: ${LOG_FILE}"
echo ""