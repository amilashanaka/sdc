#!/bin/bash

# ============================================================================
# Spicer DAQ Installation Script for PYNQ Z1
# ============================================================================
# Run as: bash install.sh
# Default PYNQ password will be used automatically
# ============================================================================

set -euo pipefail

# PYNQ default credentials
PYNQ_USER="xilinx"
PYNQ_PASS="xilinx"

# Check if running as root, if not use sudo with password
if [ "$EUID" -ne 0 ]; then 
    echo "Elevating to root..."
    echo "${PYNQ_PASS}" | sudo -S bash "$0" "$@"
    exit $?
fi

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

echo "============================================================================"
echo "  Spicer DAQ Installation for PYNQ Z1"
echo "  Running as: root (required for FPGA hardware access)"
echo "============================================================================"
echo ""

log "Starting installation (preserving PYNQ on :9090)..."
apt update -y >>$LOG_FILE 2>&1

log "Checking PYNQ on port 9090..."
if ss -ltn | grep -q ':9090'; then
    ok "Port 9090 active - PYNQ running, will preserve"
else
    warn "Port 9090 inactive - PYNQ may not be running"
fi

# Check and stop conflicting service on port 80
log "Checking port 80..."
PORT_80_PID=$(ss -tulpn | grep ':80 ' | grep -v apache2 | awk '{print $7}' | grep -oP 'pid=\K[0-9]+' | head -1)
if [ -n "$PORT_80_PID" ]; then
    PORT_80_CMD=$(ps -p $PORT_80_PID -o comm=)
    warn "Port 80 is in use by PID $PORT_80_PID ($PORT_80_CMD)"
    
    # Safety check
    if [[ "$PORT_80_CMD" == *"jupyter"* ]] || [[ "$PORT_80_CMD" == *"pynq"* ]]; then
        err "Port 80 is used by PYNQ system service - ABORTING"
        exit 1
    fi
    
    log "Stopping process on port 80..."
    kill $PORT_80_PID 2>/dev/null || true
    sleep 2
    ok "Port 80 freed"
fi

log "Installing Apache + PHP + Git + Python dependencies + SSL tools"
export DEBIAN_FRONTEND=noninteractive
apt install -y \
    apache2 apache2-utils \
    php libapache2-mod-php php-mysql php-cli \
    git mariadb-server mariadb-client \
    python3-pip python3-venv \
    python3-dev build-essential libssl-dev libffi-dev \
    openssl >>$LOG_FILE 2>&1
ok "Packages installed"

a2enmod rewrite ssl proxy proxy_http proxy_wstunnel headers >>$LOG_FILE 2>&1
systemctl enable apache2 >/dev/null 2>&1 || true

