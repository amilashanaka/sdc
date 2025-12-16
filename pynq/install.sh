#!/bin/bash

# ============================================================================
# Spicer DAQ Installation Script for PYNQ Z1
# ============================================================================
# Run as: bash install.sh
# Default PYNQ password will be used automatically
# ============================================================================

set -euo pipefail

# ============================================================================
# CONFIGURATION PARAMETERS - EDIT THESE FOR YOUR NETWORK
# ============================================================================
PYNQ_USER="xilinx"
PYNQ_PASS="xilinx"
KEEP_JUPYTER="false"

# Windows Network Share Configuration
WIN_SERVER="192.168.1.100"           # Replace with your Windows PC IP
WIN_SHARE="Software"                  # Share name (Y: drive root)
WIN_SUBDIR="sdc_dev"                  # Subdirectory within share
WIN_USER="yourusername"               # Windows username (leave empty for guest)
WIN_PASS="yourpassword"               # Windows password (leave empty for guest)
DOMAIN="WORKGROUP"                    # Windows domain/workgroup

# Local mount point (will be created)
MOUNT_POINT="/mnt/windows_share"
LOCAL_SOURCE_PATH="${MOUNT_POINT}/${WIN_SUBDIR}"

# ============================================================================
# SYSTEM CONFIGURATION
# ============================================================================
LOG_FILE="/tmp/install_$(date +%Y%m%d_%H%M%S).log"
APP_DIR="/var/www/html"
WWW_USER="root"
WWW_GROUP="root"
DB_PASS="daq"
DB_NAME="daq"
SERVER_IP="$(hostname -I | awk '{print $1}')"
VENV_PATH="/usr/local/share/pynq-venv"
SSL_CERT="/etc/ssl/certs/apache-selfsigned.crt"
SSL_KEY="/etc/ssl/private/apache-selfsigned.key"

# Colors
BLUE='\033[0;34m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m'

log(){ echo -e "${BLUE}[*] $*${NC}"; }
ok(){ echo -e "${GREEN}[OK] $*${NC}"; }
warn(){ echo -e "${YELLOW}[WARN] $*${NC}"; }
err(){ echo -e "${RED}[ERROR] $*${NC}"; }

# Check if running as root
if [ "$EUID" -ne 0 ]; then 
    echo "Elevating to root..."
    echo "${PYNQ_PASS}" | sudo -S bash "$0" "$@"
    exit $?
fi

echo "============================================================================"
echo "  Spicer DAQ Installation for PYNQ Z1"
echo "  Source: Windows Network Share (//${WIN_SERVER}/${WIN_SHARE}/${WIN_SUBDIR})"
echo "  Jupyter Notebook: $([ "$KEEP_JUPYTER" = "true" ] && echo "KEEP" || echo "DISABLE")"
echo "============================================================================"
echo ""

# ============================================================================
# Function to mount Windows share
# ============================================================================
mount_windows_share() {
    log "Mounting Windows network share..."
    
    # Install cifs-utils if not present
    if ! dpkg -l | grep -q cifs-utils; then
        apt install -y cifs-utils >>$LOG_FILE 2>&1
    fi
    
    # Create mount point
    mkdir -p "${MOUNT_POINT}"
    
    # Unmount if already mounted
    umount -l "${MOUNT_POINT}" 2>/dev/null || true
    
    # Prepare credentials if provided
    if [ -n "$WIN_USER" ] && [ -n "$WIN_PASS" ]; then
        # Create credentials file
        cat > /tmp/smb.cred << EOF
username=${WIN_USER}
password=${WIN_PASS}
domain=${DOMAIN}
EOF
        chmod 600 /tmp/smb.cred
        MOUNT_OPTS="credentials=/tmp/smb.sec,uid=0,gid=0,iocharset=utf8"
    else
        MOUNT_OPTS="guest,uid=0,gid=0,iocharset=utf8"
    fi
    
    # Mount the share
    if mount -t cifs "//${WIN_SERVER}/${WIN_SHARE}" "${MOUNT_POINT}" -o "${MOUNT_OPTS}"; then
        ok "Windows share mounted at ${MOUNT_POINT}"
        return 0
    else
        err "Failed to mount Windows share"
        echo "Check the following:"
        echo "1. Windows PC IP address: ${WIN_SERVER}"
        echo "2. Share name: ${WIN_SHARE}"
        echo "3. Network connectivity"
        echo "4. Windows sharing permissions"
        return 1
    fi
}

