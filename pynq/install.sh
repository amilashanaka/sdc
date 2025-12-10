#!/bin/bash

# Improved PYNQ Installation Script with Error Handling
# =====================================================

set -e  # Exit on error
set -o pipefail  # Exit on pipe failure

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Script directory
SCRIPT_DIR=$(dirname "$0")

# Log file
LOG_FILE="/tmp/pynq_install_$(date +%Y%m%d_%H%M%S).log"

# Function to print colored messages
print_step() {
    echo -e "${BLUE}=== [$1] $2 ===${NC}" | tee -a "$LOG_FILE"
}

print_success() {
    echo -e "${GREEN}✓ $1${NC}" | tee -a "$LOG_FILE"
}

print_error() {
    echo -e "${RED}✗ ERROR: $1${NC}" | tee -a "$LOG_FILE"
}

print_warning() {
    echo -e "${YELLOW}⚠ WARNING: $1${NC}" | tee -a "$LOG_FILE"
}

# Function to check if command succeeded
check_status() {
    if [ $? -eq 0 ]; then
        print_success "$1"
    else
        print_error "$1 failed"
        exit 1
    fi
}

# Function to wait for service
wait_for_service() {
    local service=$1
    local max_wait=30
    local count=0
    
    while [ $count -lt $max_wait ]; do
        if systemctl is-active --quiet $service; then
            print_success "$service is active"
            return 0
        fi
        sleep 1
        count=$((count + 1))
    done
    
    print_warning "$service did not start within ${max_wait}s"
    return 1
}

# Start installation
echo -e "${BLUE}╔════════════════════════════════════════════╗${NC}"
echo -e "${BLUE}║  PYNQ DAQ System Installation Script      ║${NC}"
echo -e "${BLUE}╚════════════════════════════════════════════╝${NC}"
echo ""
echo "Installation log: $LOG_FILE"
echo ""

# Step 1: Update system
print_step "1/27" "Updating package lists"
sudo apt-get update -qq 2>&1 | tee -a "$LOG_FILE"
check_status "Package list update"

# Step 2: Stop conflicting services
print_step "2/27" "Stopping conflicting PYNQ services"
sudo systemctl stop jupyter 2>/dev/null || print_warning "Jupyter not running"
sudo systemctl disable jupyter 2>/dev/null || print_warning "Jupyter not installed"
sudo systemctl stop redirect_server 2>/dev/null || print_warning "Redirect server not running"
sudo systemctl disable redirect_server 2>/dev/null || print_warning "Redirect server not installed"

if [ -f /usr/local/bin/redirect_server ]; then
    sudo mv /usr/local/bin/redirect_server /usr/local/bin/redirect_server.bak
    print_success "Backed up redirect_server"
fi

# Step 3: Install Apache2
print_step "3/27" "Installing Apache2 and SSL"
sudo DEBIAN_FRONTEND=noninteractive apt-get install -y apache2 apache2-utils ssl-cert git 2>&1 | tee -a "$LOG_FILE"
check_status "Apache2 installation"

# Step 4: Install PHP
print_step "4/27" "Installing PHP and MySQL driver"
sudo DEBIAN_FRONTEND=noninteractive apt-get install -y php libapache2-mod-php php-mysql php-cli php-fpm 2>&1 | tee -a "$LOG_FILE"
check_status "PHP installation"

# Step 5: Enable Apache modules
print_step "5/27" "Enabling Apache modules"
sudo a2enmod rewrite proxy proxy_http proxy_wstunnel ssl 2>&1 | tee -a "$LOG_FILE"
# Enable PHP module (version-agnostic)
sudo a2enmod php* 2>&1 | tee -a "$LOG_FILE" || print_warning "PHP module enable had warnings"
check_status "Apache modules enabled"

