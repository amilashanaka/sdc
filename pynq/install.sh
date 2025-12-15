#!/bin/bash

# ============================================================================
# Spicer DAQ Installation Script for PYNQ Z1
# ============================================================================
# Run as: sudo bash install.sh
# Or: echo "xilinx" | sudo -S bash install.sh
# ============================================================================

set -euo pipefail

# PYNQ default credentials
PYNQ_USER="xilinx"
PYNQ_PASS="xilinx"

# Check if running as root
if [ "$EUID" -ne 0 ]; then 
    echo "This script must be run as root. Attempting sudo..."
    echo "${PYNQ_PASS}" | sudo -S bash "$0" "$@"
    exit $?
fi

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
echo "  User: ${PYNQ_USER}"
echo "============================================================================"
echo ""

# Verify xilinx user exists
if ! id "${PYNQ_USER}" &>/dev/null; then
    err "User '${PYNQ_USER}' does not exist!"
    exit 1
fi

ok "Running as root, service will run as user: ${PYNQ_USER}"

log "Starting installation (preserving PYNQ on port 9090)..."
apt update -y >>$LOG_FILE 2>&1

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
PORT_80_PID=$(ss -tulpn | grep ':80 ' | grep -v apache2 | awk '{print $7}' | grep -oP 'pid=\K[0-9]+' | head -1)
if [ -n "$PORT_80_PID" ]; then
    PORT_80_CMD=$(ps -p $PORT_80_PID -o comm=)
    warn "Port 80 occupied by PID $PORT_80_PID ($PORT_80_CMD)"
    
    # Safety check - don't kill PYNQ services
    if [[ "$PORT_80_CMD" == *"jupyter"* ]] || [[ "$PORT_80_CMD" == *"pynq"* ]]; then
        err "Port 80 is used by PYNQ system service - ABORTING"
        exit 1
    fi
    
    log "Stopping process on port 80..."
    kill $PORT_80_PID 2>/dev/null || true
    sleep 2
    ok "Port 80 freed"
fi

# ============================================================================
# Install System Packages
# ============================================================================
log "Installing Apache, PHP, MariaDB, Git, and SSL tools..."
export DEBIAN_FRONTEND=noninteractive
apt install -y \
    apache2 apache2-utils \
    php libapache2-mod-php php-mysql php-cli \
    git mariadb-server mariadb-client \
    python3-pip \
    openssl >>$LOG_FILE 2>&1
ok "System packages installed"

# ============================================================================
# Setup Python Environment - Use PYNQ Virtual Environment
# ============================================================================
VENV_PATH="/usr/local/share/pynq-venv"

log "Checking PYNQ virtual environment..."
if [ -d "$VENV_PATH" ]; then
    ok "Found PYNQ virtual environment at $VENV_PATH"
else
    err "PYNQ virtual environment not found at $VENV_PATH"
    exit 1
fi

log "Installing Python packages to PYNQ virtual environment..."
# Use the PYNQ venv - this is what works when you run manually
sudo $VENV_PATH/bin/pip install --upgrade pip setuptools wheel >>$LOG_FILE 2>&1

# Install exact versions from original working setup
sudo $VENV_PATH/bin/pip install fastapi==0.124.0 >>$LOG_FILE 2>&1
sudo $VENV_PATH/bin/pip install 'uvicorn[standard]==0.38.0' >>$LOG_FILE 2>&1
sudo $VENV_PATH/bin/pip install websockets==10.3 >>$LOG_FILE 2>&1
sudo $VENV_PATH/bin/pip install pymysql python-multipart >>$LOG_FILE 2>&1

# Verify installation
log "Verifying Python packages..."
if $VENV_PATH/bin/python -c "import fastapi; import uvicorn; import websockets; print('OK')" 2>>$LOG_FILE; then
    ok "All required Python packages installed in PYNQ venv"
else
    err "Python package installation failed!"
    exit 1
fi

# ============================================================================
# Enable Apache Modules
# ============================================================================
log "Enabling Apache modules..."
a2enmod rewrite ssl proxy proxy_http proxy_wstunnel headers >>$LOG_FILE 2>&1
systemctl enable apache2 >/dev/null 2>&1 || true
ok "Apache modules enabled"

