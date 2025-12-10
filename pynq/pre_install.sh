#!/bin/bash
# Setup Helper - Fixes line endings and runs installation
# Run this first: bash setup.sh

echo "=== Fixing line ending issues ==="

# Fix install.sh line endings
if [ -f "install.sh" ]; then
    echo "Fixing install.sh..."
    sed -i 's/\r$//' install.sh
    chmod +x install.sh
    echo "✓ install.sh fixed"
else
    echo "✗ install.sh not found!"
    exit 1
fi

# Fix server.py if exists
if [ -f "server.py" ]; then
    echo "Fixing server.py..."
    sed -i 's/\r$//' server.py
    echo "✓ server.py fixed"
fi

# Fix service file if exists
if [ -f "spicer-daq.service" ]; then
    echo "Fixing spicer-daq.service..."
    sed -i 's/\r$//' spicer-daq.service
    echo "✓ spicer-daq.service fixed"
fi

echo ""
echo "=== All files fixed! ==="
echo ""
echo "Now running installation..."
echo ""

# Run the installation
sudo ./install.sh