# Generate self-signed certificate if not exists
log "Setting up self-signed SSL certificate..."
mkdir -p /etc/ssl/private
if [ ! -f "$SSL_CERT" ] || [ ! -f "$SSL_KEY" ]; then
    openssl req -x509 -nodes -days 365 -newkey rsa:2048 \
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
    mkdir -p "${BACKUP_DIR}"
    cp -r ${APP_DIR}/* "${BACKUP_DIR}/" 2>/dev/null || true
    ok "Backup created at ${BACKUP_DIR}"
fi

# Clean /var/www/html completely
log "Cleaning ${APP_DIR}..."
rm -rf ${APP_DIR}/*
rm -rf ${APP_DIR}/.[!.]* 2>/dev/null || true
ok "Directory cleaned"

# Clone repository
log "Cloning repository from ${REPO_URL}..."
if git clone "${REPO_URL}" "${APP_DIR}" >>$LOG_FILE 2>&1; then
    ok "Repository cloned successfully"
else
    err "Failed to clone repository. Check the URL: ${REPO_URL}"
    exit 1
fi

# Set permissions
log "Setting permissions for ${APP_DIR}..."
chown -R ${WWW_USER}:${WWW_GROUP} ${APP_DIR}
chmod -R 775 ${APP_DIR}
ok "Permissions applied"

log "Configuring MariaDB..."
systemctl start mariadb
sleep 2

# Secure MariaDB installation
mysql -u root <<EOF 2>/dev/null || true
ALTER USER 'root'@'localhost' IDENTIFIED VIA mysql_native_password USING PASSWORD('${DB_PASS}');
DELETE FROM mysql.user WHERE User='';
DELETE FROM mysql.user WHERE User='root' AND Host NOT IN ('localhost', '127.0.0.1', '::1');
DROP DATABASE IF EXISTS test;
DELETE FROM mysql.db WHERE Db='test' OR Db='test\\_%';
FLUSH PRIVILEGES;
EOF

mysql -u root -p${DB_PASS} -e \
    "CREATE DATABASE IF NOT EXISTS ${DB_NAME};" 2>/dev/null || true

SQL_FILE="${APP_DIR}/db/table.sql"
if [ -f "$SQL_FILE" ]; then
    log "Importing database schema"
    mysql -u root -p${DB_PASS} ${DB_NAME} < "$SQL_FILE"
    ok "Database imported"
else
    warn "No SQL file found at ${SQL_FILE}"
fi

# Setup PYNQ virtual environment
log "Setting up Python virtual environment..."
if [ -d "$VENV_PATH" ]; then
    ok "Using existing PYNQ virtual environment at $VENV_PATH"
else
    log "Creating virtual environment at $VENV_PATH..."
    python3 -m venv "$VENV_PATH"
    ok "Virtual environment created"
fi

# Install Python packages
log "Installing Python packages in virtual environment..."
$VENV_PATH/bin/pip install --upgrade pip setuptools wheel >>$LOG_FILE 2>&1

# Install exact versions
$VENV_PATH/bin/pip install fastapi==0.124.0 >>$LOG_FILE 2>&1
$VENV_PATH/bin/pip install 'uvicorn[standard]==0.38.0' >>$LOG_FILE 2>&1
$VENV_PATH/bin/pip install websockets==10.3 >>$LOG_FILE 2>&1
$VENV_PATH/bin/pip install pymysql python-multipart >>$LOG_FILE 2>&1

# Verify installation
log "Verifying Python package installation..."
if $VENV_PATH/bin/python -c "import fastapi; import uvicorn; import websockets; print('OK')" 2>>$LOG_FILE; then
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
    chmod +x "$SERVER_PY_PATH"
else
    err "server.py NOT found at ${APP_DIR}/pynq/server.py"
    exit 1
fi

# Create systemd service file with proper dependencies and delays
log "Creating systemd service file..."
tee /etc/systemd/system/spicer-daq.service > /dev/null << 'EOF'
[Unit]
Description=Spicer DAQ WebSocket Server
After=network-online.target
Wants=network-online.target
# Wait for FPGA to be ready
After=systemd-modules-load.service
# Start after a delay to ensure system is fully initialized
StartLimitIntervalSec=300
StartLimitBurst=5

[Service]
Type=simple
User=root
Group=root
WorkingDirectory=/var/www/html/pynq

# CRITICAL: Add delay before starting to avoid boot hang
ExecStartPre=/bin/sleep 15

# Kill any existing process on port 8000
ExecStartPre=/bin/sh -c '/bin/fuser -k 8000/tcp 2>/dev/null || true'
ExecStartPre=/bin/sleep 2

# Start the server
ExecStart=/usr/local/share/pynq-venv/bin/python /var/www/html/pynq/server.py

# Restart policy - but with delays to prevent boot hang
Restart=on-failure
RestartSec=30

# Timeout settings
TimeoutStartSec=60
TimeoutStopSec=30

# Environment variables for PYNQ/FPGA
Environment="PYTHONUNBUFFERED=1"
Environment="XILINX_XRT=/usr"
Environment="LD_LIBRARY_PATH=/usr/lib"

# Logging
StandardOutput=journal
StandardError=journal
SyslogIdentifier=spicer-daq

[Install]
WantedBy=multi-user.target
EOF

# Stop any existing server processes
log "Stopping any existing server processes..."
pkill -f "server.py" 2>/dev/null || true
pkill -f "uvicorn" 2>/dev/null || true
fuser -k 8000/tcp 2>/dev/null || true
sleep 3

# Enable and start the service
systemctl daemon-reload
systemctl enable spicer-daq.service >>$LOG_FILE 2>&1

log "Starting FastAPI server via systemd..."
if systemctl start spicer-daq.service; then
    ok "Spicer DAQ server service started"
    
    # Wait for server to start
    sleep 8
    
    # Check service status
    if systemctl is-active --quiet spicer-daq.service; then
        ok "Spicer DAQ server service is active"
        
        # Check if it's listening on port 8000
        if ss -tlnp | grep -q ':8000'; then
            ok "Server is listening on port 8000"
        else
            warn "Server not listening on port 8000 yet. This may be normal during FPGA initialization."
            warn "Check logs with: sudo journalctl -u spicer-daq.service -f"
        fi
    else
        warn "Service may still be initializing (FPGA loading takes time)"
        warn "Check status with: sudo systemctl status spicer-daq"
    fi
else
    warn "Service start command issued - may be initializing"
fi

log "Configuring Apache for HTTP and HTTPS..."
# Create HTTP virtual host with redirect to HTTPS
tee /etc/apache2/sites-available/spicer.conf > /dev/null << EOF
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
tee /etc/apache2/sites-available/spicer-ssl.conf > /dev/null << EOF
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

a2dissite 000-default default-ssl >/dev/null 2>&1 || true
a2ensite spicer spicer-ssl >/dev/null 2>&1

# Test Apache configuration
log "Testing Apache configuration..."
if apache2ctl configtest 2>&1 | grep -q "Syntax OK"; then
    ok "Apache config syntax OK"
else
    err "Apache config has errors:"
    apache2ctl configtest
    exit 1
fi

# Start Apache
log "Starting Apache..."
if systemctl restart apache2; then
    ok "Apache started successfully"
else
    err "Apache failed to start. Checking logs..."
    journalctl -xeu apache2.service --no-pager | tail -20
    exit 1
fi

log "Final permission check..."
chown -R ${WWW_USER}:${WWW_GROUP} ${APP_DIR}
chmod -R 775 ${APP_DIR}

ok "Installation complete!"
echo ""
echo "============================================================================"
echo "🎯 Access Points:"
echo "  Main Access:      https://${SERVER_IP}/ (self-signed cert, ignore warnings)"
echo "  FastAPI Server:   http://127.0.0.1:8000/ (internal only)"
echo "  WebSocket (WS):   ws://127.0.0.1:8000/ws (internal)"
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
echo ""
echo "🐍 Python Environment:"
echo "  Virtual env: ${VENV_PATH}"
echo "  Python: $($VENV_PATH/bin/python --version)"
echo "  FastAPI: $($VENV_PATH/bin/python -c 'import fastapi; print(fastapi.__version__)')"
echo "  Uvicorn: $($VENV_PATH/bin/python -c 'import uvicorn; print(uvicorn.__version__)')"
echo ""
echo "🔧 Quick Tests:"
echo "  Check service: sudo systemctl status spicer-daq"
echo "  Test Apache HTTPS: curl -k https://${SERVER_IP}/"
echo "  Check port 8000: sudo ss -tlnp | grep :8000"
echo "  Check port 443: sudo ss -tlnp | grep :443"
echo ""
echo "⚠️  IMPORTANT - Boot Behavior:"
echo "  The service has a 15-second startup delay to prevent boot hangs"
echo "  This allows the FPGA and system to fully initialize first"
echo "  If you see delays on boot, this is normal and expected"
echo ""
echo "✅ HTTPS enabled with self-signed certificate"
echo "✅ HTTP redirects to HTTPS"
echo "✅ WebSocket proxied through Apache"
echo "✅ Uses PYNQ virtual environment at ${VENV_PATH}"
echo "✅ Auto-starts on boot (with 15s delay)"
echo "============================================================================"
echo ""
echo "Installation log: ${LOG_FILE}"
echo ""
echo "Note: The server runs as ROOT (required for FPGA hardware access)"
echo "Access via HTTPS, accept self-signed certificate warning in browser."
echo ""
echo "If the server doesn't start immediately, wait 15-30 seconds for FPGA init."