# ============================================================================
# Generate SSL Certificate
# ============================================================================
log "Setting up self-signed SSL certificate..."
mkdir -p /etc/ssl/private
if [ ! -f "$SSL_CERT" ] || [ ! -f "$SSL_KEY" ]; then
    openssl req -x509 -nodes -days 365 -newkey rsa:2048 \
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
    mkdir -p "${BACKUP_DIR}"
    cp -r ${APP_DIR}/* "${BACKUP_DIR}/" 2>/dev/null || true
    ok "Backup created"
fi

log "Cleaning ${APP_DIR}..."
rm -rf ${APP_DIR}/*
rm -rf ${APP_DIR}/.[!.]* 2>/dev/null || true
ok "Directory cleaned"

# ============================================================================
# Clone Repository
# ============================================================================
log "Cloning repository from ${REPO_URL}..."
if git clone "${REPO_URL}" "${APP_DIR}" >>$LOG_FILE 2>&1; then
    ok "Repository cloned successfully"
else
    err "Failed to clone repository: ${REPO_URL}"
    exit 1
fi

# ============================================================================
# Set Permissions
# ============================================================================
log "Setting permissions for ${APP_DIR}..."
chown -R ${WWW_USER}:${WWW_GROUP} ${APP_DIR}
chmod -R 755 ${APP_DIR}

# Allow xilinx user to access pynq directory
chown -R ${PYNQ_USER}:${PYNQ_USER} ${APP_DIR}/pynq
chmod -R 755 ${APP_DIR}/pynq

ok "Permissions set"

# ============================================================================
# Configure MariaDB
# ============================================================================
log "Configuring MariaDB..."
systemctl start mariadb
sleep 2

# Secure MariaDB
mysql -u root <<EOF 2>/dev/null || true
ALTER USER 'root'@'localhost' IDENTIFIED VIA mysql_native_password USING PASSWORD('${DB_PASS}');
DELETE FROM mysql.user WHERE User='';
DELETE FROM mysql.user WHERE User='root' AND Host NOT IN ('localhost', '127.0.0.1', '::1');
DROP DATABASE IF EXISTS test;
DELETE FROM mysql.db WHERE Db='test' OR Db='test\\_%';
FLUSH PRIVILEGES;
EOF

# Create database
mysql -u root -p${DB_PASS} -e \
    "CREATE DATABASE IF NOT EXISTS ${DB_NAME};" 2>/dev/null || true

# Import schema if exists
SQL_FILE="${APP_DIR}/db/table.sql"
if [ -f "$SQL_FILE" ]; then
    log "Importing database schema..."
    mysql -u root -p${DB_PASS} ${DB_NAME} < "$SQL_FILE"
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
    chmod +x "$SERVER_PY_PATH"
    chown ${PYNQ_USER}:${PYNQ_USER} "$SERVER_PY_PATH"
else
    err "server.py NOT found at ${SERVER_PY_PATH}"
    exit 1
fi

# ============================================================================
# Create systemd Service (Using PYNQ Virtual Environment)
# ============================================================================
log "Creating systemd service for WebSocket server..."
cat > /etc/systemd/system/spicer-daq.service <<EOF
[Unit]
Description=Spicer DAQ WebSocket Server
After=network.target multi-user.target
StartLimitIntervalSec=60
StartLimitBurst=3

[Service]
Type=simple
User=${PYNQ_USER}
Group=${PYNQ_USER}
WorkingDirectory=${APP_DIR}/pynq

# Use PYNQ virtual environment Python (same as when running manually)
ExecStart=${VENV_PATH}/bin/python ${APP_DIR}/pynq/server.py

# Kill any process on port 8000 before starting
ExecStartPre=/bin/sh -c '/bin/fuser -k 8000/tcp || true'
ExecStartPre=/bin/sleep 2

# Auto-restart on failure
Restart=always
RestartSec=10

# Environment variables for PYNQ
Environment="PYTHONUNBUFFERED=1"
Environment="XILINX_XRT=/usr"
Environment="HOME=/home/${PYNQ_USER}"
Environment="PATH=${VENV_PATH}/bin:/usr/local/sbin:/usr/local/bin:/usr/sbin:/usr/bin:/sbin:/bin"

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
pkill -u ${PYNQ_USER} -f "server.py" 2>/dev/null || true
pkill -u ${PYNQ_USER} -f "uvicorn" 2>/dev/null || true
fuser -k 8000/tcp 2>/dev/null || true
sleep 3

# ============================================================================
# Start WebSocket Service
# ============================================================================
systemctl daemon-reload
systemctl enable spicer-daq.service >>$LOG_FILE 2>&1

log "Starting WebSocket server as user '${PYNQ_USER}'..."
if systemctl start spicer-daq.service; then
    ok "Service started"
    sleep 5
    
    if systemctl is-active --quiet spicer-daq.service; then
        ok "Service is active"
        
        if ss -tlnp | grep -q ':8000'; then
            ok "WebSocket server listening on port 8000"
        else
            warn "Port 8000 not listening yet. Check logs: sudo journalctl -u spicer-daq -f"
        fi
    else
        err "Service failed to start"
        journalctl -u spicer-daq.service --no-pager -n 30
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
cat > /etc/apache2/sites-available/spicer.conf <<EOF
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
cat > /etc/apache2/sites-available/spicer-ssl.conf <<EOF
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
a2dissite 000-default default-ssl 2>/dev/null || true

# Enable Spicer sites
a2ensite spicer spicer-ssl >>$LOG_FILE 2>&1

# ============================================================================
# Test and Start Apache
# ============================================================================
log "Testing Apache configuration..."
if apache2ctl configtest 2>&1 | grep -q "Syntax OK"; then
    ok "Apache configuration valid"
else
    err "Apache configuration has errors:"
    apache2ctl configtest
    exit 1
fi

log "Restarting Apache..."
if systemctl restart apache2; then
    ok "Apache started successfully"
else
    err "Apache failed to start"
    journalctl -xeu apache2.service --no-pager -n 20
    exit 1
fi

# ============================================================================
# Final Permissions Check
# ============================================================================
log "Final permissions check..."
chown -R ${WWW_USER}:${WWW_GROUP} ${APP_DIR}
chown -R ${PYNQ_USER}:${PYNQ_USER} ${APP_DIR}/pynq
chmod -R 755 ${APP_DIR}
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
if ss -tlnp | grep -q ':8000'; then
    ok "Port 8000 (WebSocket) is listening"
else
    warn "Port 8000 not listening"
fi

if ss -tlnp | grep -q ':443'; then
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
echo "👤 Service User: ${PYNQ_USER}"
echo "🔑 Default Password: ${PYNQ_PASS}"
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
echo "   User:     ${PYNQ_USER}"
echo "   Python:   ${VENV_PATH}/bin/python (PYNQ virtual environment)"
echo "   Version:  $(${VENV_PATH}/bin/python --version)"
echo "   FastAPI:  $(${VENV_PATH}/bin/python -c 'import fastapi; print(fastapi.__version__)')"
echo "   Uvicorn:  $(${VENV_PATH}/bin/python -c 'import uvicorn; print(uvicorn.__version__)')"
echo ""
echo "🔒 Security:"
echo "   ✓ HTTPS enabled (self-signed certificate)"
echo "   ✓ HTTP redirects to HTTPS"
echo "   ✓ WebSocket proxied through Apache"
echo "   ✓ Service runs as user '${PYNQ_USER}' (not root)"
echo ""
echo "📝 Architecture:"
echo "   Apache/PHP → Web Interface (port 443, runs as www-data)"
echo "   Python     → WebSocket Server (port 8000, runs as ${PYNQ_USER})"
echo "   MariaDB    → Database (port 3306)"
echo "   PYNQ       → FPGA/Hardware Access (${VENV_PATH})"
echo ""
echo "🚀 Auto-start on Boot:"
echo "   ✓ spicer-daq.service enabled"
echo "   ✓ Server will start automatically as '${PYNQ_USER}' on boot"
echo ""
echo "🧪 Quick Tests:"
echo "   curl -k https://${SERVER_IP}/"
echo "   curl http://127.0.0.1:8000/health"
echo "   sudo systemctl status spicer-daq"
echo "   sudo ss -tlnp | grep ':8000\\|:443'"
echo ""
echo "⚠️  Note: Accept self-signed certificate warning in browser"
echo "============================================================================"
echo ""
echo "Installation log: ${LOG_FILE}"
echo ""