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

log() { echo -e "${BLUE}[*] $*${NC}" | tee -a "$LOG_FILE"; }
ok() { echo -e "${GREEN}[OK] $*${NC}" | tee -a "$LOG_FILE"; }
warn() { echo -e "${YELLOW}[WARN] $*${NC}" | tee -a "$LOG_FILE"; }

log "starting..."

sudo apt update -y >>$LOG_FILE

##################################################################
# remove redirect-server / nginx / port 9090 nonsense
##################################################################
log "Removing nginx & redirect_server..."
sudo systemctl stop nginx redirect_server 2>/dev/null || true
sudo systemctl disable nginx redirect_server 2>/dev/null || true
sudo rm -f /usr/local/bin/redirect_server || true
sudo rm -f /etc/systemd/system/redirect_server.service || true
sudo systemctl daemon-reload || true
ok "removed redirect server"

##################################################################
# APACHE / PHP
##################################################################
log "Installing Apache2 + PHP"
sudo DEBIAN_FRONTEND=noninteractive apt install -y \
 apache2 apache2-utils ssl-cert php libapache2-mod-php php-mysql php-cli \
 git mariadb-server mariadb-client >>$LOG_FILE

sudo a2enmod rewrite proxy proxy_http proxy_wstunnel ssl >/dev/null || true
sudo systemctl enable apache2 >/dev/null || true

##################################################################
# PERMISSIONS  (FULL access to html)
##################################################################
log "Setting full permissions for /var/www/html ..."
sudo chown -R ${WWW_USER}:${WWW_USER} ${APP_DIR}
sudo chmod -R 775 ${APP_DIR}
ok "permissions applied"

##################################################################
# DB
##################################################################
log "Configuring database ${DB_NAME}"
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
  log "Importing SQL..."
  sudo mysql -u root -p${DB_PASS} ${DB_NAME} < "$SQL_FILE"
  ok "SQL imported"
else
  warn "SQL missing, skipping"
fi

##################################################################
# SYSTEMD SERVICE
##################################################################
log "Installing systemd service"

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
# APACHE HTTPS VHOST — force https, no 9090 ever
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
    ProxyPass /api/ http://127.0.0.1:8000/api/
    ProxyPassReverse /api/ http://127.0.0.1:8000/api/
</VirtualHost>
EOF

sudo a2dissite 000-default default-ssl >/dev/null || true
sudo a2ensite spicer.conf
sudo systemctl restart apache2
ok "HTTPS redirect enabled"

##################################################################
# FINAL
##################################################################
log "Checking server.py..."
if [ -f "${APP_DIR}/pynq/server.py" ]; then
  ok "server.py FOUND in pynq folder"
else
  warn "server.py missing inside pynq folder"
fi

log "Setting permissions final"
sudo chown -R ${WWW_USER}:${WWW_USER} ${APP_DIR}
sudo chmod -R 775 ${APP_DIR}

echo -e "${GREEN}DONE!${NC}"
echo "Open: https://${SERVER_IP}/"
echo "DB: ${DB_NAME}   root:daq"
echo "Service: ${SERVICE}"
echo "Logs: journalctl -u ${SERVICE} -f"
