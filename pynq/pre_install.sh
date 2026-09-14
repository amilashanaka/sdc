#!/bin/bash
# Recovery script - checks what's installed and completes installation

set -e
SCRIPT_DIR=$(dirname "$0")

# Colors
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m'

LOG_FILE="/tmp/pynq_recovery_$(date +%Y%m%d_%H%M%S).log"

print_step() {
    echo -e "${BLUE}=== $1 ===${NC}" | tee -a "$LOG_FILE"
}

print_success() {
    echo -e "${GREEN}✓ $1${NC}" | tee -a "$LOG_FILE"
}

print_error() {
    echo -e "${RED}✗ $1${NC}" | tee -a "$LOG_FILE"
}

print_warning() {
    echo -e "${YELLOW}⚠ $1${NC}" | tee -a "$LOG_FILE"
}

echo "==================================="
echo "  PYNQ Installation Recovery Tool  "
echo "==================================="
echo ""
echo "Checking current installation status..."
echo ""

# Check Apache
print_step "Checking Apache2"
if dpkg -l | grep -q "^ii.*apache2 "; then
    print_success "Apache2 is installed"
    APACHE_INSTALLED=true
else
    print_warning "Apache2 is NOT installed"
    APACHE_INSTALLED=false
fi

# Check PHP
print_step "Checking PHP"
if dpkg -l | grep -q "^ii.*php.*"; then
    print_success "PHP is installed"
    PHP_INSTALLED=true
else
    print_warning "PHP is NOT installed"
    PHP_INSTALLED=false
fi

# Check MariaDB
print_step "Checking MariaDB"
if dpkg -l | grep -q "^ii.*mariadb-server"; then
    print_success "MariaDB is installed"
    MARIADB_INSTALLED=true
else
    print_warning "MariaDB is NOT installed"
    MARIADB_INSTALLED=false
fi

# Check web files
print_step "Checking web files"
if [ -d "/var/www/html" ] && [ "$(ls -A /var/www/html 2>/dev/null)" ]; then
    print_success "Web files exist in /var/www/html"
    WEB_FILES=true
else
    print_warning "Web files missing"
    WEB_FILES=false
fi

echo ""
echo "==================================="
echo "Starting recovery installation..."
echo "==================================="
echo ""

# Install missing components
if [ "$APACHE_INSTALLED" = false ]; then
    print_step "Installing Apache2"
    sudo apt-get update
    sudo DEBIAN_FRONTEND=noninteractive apt-get install -y apache2 apache2-utils ssl-cert
    print_success "Apache2 installed"
fi

if [ "$PHP_INSTALLED" = false ]; then
    print_step "Installing PHP"
    sudo DEBIAN_FRONTEND=noninteractive apt-get install -y php libapache2-mod-php php-mysql php-cli php-fpm
    print_success "PHP installed"
fi

# Enable Apache modules
print_step "Enabling Apache modules"
sudo a2enmod rewrite proxy proxy_http proxy_wstunnel ssl 2>/dev/null || true
sudo a2enmod php* 2>/dev/null || true
print_success "Modules enabled"

# Stop conflicting services
print_step "Stopping conflicting services"
sudo systemctl stop jupyter 2>/dev/null || true
sudo systemctl disable jupyter 2>/dev/null || true
sudo systemctl stop redirect_server 2>/dev/null || true
sudo systemctl disable redirect_server 2>/dev/null || true
if [ -f /usr/local/bin/redirect_server ]; then
    sudo mv /usr/local/bin/redirect_server /usr/local/bin/redirect_server.bak 2>/dev/null || true
fi