# ============================================================================
# Function to unmount Windows share
# ============================================================================
unmount_windows_share() {
    log "Unmounting Windows share..."
    umount -l "${MOUNT_POINT}" 2>/dev/null || true
    rm -f /tmp/smb.cred 2>/dev/null
    ok "Windows share unmounted"
}

# ============================================================================
# OPTIMIZE: Parallel apt update in background
# ============================================================================
log "Starting system update (background)..."
apt update -y >>$LOG_FILE 2>&1 &
APT_PID=$!

# ============================================================================
# Handle Jupyter Notebook
# ============================================================================
log "Checking PYNQ Jupyter on port 9090..."
JUPYTER_RUNNING=false
if ss -ltn | grep -q ':9090'; then
    JUPYTER_RUNNING=true
    ok "Jupyter Notebook is running on port 9090"
else
    warn "Jupyter Notebook not detected on port 9090"
fi

if [ "$KEEP_JUPYTER" = "false" ]; then
    if [ "$JUPYTER_RUNNING" = "true" ]; then
        log "Disabling Jupyter Notebook..."
        systemctl stop jupyter 2>/dev/null || true
        systemctl disable jupyter 2>/dev/null || true
        pkill -f jupyter 2>/dev/null || true
        ok "Jupyter Notebook stopped and disabled"
    fi
else
    ok "Keeping Jupyter Notebook enabled"
fi

# ============================================================================
# Check Port 80
# ============================================================================
log "Checking port 80..."
PORT_80_PID=$(ss -tulpn | grep ':80 ' | grep -v apache2 | awk '{print $7}' | grep -oP 'pid=\K[0-9]+' | head -1)
if [ -n "$PORT_80_PID" ]; then
    PORT_80_CMD=$(ps -p $PORT_80_PID -o comm=)
    
    if [[ "$PORT_80_CMD" == *"jupyter"* ]] || [[ "$PORT_80_CMD" == *"pynq"* ]]; then
        err "Port 80 is used by PYNQ service - ABORTING"
        exit 1
    fi
    
    log "Freeing port 80 (PID: $PORT_80_PID)..."
    kill $PORT_80_PID 2>/dev/null || true
    sleep 1
    ok "Port 80 freed"
fi

# Wait for apt update to complete
wait $APT_PID 2>/dev/null || true

# ============================================================================
# Mount Windows share
# ============================================================================
if ! mount_windows_share; then
    exit 1
fi

# Validate source path
if [ ! -d "$LOCAL_SOURCE_PATH" ]; then
    err "Source directory not found: $LOCAL_SOURCE_PATH"
    echo "Expected path: //${WIN_SERVER}/${WIN_SHARE}/${WIN_SUBDIR}"
    unmount_windows_share
    exit 1
fi

# ============================================================================
# OPTIMIZE: Install packages with minimal interaction
# ============================================================================
log "Installing system packages..."
export DEBIAN_FRONTEND=noninteractive
apt install -y --no-install-recommends \
    apache2 apache2-utils \
    php libapache2-mod-php php-mysql php-cli \
    mariadb-server mariadb-client \
    openssl >>$LOG_FILE 2>&1 &
INSTALL_PID=$!

# ============================================================================
# OPTIMIZE: Generate SSL cert while packages install
# ============================================================================
log "Generating SSL certificate..."
mkdir -p /etc/ssl/private
if [ ! -f "$SSL_CERT" ] || [ ! -f "$SSL_KEY" ]; then
    openssl req -x509 -nodes -days 365 -newkey rsa:2048 \
        -keyout "$SSL_KEY" \
        -out "$SSL_CERT" \
        -subj "/C=US/ST=State/L=City/O=Org/CN=${SERVER_IP}" >>$LOG_FILE 2>&1 &
    SSL_PID=$!
else
    ok "Using existing SSL certificate"
    SSL_PID=""
fi

# Wait for package installation
wait $INSTALL_PID 2>/dev/null || true
ok "System packages installed"

# Wait for SSL generation if running
[ -n "$SSL_PID" ] && wait $SSL_PID 2>/dev/null || true
[ ! -f "$SSL_CERT" ] || ok "SSL certificate ready"

