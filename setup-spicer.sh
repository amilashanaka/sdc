#!/bin/bash
echo "=== Setting up Spicer DAQ System ==="

# 1. Install Python dependencies
echo "Installing Python packages..."
pip3 install fastapi uvicorn psutil

# 2. Enable Apache modules
echo "Configuring Apache..."
sudo a2enmod proxy
sudo a2enmod proxy_http
sudo a2enmod proxy_wstunnel
sudo a2enmod rewrite

# 3. Create systemd service
echo "Creating systemd service..."
sudo tee /etc/systemd/system/spicer-daq.service > /dev/null << EOF
[Unit]
Description=Spicer DAQ WebSocket Server
After=network.target apache2.service
Requires=apache2.service

[Service]
Type=simple
User=www-data
Group=www-data
WorkingDirectory=/var/www/html/pynq
ExecStart=/usr/bin/python3 /var/www/html/pynq/server.py
Restart=always
RestartSec=10
StandardOutput=journal
StandardError=journal

[Install]
WantedBy=multi-user.target
EOF

# 4. Configure Apache
echo "Configuring Apache virtual host..."
sudo tee /etc/apache2/sites-available/spicer.conf > /dev/null << EOF
<VirtualHost *:80>
    DocumentRoot /var/www/html
    
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

# 5. Enable the site
echo "Enabling Apache site..."
sudo a2ensite spicer.conf
sudo a2dissite 000-default.conf

# 6. Set permissions
echo "Setting permissions..."
sudo chown -R www-data:www-data /var/www/html
sudo chmod 755 /var/www/html/pynq/server.py

# 7. Start services
echo "Starting services..."
sudo systemctl daemon-reload
sudo systemctl enable spicer-daq.service
sudo systemctl start spicer-daq.service
sudo systemctl restart apache2

# 8. Check status
echo "=== Service Status ==="
sudo systemctl status spicer-daq.service --no-pager
echo -e "\n=== Listening Ports ==="
sudo netstat -tlnp | grep -E ":80|:8000"

echo -e "\n=== Setup Complete! ==="
echo "Access your application at: http://192.168.2.99/scope"
echo "WebSocket will connect to: ws://192.168.2.99/ws"