# Install web files if missing
if [ "$WEB_FILES" = false ]; then
    print_step "Installing web files"
    sudo rm -rf /var/www/html/*
    sudo mkdir -p /var/www/html
    
    TMP_CLONE=/tmp/sdc_repo_clone_recovery
    rm -rf $TMP_CLONE
    git clone --depth 1 https://github.com/amilashanaka/sdc.git $TMP_CLONE
    sudo cp -R $TMP_CLONE/* /var/www/html/
    rm -rf $TMP_CLONE
    
    sudo mkdir -p /var/www/html/pynq
    sudo mkdir -p /var/www/html/static
    sudo chown -R www-data:www-data /var/www/html
    print_success "Web files installed"
fi

# Install Python packages
print_step "Installing Python packages"
sudo apt-get install -y python3-pip python3-psutil python3-numpy python3-scipy python3-websockets python3-serial

if [ -f /usr/local/share/pynq-venv/bin/pip ]; then
    sudo /usr/local/share/pynq-venv/bin/pip install fastapi uvicorn starlette watchdog 2>/dev/null || true
else
    sudo pip3 install fastapi uvicorn starlette watchdog 2>/dev/null || true
fi
print_success "Python packages installed"

# Copy server.py if it exists
if [ -f "$SCRIPT_DIR/server.py" ]; then
    print_step "Installing server.py"
    sudo cp "$SCRIPT_DIR/server.py" /var/www/html/pynq/server.py
    sudo chown www-data:www-data /var/www/html/pynq/server.py
    print_success "server.py installed"
fi

# Install MariaDB if needed
if [ "$MARIADB_INSTALLED" = false ]; then
    print_step "Installing MariaDB"
    sudo DEBIAN_FRONTEND=noninteractive apt-get install -y mariadb-server mariadb-client
    sudo systemctl enable mariadb
    sudo systemctl start mariadb
    sleep 3
    
    # Set root password
    sudo mysql -u root <<EOF 2>/dev/null || true
ALTER USER 'root'@'localhost' IDENTIFIED VIA mysql_native_password USING PASSWORD('daq');
ALTER USER 'root'@'localhost' IDENTIFIED BY 'daq';
FLUSH PRIVILEGES;
EOF
    print_success "MariaDB installed and configured"
fi

# Create database
print_step "Creating database"
sudo mysql -u root -pdaq -e "CREATE DATABASE IF NOT EXISTS daq;" 2>/dev/null || true

# Import SQL if exists
SQL_FILE="/var/www/html/db/table.sql"
if [ -f "$SQL_FILE" ]; then
    print_step "Importing database schema"
    sudo mysql -u root -pdaq daq < "$SQL_FILE" 2>/dev/null || true
    print_success "Database imported"
fi

# Install systemd service
if [ -f "$SCRIPT_DIR/spicer-daq.service" ]; then
    print_step "Installing systemd service"
    sudo cp "$SCRIPT_DIR/spicer-daq.service" /etc/systemd/system/spicer-daq.service
    sudo chmod 644 /etc/systemd/system/spicer-daq.service
    sudo systemctl daemon-reload
    print_success "Service installed"
fi

# Create Apache virtual host
print_step "Creating Apache virtual host"
sudo tee /etc/apache2/sites-available/spicer.conf >/dev/null <<'EOF'
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
    
    ErrorLog ${APACHE_LOG_DIR}/spicer_error.log
    CustomLog ${APACHE_LOG_DIR}/spicer_access.log combined
</VirtualHost>
EOF

# Configure Apache sites
sudo a2dissite 000-default.conf 2>/dev/null || true
sudo a2dissite default-ssl 2>/dev/null || true
sudo a2ensite spicer.conf
print_success "Virtual host configured"

# Fix permissions
print_step "Setting permissions"
sudo chown -R www-data:www-data /var/www/html
print_success "Permissions set"

# Start services
print_step "Starting Apache"
sudo systemctl enable apache2
sudo systemctl restart apache2
sleep 2

if systemctl is-active --quiet apache2; then
    print_success "Apache is running"
else
    print_error "Apache failed to start"
    sudo systemctl status apache2 --no-pager
fi

print_step "Starting MariaDB"
sudo systemctl enable mariadb
sudo systemctl restart mariadb
sleep 2

if systemctl is-active --quiet mariadb; then
    print_success "MariaDB is running"
else
    print_warning "MariaDB may not be running"
fi

# Start application service
if [ -f /etc/systemd/system/spicer-daq.service ]; then
    print_step "Starting spicer-daq service"
    sudo systemctl enable spicer-daq.service
    sudo systemctl restart spicer-daq.service
    sleep 3
    
    if systemctl is-active --quiet spicer-daq; then
        print_success "Spicer-DAQ is running"
    else
        print_warning "Spicer-DAQ may not be running - check logs: journalctl -u spicer-daq -n 50"
    fi
fi

# Final sync
sync

echo ""
echo "==================================="
echo "  Recovery Complete!               "
echo "==================================="
echo ""
echo "Service Status:"
systemctl is-active apache2 && echo "  ✓ Apache2: Running" || echo "  ✗ Apache2: Not running"
systemctl is-active mariadb && echo "  ✓ MariaDB: Running" || echo "  ✗ MariaDB: Not running"
systemctl is-active spicer-daq 2>/dev/null && echo "  ✓ Spicer-DAQ: Running" || echo "  ⚠ Spicer-DAQ: Not running"
echo ""
echo "Access your application at:"
echo "  https://$(hostname -I | awk '{print $1}')"
echo ""
echo "Log file: $LOG_FILE"
echo ""