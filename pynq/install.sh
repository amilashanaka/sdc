#!/bin/bash
set -e

echo "=== [1/20] Updating system ==="
sudo apt-get update

echo "=== [2/20] Installing Apache2 + SSL ==="
sudo apt-get install -y apache2 apache2-utils ssl-cert git

echo "=== [3/20] Enabling Apache modules ==="
sudo a2enmod rewrite
sudo a2enmod proxy
sudo a2enmod proxy_http
sudo a2enmod proxy_wstunnel
sudo a2enmod ssl

echo "=== [4/20] Preparing clean /var/www/html ==="
sudo rm -rf /var/www/html/*
sudo mkdir -p /var/www/html
sudo chown -R $USER:$USER /var/www/html

echo "=== [5/20] Cloning SDC repo (HTTPS) ==="
TMP_CLONE=/tmp/sdc_repo_clone
rm -rf $TMP_CLONE

git clone https://github.com/amilashanaka/sdc.git $TMP_CLONE

echo "=== Copying repo contents to /var/www/html ==="
cp -R $TMP_CLONE/* /var/www/html/
rm -rf $TMP_CLONE

sudo chown -R www-data:www-data /var/www/html

echo "=== [6/20] Creating FastAPI directory ==="
sudo mkdir -p /var/www/html/pynq
sudo mkdir -p /var/www/html/static
sudo chown -R www-data:www-data /var/www/html

echo "=== [7/20] Installing Python packages ==="
sudo apt-get install -y python3-pip python3-psutil python3-numpy python3-scipy python3-websockets python3-serial

pip3 install fastapi uvicorn starlette watchdog

echo "=== [8/20] Copying server.py into FastAPI folder ==="
cp /mnt/data/server.py /var/www/html/pynq/server.py
sudo chown www-data:www-data /var/www/html/pynq/server.py

echo "=== [9/20] Installing systemd service ==="
sudo cp /mnt/data/spicer-daq.service /etc/systemd/system/spicer-daq.service
sudo chmod 644 /etc/systemd/system/spicer-daq.service

echo "=== [10/20] Reloading systemd ==="
sudo systemctl daemon-reload

echo "=== [11/20] Enabling & starting spicer-daq ==="
sudo systemctl enable spicer-daq.service
sudo systemctl start spicer-daq.service

echo "=== [12/20] Creating Apache HTTPS virtual host ==="
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

echo "=== [13/20] Disabling default Apache site ==="
sudo a2dissite 000-default.conf
sudo a2dissite default-ssl 2>/dev/null || true

echo "=== [14/20] Enabling spicer.conf ==="
sudo a2ensite spicer.conf

echo "=== [15/20] Restarting Apache ==="
sudo systemctl restart apache2

echo "=== [16/20] Checking service status ==="
sudo systemctl status spicer-daq.service --no-pager || true

echo "=== [17/20] Setting final permissions ==="
sudo chown -R www-data:www-data /var/www/html

echo "=== [18/20] Flushing filesystem ==="
sync

echo "=== [19/20] Completed setup ==="

echo "=== [20/20] Rebooting PYNQ-Z1 ==="
sudo reboot
