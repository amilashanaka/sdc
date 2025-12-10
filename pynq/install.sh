#!/bin/bash
set -e
SCRIPT_DIR=$(dirname "$0")
echo "=== [1/40] Updating system ==="
sudo apt-get update
echo "=== [1.5/40] Disabling default PYNQ services to free port 80 ==="
sudo systemctl stop jupyter || true
sudo systemctl disable jupyter || true
sudo systemctl stop redirect_server || true
sudo systemctl disable redirect_server || true
if [ -f /usr/local/bin/redirect_server ]; then
sudo mv /usr/local/bin/redirect_server /usr/local/bin/redirect_server.bak
fi
echo "=== [2/40] Installing Apache2 + SSL ==="
sudo apt-get install -y apache2 apache2-utils ssl-cert git
echo "=== [3/40] Installing PHP + MySQL driver ==="
sudo apt-get install -y php libapache2-mod-php php-mysql php-cli php-fpm
echo "=== [4/40] Enabling Apache modules ==="
sudo a2enmod rewrite
sudo a2enmod proxy
sudo a2enmod proxy_http
sudo a2enmod proxy_wstunnel
sudo a2enmod ssl
sudo a2enmod php*
echo "=== [5/40] Preparing clean /var/www/html ==="
sudo rm -rf /var/www/html/*
sudo mkdir -p /var/www/html
sudo chown -R $USER:$USER /var/www/html
echo "=== [6/40] Cloning SDC repo (HTTPS) ==="
TMP_CLONE=/tmp/sdc_repo_clone
rm -rf $TMP_CLONE
git clone https://github.com/amilashanaka/sdc.git $TMP_CLONE
echo "=== [7/40] Copying repo contents to /var/www/html ==="
cp -R $TMP_CLONE/* /var/www/html/
rm -rf $TMP_CLONE
sudo chown -R www-data:www-data /var/www/html
echo "=== [8/40] Creating FastAPI directory ==="
sudo mkdir -p /var/www/html/pynq
sudo mkdir -p /var/www/html/static
sudo chown -R www-data:www-data /var/www/html
echo "=== [9/40] Installing Python modules ==="
sudo apt-get install -y python3-pip python3-psutil python3-numpy python3-scipy python3-websockets python3-serial
pip3 install fastapi uvicorn starlette watchdog
echo "=== [10/40] Copying server.py from script directory ==="
cp "$SCRIPT_DIR/server.py" /var/www/html/pynq/server.py
sudo chown www-data:www-data /var/www/html/pynq/server.py
echo "=== [11/40] Installing MariaDB server ==="
sudo apt-get install -y mariadb-server mariadb-client
echo "=== [12/40] Starting MySQL ==="
sudo systemctl enable mariadb
sudo systemctl start mariadb
echo "=== [13/40] Setting MySQL root password ==="
sudo mysql -u root <<EOF
ALTER USER 'root'@'localhost' IDENTIFIED VIA mysql_native_password USING PASSWORD('daq');
ALTER USER 'root'@'localhost' IDENTIFIED BY 'daq';
FLUSH PRIVILEGES;
EOF
echo "=== [14/40] Creating database 'daq' if not exists ==="
sudo mysql -u root -pdaq -e "CREATE DATABASE IF NOT EXISTS daq;"
echo "=== [15/40] Checking for SQL file ==="
SQL_FILE="/var/www/html/db/table.sql"
if [ -f "$SQL_FILE" ]; then
echo "=== [16/40] Importing table.sql ==="
sudo mysql -u root -pdaq daq < "$SQL_FILE"
echo "=== Import completed successfully ==="
else
echo "!!! WARNING: table.sql NOT FOUND at $SQL_FILE"
echo "!!! LOGIN FEATURE WILL NOT WORK WITHOUT table.sql"
fi
echo "=== [17/40] Installing systemd service ==="
sudo cp "$SCRIPT_DIR/spicer-daq.service" /etc/systemd/system/spicer-daq.service
sudo chmod 644 /etc/systemd/system/spicer-daq.service
echo "=== [18/40] Reloading systemd ==="
sudo systemctl daemon-reload
echo "=== [19/40] Enabling & starting spicer-daq ==="
sudo systemctl enable spicer-daq.service
sudo systemctl start spicer-daq.service
echo "=== [20/40] Creating Apache HTTPS virtual host ==="
sudo tee /etc/apache2/sites-available/spicer.conf >/dev/null <<EOF
<VirtualHost *:80>
ServerName 0.0.0.0
Redirect "/" "https://%{HTTP_HOST}%{REQUEST_URI}"
</VirtualHost>
<VirtualHost *:443>
ServerName 0.0.0.0
DocumentRoot /var/www/html
SSLEngine on
SSLCertificateFile /etc/ssl/certs/ssl-cert-snakeoil.pem
SSLCertificateKeyFile /etc/ssl/private/ssl-cert-snakeoil.key
<Directory /var/www/html>
Options Indexes FollowSymLinks
AllowOverride All
Require all granted
</Directory>
RewriteEngine On
RewriteCond %{HTTP:Upgrade} websocket [NC]
RewriteCond %{HTTP:Connection} upgrade [NC]
RewriteRule ^/ws$ ws://127.0.0.1:8000/ws [P,L]
ProxyPass /api/ http://127.0.0.1:8000/api/
ProxyPassReverse /api/ http://127.0.0.1:8000/api/
</VirtualHost>
EOF
echo "=== [21/40] Disabling default Apache site ==="
sudo a2dissite 000-default.conf
sudo a2dissite default-ssl 2>/dev/null || true
echo "=== [22/40] Enabling spicer.conf ==="
sudo a2ensite spicer.conf
echo "=== [23/40] Restarting Apache ==="
sudo systemctl restart apache2
echo "=== [24/40] Checking spicer-daq service ==="
sudo systemctl status spicer-daq.service --no-pager || true
echo "=== [25/40] Fixing web directory permissions ==="
sudo chown -R www-data:www-data /var/www/html
echo "=== [26/40] Sync filesystem ==="
sync
echo "=== [27/40] INSTALL COMPLETE — REBOOTING ==="
sudo reboot