# ============================================================================
# Configure Apache modules
# ============================================================================
log "Configuring Apache..."
a2enmod rewrite ssl proxy proxy_http proxy_wstunnel headers >>$LOG_FILE 2>&1
systemctl enable apache2 >/dev/null 2>&1 || true
ok "Apache configured"

# ============================================================================
# OPTIMIZE: Backup + Copy from Windows share in parallel
# ============================================================================
if [ "$(ls -A ${APP_DIR} 2>/dev/null)" ]; then
    BACKUP_DIR="/tmp/html_backup_$(date +%Y%m%d_%H%M%S)"
    log "Backing up ${APP_DIR}..."
    mkdir -p "${BACKUP_DIR}"
    cp -r ${APP_DIR}/* "${BACKUP_DIR}/" 2>/dev/null &
    BACKUP_PID=$!
else
    BACKUP_PID=""
fi

log "Cleaning ${APP_DIR}..."
rm -rf ${APP_DIR}/*
rm -rf ${APP_DIR}/.[!.]* 2>/dev/null || true

# Wait for backup if running
[ -n "$BACKUP_PID" ] && wait $BACKUP_PID 2>/dev/null || true

log "Copying source code from Windows share..."
# Copy all files including hidden ones
shopt -s dotglob
if cp -r "$LOCAL_SOURCE_PATH"/* "$APP_DIR"/ 2>/dev/null; then
    shopt -u dotglob
    # Check if files were copied
    if [ "$(ls -A ${APP_DIR} 2>/dev/null)" ]; then
        ok "Source code copied from Windows share"
    else
        err "No files found in source directory"
        unmount_windows_share
        exit 1
    fi
else
    err "Failed to copy files from Windows share"
    unmount_windows_share
    exit 1
fi

# Unmount Windows share after copying
unmount_windows_share

chown -R ${WWW_USER}:${WWW_GROUP} ${APP_DIR}
chmod -R 775 ${APP_DIR}

# ============================================================================
# OPTIMIZE: Configure MariaDB + Install Python packages in parallel
# ============================================================================
log "Configuring MariaDB..."
systemctl start mariadb &
MARIADB_PID=$!

# Start Python package installation in parallel
log "Installing Python packages..."
(
    if [ -d "$VENV_PATH" ]; then
        $VENV_PATH/bin/pip install --upgrade pip setuptools wheel >>$LOG_FILE 2>&1
        $VENV_PATH/bin/pip install fastapi==0.124.0 >>$LOG_FILE 2>&1
        $VENV_PATH/bin/pip install 'uvicorn[standard]==0.38.0' >>$LOG_FILE 2>&1
        $VENV_PATH/bin/pip install websockets==10.3 >>$LOG_FILE 2>&1
        $VENV_PATH/bin/pip install pymysql python-multipart >>$LOG_FILE 2>&1
    fi
) &
PIP_PID=$!

# Wait for MariaDB
wait $MARIADB_PID 2>/dev/null || true
sleep 2

# Configure MariaDB
mysql -u root <<EOF 2>/dev/null || true
ALTER USER 'root'@'localhost' IDENTIFIED VIA mysql_native_password USING PASSWORD('${DB_PASS}');
DELETE FROM mysql.user WHERE User='';
DELETE FROM mysql.user WHERE User='root' AND Host NOT IN ('localhost', '127.0.0.1', '::1');
DROP DATABASE IF EXISTS test;
DELETE FROM mysql.db WHERE Db='test' OR Db='test\\_%';
FLUSH PRIVILEGES;
EOF

mysql -u root -p${DB_PASS} -e "CREATE DATABASE IF NOT EXISTS ${DB_NAME};" 2>/dev/null || true

SQL_FILE="${APP_DIR}/db/table.sql"
if [ -f "$SQL_FILE" ]; then
    mysql -u root -p${DB_PASS} ${DB_NAME} < "$SQL_FILE" >>$LOG_FILE 2>&1
fi
ok "MariaDB configured"

# Wait for Python packages
wait $PIP_PID 2>/dev/null || true

# Verify Python installation
if [ -d "$VENV_PATH" ] && $VENV_PATH/bin/python -c "import fastapi, uvicorn, websockets" 2>/dev/null; then
    ok "Python packages installed"
else
    err "Python package installation failed"
    exit 1
fi

# ============================================================================
# Verify server.py
# ============================================================================
SERVER_PY_PATH="${APP_DIR}/pynq/server.py"
if [ -f "$SERVER_PY_PATH" ]; then
    chmod +x "$SERVER_PY_PATH"
    ok "server.py ready"
else
    err "server.py not found"
    exit 1
fi

# ============================================================================
# Create systemd service
# ============================================================================
log "Creating systemd service..."
tee /etc/systemd/system/spicer-daq.service > /dev/null << 'EOF'
[Unit]
Description=Spicer DAQ WebSocket Server
After=network-online.target systemd-modules-load.service
Wants=network-online.target
StartLimitIntervalSec=300
StartLimitBurst=5

[Service]
Type=simple
User=root
Group=root
WorkingDirectory=/var/www/html/pynq

ExecStartPre=/bin/sleep 15
ExecStartPre=/bin/sh -c '/bin/fuser -k 8000/tcp 2>/dev/null || true'
ExecStartPre=/bin/sleep 2
ExecStart=/usr/local/share/pynq-venv/bin/python /var/www/html/pynq/server.py

Restart=on-failure
RestartSec=30
TimeoutStartSec=60
TimeoutStopSec=30

Environment="PYTHONUNBUFFERED=1"
Environment="XILINX_XRT=/usr"
Environment="LD_LIBRARY_PATH=/usr/lib"

StandardOutput=journal
StandardError=journal
SyslogIdentifier=spicer-daq

[Install]
WantedBy=multi-user.target
EOF

# Stop existing processes
pkill -f "server.py" 2>/dev/null || true
pkill -f "uvicorn" 2>/dev/null || true
fuser -k 8000/tcp 2>/dev/null || true
sleep 2

systemctl daemon-reload
systemctl enable spicer-daq.service >>$LOG_FILE 2>&1
systemctl start spicer-daq.service
ok "Service started"

# ============================================================================
# Configure Apache virtual hosts
# ============================================================================
log "Configuring Apache virtual hosts..."

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

if apache2ctl configtest 2>&1 | grep -q "Syntax OK"; then
    systemctl restart apache2
    ok "Apache started"
else
    err "Apache config error"
    apache2ctl configtest
    exit 1
fi

chown -R ${WWW_USER}:${WWW_GROUP} ${APP_DIR}
chmod -R 775 ${APP_DIR}

# ============================================================================
# Installation Complete
# ============================================================================
echo ""
echo "============================================================================"
ok "Installation Complete!"
echo "============================================================================"
echo ""
echo "🌐 Access Points:"
echo "   Main Interface:   https://${SERVER_IP}/"
echo "   WebSocket:        ws://127.0.0.1:8000/ws (internal)"

if [ "$KEEP_JUPYTER" = "true" ]; then
    echo "   PYNQ Jupyter:     http://${SERVER_IP}:9090/"
else
    echo "   PYNQ Jupyter:     DISABLED"
fi

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
echo "   Logs:     sudo journalctl -u spicer-daq -f"
echo ""
echo "🐍 Python:"
echo "   Environment: ${VENV_PATH}"
echo "   Python:      $($VENV_PATH/bin/python --version 2>/dev/null || echo 'N/A')"

if [ "$KEEP_JUPYTER" = "false" ]; then
    echo ""
    echo "⚡ Performance:"
    echo "   Jupyter disabled - more resources available for DAQ"
    echo "   To re-enable: sudo systemctl start jupyter && sudo systemctl enable jupyter"
fi

echo ""
echo "📂 Source: Windows Network Share (//${WIN_SERVER}/${WIN_SHARE}/${WIN_SUBDIR})"
echo "✅ Auto-starts on boot (15s delay for FPGA initialization)"
echo "✅ HTTPS enabled with self-signed certificate"
echo "✅ Runs as root for FPGA hardware access"
echo "============================================================================"
echo ""
echo "Installation log: ${LOG_FILE}"
echo ""

# Show installation time
INSTALL_END=$(date +%s)
INSTALL_START=$(stat -c %Y $LOG_FILE 2>/dev/null || echo $INSTALL_END)
INSTALL_TIME=$((INSTALL_END - INSTALL_START))
echo "⏱️  Installation completed in ${INSTALL_TIME} seconds"
echo ""