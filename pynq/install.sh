#!/bin/bash
set -euo pipefail

LOG_FILE="/tmp/install_$(date +%Y%m%d_%H%M%S).log"
APP_DIR="/opt/spicer-daq"  # Changed to avoid conflict with PYNQ
WWW_USER="www-data"
DB_PASS="daq"
DB_NAME="daq"
SERVICE="spicer-daq"
SERVER_IP="$(hostname -I | awk '{print $1}')"
REPO_URL="https://github.com/amilashanaka/sdc.git"  # UPDATE THIS!

BLUE='\033[0;34m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m'

log(){ echo -e "${BLUE}[*] $*${NC}"; }
ok(){ echo -e "${GREEN}[OK] $*${NC}"; }
warn(){ echo -e "${YELLOW}[WARN] $*${NC}"; }
err(){ echo -e "${RED}[ERROR] $*${NC}"; }

log "Starting installation (preserving PYNQ)..."
sudo apt update -y >>$LOG_FILE

log "Checking PYNQ on port 9090..."
if ss -ltn | grep -q ':9090'; then
    ok "Port 9090 active – PYNQ running, will preserve"
else
    warn "Port 9090 inactive – PYNQ may not be running"
fi

log "Checking redirect_server service..."
if systemctl list-unit-files | grep -q redirect_server; then
    ok "redirect_server exists – preserving"
else
    warn "redirect_server not found (normal if not using it)"
fi

log "Installing Apache + PHP + Git + Python dependencies"
export DEBIAN_FRONTEND=noninteractive
sudo -E apt install -y \
 apache2 apache2-utils ssl-cert \
 php libapache2-mod-php php-mysql php-cli \
 git mariadb-server mariadb-client \
 python3-pip python3-venv >>$LOG_FILE 2>&1
ok "Packages installed"

sudo a2enmod rewrite proxy proxy_http proxy_wstunnel ssl >/dev/null || true
sudo systemctl enable apache2 >/dev/null || true

log "Creating application directory at ${APP_DIR}..."
sudo mkdir -p "${APP_DIR}"

# Backup if directory already exists
if [ "$(ls -A ${APP_DIR} 2>/dev/null)" ]; then
    BACKUP_DIR="/tmp/spicer_backup_$(date +%Y%m%d_%H%M%S)"
    log "Backing up existing ${APP_DIR} to ${BACKUP_DIR}"
    sudo mkdir -p "${BACKUP_DIR}"
    sudo cp -r ${APP_DIR}/* "${BACKUP_DIR}/" 2>/dev/null || true
    ok "Backup created"
fi

# Clean and clone
log "Cleaning ${APP_DIR}..."
sudo rm -rf ${APP_DIR}/*
sudo rm -rf ${APP_DIR}/.[!.]* 2>/dev/null || true

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
 "CREATE DATABASE IF NOT EXISTS ${DB_NAME};"

SQL_FILE="${APP_DIR}/db/table.sql"
if [ -f "$SQL_FILE" ]; then
    log "Importing database schema"
    sudo mysql -u root -p${DB_PASS} ${DB_NAME} < "$SQL_FILE"
    ok "Database imported"
else
    warn "No SQL file found at ${SQL_FILE}"
fi

log "Installing FastAPI service ${SERVICE}..."

if [ -f "${APP_DIR}/pynq/spicer-daq.service" ]; then
    sudo cp "${APP_DIR}/pynq/spicer-daq.service" /etc/systemd/system/${SERVICE}.service
    # Update WorkingDirectory in service file
    sudo sed -i "s|WorkingDirectory=.*|WorkingDirectory=${APP_DIR}/pynq|g" /etc/systemd/system/${SERVICE}.service
else
cat <<EOF | sudo tee /etc/systemd/system/${SERVICE}.service >/dev/null
[Unit]
Description=Spicer DAQ FastAPI app
After=network.target

[Service]
Type=simple
User=www-data
WorkingDirectory=${APP_DIR}/pynq
ExecStart=/usr/bin/python3 server.py
Restart=on-failure
RestartSec=5

[Install]
WantedBy=multi-user.target
EOF
fi

sudo systemctl daemon-reload
sudo systemctl enable ${SERVICE}.service
sudo systemctl restart ${SERVICE}.service || true

log "Configuring Apache (port 80/443 for your app, preserving :9090 for PYNQ)..."

sudo tee /etc/apache2/sites-available/spicer.conf >/dev/null <<EOF
# HTTP -> HTTPS redirect
<VirtualHost *:80>
    ServerName ${SERVER_IP}
    Redirect permanent / https://${SERVER_IP}/
</VirtualHost>

# Main HTTPS site for Spicer DAQ app
<VirtualHost *:443>
    ServerName ${SERVER_IP}
    DocumentRoot ${APP_DIR}

    SSLEngine on
    SSLCertificateFile /etc/ssl/certs/ssl-cert-snakeoil.pem
    SSLCertificateKeyFile /etc/ssl/private/ssl-cert-snakeoil.key

    <Directory ${APP_DIR}>
        AllowOverride All
        Require all granted
    </Directory>

    # Proxy API requests to FastAPI backend
    ProxyPreserveHost On
    ProxyPass /api/ http://127.0.0.1:8000/api/
    ProxyPassReverse /api/ http://127.0.0.1:8000/api/

    # WebSocket support if needed
    RewriteEngine On
    RewriteCond %{HTTP:Upgrade} websocket [NC]
    RewriteCond %{HTTP:Connection} upgrade [NC]
    RewriteRule ^/?(.*) "ws://127.0.0.1:8000/\$1" [P,L]

    # Logging
    ErrorLog \${APACHE_LOG_DIR}/spicer_error.log
    CustomLog \${APACHE_LOG_DIR}/spicer_access.log combined
</VirtualHost>
EOF

sudo a2dissite 000-default default-ssl >/dev/null 2>&1 || true
sudo a2ensite spicer.conf >/dev/null
sudo systemctl restart apache2

log "Final permission check..."
sudo chown -R ${WWW_USER}:${WWW_USER} ${APP_DIR}
sudo chmod -R 775 ${APP_DIR}

log "Verifying installation..."
if [ -f "${APP_DIR}/pynq/server.py" ]; then
    ok "server.py found"
else
    warn "server.py NOT found at ${APP_DIR}/pynq/server.py"
fi

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

ok "Installation complete!"
echo ""
echo "=========================================="
echo "🎯 Access Points:"
echo "  Your App (HTTP):  http://${SERVER_IP}/"
echo "  Your App (HTTPS): https://${SERVER_IP}/"
echo "  PYNQ Jupyter:     http://${SERVER_IP}:9090/tree"
echo ""
echo "📊 Database:"
echo "  Name: ${DB_NAME}"
echo "  User: root"
echo "  Pass: ${DB_PASS}"
echo ""
echo "⚙️  Service Management:"
echo "  Service: ${SERVICE}"
echo "  Status:  sudo systemctl status ${SERVICE}"
echo "  Logs:    journalctl -u ${SERVICE} -f"
echo "  Restart: sudo systemctl restart ${SERVICE}"
echo ""
echo "📁 Application Location: ${APP_DIR}"
echo "=========================================="