# Step 6: Prepare web directory
print_step "6/27" "Preparing /var/www/html directory"
sudo rm -rf /var/www/html/*
sudo mkdir -p /var/www/html
sudo chown -R $USER:$USER /var/www/html
check_status "Web directory prepared"

# Step 7: Clone repository
print_step "7/27" "Cloning SDC repository"
TMP_CLONE=/tmp/sdc_repo_clone
rm -rf $TMP_CLONE
git clone --depth 1 https://github.com/amilashanaka/sdc.git $TMP_CLONE 2>&1 | tee -a "$LOG_FILE"
check_status "Repository cloned"

# Step 8: Copy files
print_step "8/27" "Copying files to web directory"
cp -R $TMP_CLONE/* /var/www/html/
rm -rf $TMP_CLONE
sudo chown -R www-data:www-data /var/www/html
check_status "Files copied"

# Step 9: Create directories
print_step "9/27" "Creating application directories"
sudo mkdir -p /var/www/html/pynq
sudo mkdir -p /var/www/html/static
sudo chown -R www-data:www-data /var/www/html
check_status "Directories created"

# Step 10: Install Python packages
print_step "10/27" "Installing Python system packages"
sudo DEBIAN_FRONTEND=noninteractive apt-get install -y python3-pip python3-psutil python3-numpy python3-scipy python3-websockets python3-serial 2>&1 | tee -a "$LOG_FILE"
check_status "Python system packages installed"

print_step "11/27" "Installing Python application packages"
if [ -f /usr/local/share/pynq-venv/bin/pip ]; then
    sudo /usr/local/share/pynq-venv/bin/pip install fastapi uvicorn starlette watchdog 2>&1 | tee -a "$LOG_FILE"
else
    sudo pip3 install fastapi uvicorn starlette watchdog 2>&1 | tee -a "$LOG_FILE"
fi
check_status "Python packages installed"

# Step 12: Copy server.py
print_step "12/27" "Installing server application"
if [ -f "$SCRIPT_DIR/server.py" ]; then
    cp "$SCRIPT_DIR/server.py" /var/www/html/pynq/server.py
    sudo chown www-data:www-data /var/www/html/pynq/server.py
    print_success "server.py copied"
else
    print_error "server.py not found in $SCRIPT_DIR"
    exit 1
fi

# Step 13: Install MariaDB
print_step "13/27" "Installing MariaDB server"
sudo DEBIAN_FRONTEND=noninteractive apt-get install -y mariadb-server mariadb-client 2>&1 | tee -a "$LOG_FILE"
check_status "MariaDB installed"

# Step 14: Start MariaDB
print_step "14/27" "Starting MariaDB service"
sudo systemctl enable mariadb 2>&1 | tee -a "$LOG_FILE"
sudo systemctl start mariadb 2>&1 | tee -a "$LOG_FILE"
wait_for_service mariadb
check_status "MariaDB started"

# Step 15: Configure MySQL root password
print_step "15/27" "Configuring MySQL root password"
sudo mysql -u root <<EOF 2>&1 | tee -a "$LOG_FILE"
ALTER USER 'root'@'localhost' IDENTIFIED VIA mysql_native_password USING PASSWORD('daq');
ALTER USER 'root'@'localhost' IDENTIFIED BY 'daq';
FLUSH PRIVILEGES;
EOF
check_status "MySQL password set"

# Step 16: Create database
print_step "16/27" "Creating database 'daq'"
sudo mysql -u root -pdaq -e "CREATE DATABASE IF NOT EXISTS daq;" 2>&1 | tee -a "$LOG_FILE"
check_status "Database created"

# Step 17: Import SQL file
print_step "17/27" "Importing database schema"
SQL_FILE="/var/www/html/db/table.sql"
if [ -f "$SQL_FILE" ]; then
    sudo mysql -u root -pdaq daq < "$SQL_FILE" 2>&1 | tee -a "$LOG_FILE"
    check_status "Database schema imported"
else
    print_warning "table.sql not found at $SQL_FILE"
    print_warning "Login feature may not work without database schema"
fi

# Step 18: Install systemd service
print_step "18/27" "Installing systemd service"
if [ -f "$SCRIPT_DIR/spicer-daq.service" ]; then
    sudo cp "$SCRIPT_DIR/spicer-daq.service" /etc/systemd/system/spicer-daq.service
    sudo chmod 644 /etc/systemd/system/spicer-daq.service
    check_status "Service file installed"
else
    print_error "spicer-daq.service not found in $SCRIPT_DIR"
    exit 1
fi

# Step 19: Reload systemd
print_step "19/27" "Reloading systemd daemon"
sudo systemctl daemon-reload 2>&1 | tee -a "$LOG_FILE"
check_status "Systemd reloaded"

# Step 20: Create Apache virtual host
print_step "20/27" "Creating Apache HTTPS virtual host"
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
    
    # WebSocket support
    RewriteEngine On
    RewriteCond %{HTTP:Upgrade} websocket [NC]
    RewriteCond %{HTTP:Connection} upgrade [NC]
    RewriteRule ^/ws$ ws://127.0.0.1:8000/ws [P,L]
    
    # API proxy
    ProxyPass /api/ http://127.0.0.1:8000/api/
    ProxyPassReverse /api/ http://127.0.0.1:8000/api/
    
    ErrorLog \${APACHE_LOG_DIR}/spicer_error.log
    CustomLog \${APACHE_LOG_DIR}/spicer_access.log combined
</VirtualHost>
EOF
check_status "Virtual host created"

# Step 21: Configure Apache sites
print_step "21/27" "Configuring Apache sites"
sudo a2dissite 000-default.conf 2>&1 | tee -a "$LOG_FILE" || true
sudo a2dissite default-ssl 2>&1 | tee -a "$LOG_FILE" || true
sudo a2ensite spicer.conf 2>&1 | tee -a "$LOG_FILE"
check_status "Apache sites configured"

# Step 22: Test Apache configuration
print_step "22/27" "Testing Apache configuration"
sudo apache2ctl configtest 2>&1 | tee -a "$LOG_FILE"
if [ $? -eq 0 ] || grep -q "Syntax OK" "$LOG_FILE"; then
    print_success "Apache configuration valid"
else
    print_warning "Apache configuration test had warnings"
fi

# Step 23: Restart Apache
print_step "23/27" "Restarting Apache service"
sudo systemctl restart apache2 2>&1 | tee -a "$LOG_FILE"
wait_for_service apache2
check_status "Apache restarted"

# Step 24: Start application service
print_step "24/27" "Starting spicer-daq service"
sudo systemctl enable spicer-daq.service 2>&1 | tee -a "$LOG_FILE"
sudo systemctl start spicer-daq.service 2>&1 | tee -a "$LOG_FILE"
sleep 3
check_status "Service enabled and started"

# Step 25: Verify services
print_step "25/27" "Verifying all services"
echo ""
echo "Service Status:"
echo "---------------"

if systemctl is-active --quiet apache2; then
    print_success "Apache2: Running"
else
    print_error "Apache2: Not running"
fi

if systemctl is-active --quiet mariadb; then
    print_success "MariaDB: Running"
else
    print_error "MariaDB: Not running"
fi

if systemctl is-active --quiet spicer-daq; then
    print_success "Spicer-DAQ: Running"
else
    print_warning "Spicer-DAQ: Not running (check logs: journalctl -u spicer-daq -n 50)"
fi

# Step 26: Final permissions
print_step "26/27" "Setting final permissions"
sudo chown -R www-data:www-data /var/www/html
check_status "Permissions set"

# Step 27: Sync filesystem
print_step "27/27" "Syncing filesystem"
sync
check_status "Filesystem synced"

# Installation complete
echo ""
echo -e "${GREEN}╔════════════════════════════════════════════╗${NC}"
echo -e "${GREEN}║     Installation Completed Successfully   ║${NC}"
echo -e "${GREEN}╚════════════════════════════════════════════╝${NC}"
echo ""
echo "Installation Summary:"
echo "--------------------"
echo "• Web Interface: https://$(hostname -I | awk '{print $1}')"
echo "• Database: daq (user: root, pass: daq)"
echo "• Log File: $LOG_FILE"
echo ""
echo "To check service status:"
echo "  sudo systemctl status spicer-daq"
echo "  sudo systemctl status apache2"
echo "  sudo systemctl status mariadb"
echo ""
echo "To view application logs:"
echo "  sudo journalctl -u spicer-daq -f"
echo ""

# Ask about reboot
read -p "Do you want to reboot now? (recommended) [y/N]: " -n 1 -r
echo
if [[ $REPLY =~ ^[Yy]$ ]]; then
    print_step "FINAL" "Rebooting system in 5 seconds..."
    sleep 5
    sudo reboot
else
    print_warning "Reboot skipped. Please reboot manually for all changes to take effect."
    echo "Run: sudo reboot"
fi

exit 0