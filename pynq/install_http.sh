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
 python3-pip python3-venv >>$LOG_FILE 2>&1
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

sudo mysql -u root <<EOF 2>/dev/null || true
ALTER USER 'root'@'localhost' IDENTIFIED BY '${DB_PASS}';
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

log "Setting up FastAPI server ${PROCESS_NAME}..."

# Detect Python virtual environment
PYNQ_VENV="/usr/local/share/pynq-venv/bin/python"
if [ -f "$PYNQ_VENV" ]; then
    PYTHON_PATH="$PYNQ_VENV"
    ok "Using PYNQ virtual environment: $PYTHON_PATH"
else
    PYTHON_PATH="/usr/bin/python3"
    warn "PYNQ venv not found, using system python3"
fi

# Check if FastAPI is installed
log "Checking FastAPI installation..."
if $PYTHON_PATH -c "import fastapi" 2>/dev/null; then
    ok "FastAPI is available"
else
    warn "FastAPI not found. Installing dependencies..."
    if [ -f "${APP_DIR}/pynq/requirements.txt" ]; then
        $PYTHON_PATH -m pip install -r ${APP_DIR}/pynq/requirements.txt >>$LOG_FILE 2>&1 || true
    else
        $PYTHON_PATH -m pip install fastapi uvicorn >>$LOG_FILE 2>&1 || true
    fi
fi

# Update server.py to bind to 0.0.0.0 if needed
log "Checking server.py configuration..."
if [ -f "${APP_DIR}/pynq/server.py" ]; then
    ok "server.py found at ${APP_DIR}/pynq/server.py"
    
    # Update server.py to bind to 0.0.0.0 if needed
    if grep -q "127.0.0.1" "${APP_DIR}/pynq/server.py"; then
        log "Updating server.py to bind to 0.0.0.0..."
        sudo sed -i 's/127.0.0.1/0.0.0.0/g' "${APP_DIR}/pynq/server.py"
        ok "server.py updated to bind to 0.0.0.0"
    fi
else
    err "server.py NOT found at ${APP_DIR}/pynq/server.py"
    exit 1
fi

# Create rc.local if it doesn't exist
log "Setting up /etc/rc.local for auto-start..."
if [ ! -f /etc/rc.local ]; then
    sudo tee /etc/rc.local > /dev/null <<'EOF'
#!/bin/bash
# This script will be executed at the end of each multiuser runlevel.
# Make sure that the script will "exit 0" on success or any other value on error.

exit 0
EOF
    sudo chmod +x /etc/rc.local
fi

# Remove any existing server.py startup line from rc.local
sudo sed -i '/server.py/d' /etc/rc.local

# Add server.py startup command to rc.local (before exit 0)
STARTUP_CMD="cd ${APP_DIR}/pynq && sudo ${PYTHON_PATH} server.py > /tmp/server.log 2>&1 &"
sudo sed -i "/^exit 0/i${STARTUP_CMD}" /etc/rc.local

ok "Server startup added to /etc/rc.local"

# Start server.py now
log "Starting server.py now..."
cd ${APP_DIR}/pynq
if sudo ${PYTHON_PATH} server.py > /tmp/server_start.log 2>&1 & then
    SERVER_PID=$!
    ok "server.py started with PID: $SERVER_PID"
    sleep 3
    
    # Check if server is running
    if ps -p $SERVER_PID > /dev/null 2>&1; then
        ok "server.py is running (PID: $SERVER_PID)"
    else
        warn "server.py may have failed to start. Check /tmp/server_start.log"
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

# Create Apache virtual host configuration - serves static files only
sudo tee /etc/apache2/sites-available/spicer.conf >/dev/null <<EOF
# Main HTTP site for Spicer DAQ app - Static files only
<VirtualHost *:80>
    ServerName ${SERVER_IP}
    DocumentRoot ${APP_DIR}

    <Directory ${APP_DIR}>
        AllowOverride All
        Require all granted
        Options Indexes FollowSymLinks
    </Directory>

    # Logging
    ErrorLog \${APACHE_LOG_DIR}/spicer_error.log
    CustomLog \${APACHE_LOG_DIR}/spicer_access.log combined
</VirtualHost>
EOF

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

log "Verifying installation..."
if [ -f "${APP_DIR}/index.html" ] || [ -f "${APP_DIR}/index.php" ]; then
    ok "Web interface files found"
else
    warn "No index file found - verify repository contents"
fi

# Check if PYNQ is still accessible
if ss -ltn | grep -q ':9090'; then
    ok "PYNQ still running on port 9090"
else
    warn "PYNQ not detected on port 9090"
fi

# Check WebSocket connectivity
log "Testing WebSocket connectivity..."
sleep 3
if curl -s -I -X GET "http://127.0.0.1:8000/" >/dev/null 2>&1; then
    ok "FastAPI server responding on port 8000"
else
    warn "FastAPI not responding on port 8000. Checking if server.py is running..."
    
    # Try to start server.py again
    cd ${APP_DIR}/pynq
    if sudo ${PYTHON_PATH} server.py > /tmp/server_retry.log 2>&1 & then
        sleep 3
        if curl -s -I -X GET "http://127.0.0.1:8000/" >/dev/null 2>&1; then
            ok "FastAPI server now responding on port 8000"
        else
            warn "FastAPI still not responding. Check logs: /tmp/server_retry.log"
        fi
    fi
fi

ok "Installation complete!"
echo ""
echo "=========================================="
echo "🎯 Access Points:"
echo "  Static Files:     http://${SERVER_IP}/"
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
echo "  Manual start: cd ${APP_DIR}/pynq && sudo ${PYTHON_PATH} server.py"
echo "  Logs:    tail -f /tmp/server.log"
echo "  Check if running: ps aux | grep server.py"
echo ""
echo "🌐 WebSocket Test:"
echo "  Open: http://${SERVER_IP}/scope.php"
echo "  WebSocket connects directly to port 8000"
echo ""
echo "📁 Application: ${APP_DIR}"
echo "📄 Server.py:   ${APP_DIR}/pynq/server.py"
echo ""
echo "✅ server.py will auto-start on every boot via /etc/rc.local!"
echo "✅ Apache serves static files on port 80"
echo "✅ Uvicorn WebSocket on port 8000 (direct connection)"
echo "=========================================="