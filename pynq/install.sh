#!/bin/bash

set -euo pipefail

LOG_FILE="/tmp/install_$(date +%Y%m%d_%H%M%S).log"
APP_DIR="/var/www/html"
WWW_USER="www-data"
DB_PASS="daq"
DB_NAME="daq"
SERVICE="spicer-daq"
SERVER_IP="$(hostname -I | awk '{print $1}')"

BLUE='\033[0;34m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m'

log(){ echo -e "${BLUE}[*] $*${NC}"; }
ok(){ echo -e "${GREEN}[OK] $*${NC}"; }
warn(){ echo -e "${YELLOW}[WARN] $*${NC}"; }

log "Starting installation..."
sudo apt update -y >>$LOG_FILE

##################################################################
# CHECK if 9090 is alive (DO NOT KILL ANYTHING)
##################################################################
log "Checking port 9090..."
if ss -ltn | grep -q ':9090'; then
    ok "Port 9090 active – will keep reachable"
else
    warn "Port 9090 appears inactive (no change)"
fi

log "Checking redirect_server..."
if systemctl list-unit-files | grep -q redirect_server; then
    ok "redirect_server service exists – not removing"
else
    warn "redirect_server service not found"
fi

##################################################################
# APACHE + PHP
##################################################################
log "Installing Apache + PHP"
sudo DEBIAN_FRONTEND=noninteractive apt install -y \
 apache2 apache2-utils ssl-cert \
 php libapache2-mod-php php-mysql php-cli \
 git mariadb-server mariadb-client >>$LOG_FILE

sudo a2enmod rewrite proxy proxy_http proxy_wstunnel ssl >/dev/null || true
sudo systemctl enable apache2 >/dev/null || true

##################################################################
# PERMISSIONS (FULL access)
##################################################################
log "Full permissions for /var/www/html..."
sudo chown -R ${WWW_USER}:${WWW_USER} ${APP_DIR}
sudo chmod -R 775 ${APP_DIR}
ok "permissions applied"

##################################################################
# DATABASE
##################################################################
log "Configuring MariaDB..."
sudo systemctl start mariadb
sleep 1

sudo mysql -u root <<EOF || true
ALTER USER 'root'@'localhost' IDENTIFIED BY '${DB_PASS}';
FLUSH PRIVILEGES;
EOF

sudo mysql -u root -p${DB_PASS} -e \
 "CREATE DATABASE IF NOT EXISTS ${DB_NAME};"

##################################################################
# Import SQL if exists
##################################################################
SQL_FILE="${APP_DIR}/db/table.sql"
if [ -f "$SQL_FILE" ]; then
    log "Importing DB"
    sudo mysql -u root -p${DB_PASS} ${DB_NAME} < "$SQL_FILE"
    ok "SQL imported"
else
    warn "No DB file found at ${SQL_FILE}"
fi

##################################################################
# SYSTEMD SERVICE – use your file if exists in pynq/
##################################################################
log "Installing service ${SERVICE}"

if [ -f "${APP_DIR}/pynq/spicer-daq.service" ]; then
    sudo cp "${APP_DIR}/pynq/spicer-daq.service" /etc/systemd/system/${SERVICE}.service
else
cat <<EOF | sudo tee /etc/systemd/system/${SERVICE}.service >/dev/null
[Unit]
Description=Spicer DAQ FastAPI app
After=network.target

[Service]
Type=simple
User=www-data
WorkingDirectory=${APP_DIR}/pynq
ExecStart=/usr/bin/env python3 server.py
Restart=on-failure

[Install]
WantedBy=multi-user.target
EOF
fi

sudo systemctl daemon-reload
sudo systemctl enable ${SERVICE}.service
sudo systemctl restart ${SERVICE}.service || true

##################################################################
# APACHE HTTPS VHOST (HTTP→HTTPS, DO **NOT** TOUCH 9090)
##################################################################
log "Configuring Apache HTTPS redirect"

sudo tee /etc/apache2/sites-available/spicer.conf >/dev/null <<EOF
<VirtualHost *:80>
    ServerName ${SERVER_IP}
    Redirect permanent / https://${SERVER_IP}/
</VirtualHost>

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

    RewriteEngine On

    # API to DAQ
    ProxyPass /api/ http://127.0.0.1:8000/api/
    ProxyPassReverse /api/ http://127.0.0.1:8000/api/
</VirtualHost>
EOF

sudo a2dissite 000-default default-ssl >/dev/null || true
sudo a2ensite spicer.conf >/dev/null
sudo systemctl restart apache2

##################################################################
# FINAL PERMISSIONS AND CHECKS
##################################################################
log "Final permissions"
sudo chown -R ${WWW_USER}:${WWW_USER} ${APP_DIR}
sudo chmod -R 775 ${APP_DIR}

log "Check server.py..."
if [ -f "${APP_DIR}/pynq/server.py" ]; then
    ok "server.py found"
else
    warn "server.py NOT found in /pynq"
fi

ok "Install done"
echo "Open: https://${SERVER_IP}/"
echo "Old UI (if present): http://${SERVER_IP}:9090/"
echo "DB root password: daq"
echo "Service: ${SERVICE}"
echo "Logs: journalctl -u ${SERVICE} -f"
