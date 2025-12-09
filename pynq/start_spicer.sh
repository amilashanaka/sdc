#!/bin/bash
# Start the Spicer DAQ WebSocket Server with proper environment for PYNQ

# Wait for system to be fully ready
sleep 5

# For PYNQ Linux, XRT is in /usr/lib/
export XILINX_XRT=/usr
export LD_LIBRARY_PATH=/usr/lib:$LD_LIBRARY_PATH

# Set the environment variables for the virtual environment
export PATH="/usr/local/share/pynq-venv/bin:$PATH"
export PYTHONPATH="/usr/local/share/pynq-venv/lib/python3.10/site-packages:$PYTHONPATH"

# Create symlinks for expected library names if needed
if [ ! -f "/usr/lib/libxrt_core.so.2" ] && [ -f "/usr/lib/libxrt_core.so.2.17.0" ]; then
    echo "Creating symlink for XRT library..."
    sudo ln -sf /usr/lib/libxrt_core.so.2.17.0 /usr/lib/libxrt_core.so.2
fi

# Check for FPGA devices
echo "Checking for FPGA devices..."
counter=0
max_wait=30
while [ $counter -lt $max_wait ]; do
    # Check for various device nodes
    if [ -e /dev/dri/renderD128 ] || [ -e /dev/xclmgmt* ] || [ -e /sys/class/xclmgmt ]; then
        echo "FPGA device found after $counter seconds"
        break
    fi
    sleep 1
    counter=$((counter + 1))
done

if [ $counter -eq $max_wait ]; then
    echo "Warning: FPGA device not found after $max_wait seconds"
    echo "Continuing anyway - server will attempt to start"
fi

# Change to the server directory
cd /var/www/html/pynq

# Debug: Print environment
echo "=== Environment ==="
echo "XILINX_XRT: $XILINX_XRT"
echo "LD_LIBRARY_PATH: $LD_LIBRARY_PATH"
echo "PATH: $PATH"
echo "PYTHONPATH: $PYTHONPATH"
echo "Python: $(which python)"
echo "Working dir: $(pwd)"

# Run the server
echo "Starting Spicer DAQ server..."
exec python server.py