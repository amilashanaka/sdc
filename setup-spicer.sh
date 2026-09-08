#!/bin/bash
echo "=== Setting up Spicer DAQ System with Debug Mode ==="

# 1. Install Python dependencies
echo "Installing Python packages..."
pip3 install fastapi uvicorn psutil

# 2. Make scripts executable
echo "Setting up scripts..."
chmod +x /var/www/html/pynq/mode_manager.sh
chmod +x /var/www/html/pynq/server.py
chmod +x /var/www/html/pynq/startup

# 3. Enable Apache modules
echo "Configuring Apache..."
sudo a2enmod proxy
sudo a2enmod proxy_http
sudo a2enmod proxy_wstunnel
sudo a2enmod rewrite

# 4. Configure sudo permissions for web server to manage modes
echo "Configuring sudo permissions..."
sudo tee /etc/sudoers.d/spicer-mode-manager > /dev/null << EOF
www-data ALL=(ALL) NOPASSWD: /var/www/html/pynq/mode_manager.sh
www-data ALL=(ALL) NOPASSWD: /bin/kill
www-data ALL=(ALL) NOPASSWD: /bin/pkill
EOF
sudo chmod 440 /etc/sudoers.d/spicer-mode-manager

# 5. Create systemd service (uses mode manager to start in RUN mode)
echo "Creating systemd service..."
sudo tee /etc/systemd/system/spicer-daq.service > /dev/null << EOF
[Unit]
Description=Spicer DAQ Mode Manager (RUN or DEBUG mode)
After=network.target apache2.service
Requires=apache2.service

[Service]
Type=simple
User=www-data
Group=www-data
WorkingDirectory=/var/www/html/pynq
ExecStart=/var/www/html/pynq/mode_manager.sh startup
Restart=always
RestartSec=10
StandardOutput=journal
StandardError=journal
Environment="PATH=/usr/local/sbin:/usr/local/bin:/usr/sbin:/usr/bin:/sbin:/bin"

[Install]
WantedBy=multi-user.target
EOF

# 6. Configure Apache
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
    
    # WebSocket proxying
    RewriteCond %{HTTP:Upgrade} websocket [NC]
    RewriteCond %{HTTP:Connection} upgrade [NC]
    RewriteRule ^/ws$ ws://127.0.0.1:8000/ws [P,L]
    
    ProxyPass /api/ http://127.0.0.1:8000/api/
    ProxyPassReverse /api/ http://127.0.0.1:8000/api/
</VirtualHost>
EOF

# 7. Enable the site
echo "Enabling Apache site..."
sudo a2ensite spicer.conf
sudo a2dissite 000-default.conf

# 8. Set permissions
echo "Setting permissions..."
sudo chown -R www-data:www-data /var/www/html
sudo chmod 755 /var/www/html/pynq/server.py
sudo chmod 755 /var/www/html/pynq/mode_manager.sh
sudo chmod 755 /var/www/html/pynq/startup
sudo chmod 755 /var/www/html/trigger_debug_mode.php

# 9. Create necessary log directories
echo "Creating log directories..."
sudo touch /var/log/spicer-mode.log
sudo touch /var/log/spicer-startup.log
sudo touch /var/log/spicer-server.log
sudo chown www-data:www-data /var/log/spicer-*.log
sudo chmod 666 /var/log/spicer-*.log

# 10. Start services
echo "Starting services..."
sudo systemctl daemon-reload
sudo systemctl enable spicer-daq.service
sudo systemctl start spicer-daq.service
sudo systemctl restart apache2

# 11. Check status
echo "=== Service Status ==="
sudo systemctl status spicer-daq.service --no-pager
echo -e "\n=== Listening Ports ==="
sudo netstat -tlnp | grep -E ":80|:8000" || echo "Ports not yet bound (service may be starting...)"

echo -e "\n=== Spicer DAQ with Debug Mode Setup Complete! ==="
echo "System starts in RUN MODE (TCP/startup)"
echo "When web portal is accessed at http://192.168.2.99/scope, it switches to DEBUG MODE"
echo "In DEBUG MODE: server.py and WebSocket work"
echo "In RUN MODE: ./startup runs for TCP connections"
echo ""
echo "To manually check/change mode:"
echo "  sudo /var/www/html/pynq/mode_manager.sh status"
echo "  sudo /var/www/html/pynq/mode_manager.sh debug"
echo "  sudo /var/www/html/pynq/mode_manager.sh run"