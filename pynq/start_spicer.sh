#!/bin/bash
# Start the Spicer DAQ WebSocket Server with proper environment

# Wait for system to be fully ready
sleep 5

# Function to find XRT library
find_xrt_lib() {
    local lib_name="$1"
    local search_paths=(
        "/opt/xilinx/xrt/lib"
        "/usr/local/xrt/lib"
        "/usr/lib"
        "/usr/lib/x86_64-linux-gnu"
        "/usr/lib/aarch64-linux-gnu"
        "/usr/local/lib"
    )
    
    for path in "${search_paths[@]}"; do
        if [ -f "$path/$lib_name" ]; then
            echo "$path"
            return 0
        fi
    done
    
    # Try to find by pattern
    for path in "${search_paths[@]}"; do
        if ls "$path/$lib_name"* 1>/dev/null 2>&1; then
            echo "$path"
            return 0
        fi
    done
    
    return 1
}

# Source XRT environment
XRT_FOUND=0
if [ -f /opt/xilinx/xrt/setup.sh ]; then
    echo "Sourcing XRT setup from /opt/xilinx/xrt/setup.sh"
    source /opt/xilinx/xrt/setup.sh
    XRT_FOUND=1
elif [ -f /etc/profile.d/xrt.sh ]; then
    echo "Sourcing XRT profile from /etc/profile.d/xrt.sh"
    source /etc/profile.d/xrt.sh
    XRT_FOUND=1
elif [ -f /home/xilinx/.xrt_setup.sh ]; then
    echo "Sourcing XRT setup from /home/xilinx/.xrt_setup.sh"
    source /home/xilinx/.xrt_setup.sh
    XRT_FOUND=1
fi

# Find XRT library path
XRT_LIB_PATH=$(find_xrt_lib "libxrt_core.so")
if [ -n "$XRT_LIB_PATH" ]; then
    echo "Found XRT library at: $XRT_LIB_PATH"
    export LD_LIBRARY_PATH="$XRT_LIB_PATH:$LD_LIBRARY_PATH"
    # Set XILINX_XRT if not already set
    if [ -z "$XILINX_XRT" ]; then
        export XILINX_XRT=$(dirname "$XRT_LIB_PATH")/..
    fi
else
    echo "Warning: Could not find XRT libraries"
    # Check if we can find any XRT installation
    XRT_DIRS=$(find / -name "*xrt*" -type d 2>/dev/null | grep -E "(xrt|XRT)" | head -5)
    for dir in $XRT_DIRS; do
        if [ -d "$dir/lib" ]; then
            echo "Possible XRT at: $dir"
            export LD_LIBRARY_PATH="$dir/lib:$LD_LIBRARY_PATH"
            export XILINX_XRT="$dir"
            break
        fi
    done
fi

# Set the environment variables for the virtual environment
export PATH="/usr/local/share/pynq-venv/bin:$PATH"
export PYTHONPATH="/usr/local/share/pynq-venv/lib/python3.10/site-packages:$PYTHONPATH"

# Set XRT environment variables if not set
if [ -z "$XILINX_XRT" ]; then
    # Default location
    export XILINX_XRT="/opt/xilinx/xrt"
    echo "Using default XILINX_XRT: $XILINX_XRT"
fi

# Check if the specific library exists
if [ ! -f "$XILINX_XRT/lib/libxrt_core.so.2" ]; then
    echo "Checking for alternative libxrt_core versions..."
    # Look for any version
    for lib in "$XILINX_XRT/lib"/libxrt_core.so*; do
        if [ -f "$lib" ]; then
            echo "Found: $lib"
            # Create symbolic link if needed
            if [ ! -f "$XILINX_XRT/lib/libxrt_core.so.2" ]; then
                version=$(basename "$lib" | sed 's/libxrt_core.so.//')
                echo "Creating symlink from version $version to .2"
                ln -sf "$lib" "$XILINX_XRT/lib/libxrt_core.so.2"
            fi
            break
        fi
    done
fi

# Final LD_LIBRARY_PATH setup
export LD_LIBRARY_PATH="$XILINX_XRT/lib:$LD_LIBRARY_PATH"

# Wait for FPGA to initialize (up to 30 seconds)
echo "Checking for FPGA device..."
counter=0
while [ $counter -lt 30 ]; do
    if [ -e /dev/dri/renderD128 ] || [ -e /dev/xclmgmt* ]; then
        echo "FPGA device found after $counter seconds"
        break
    fi
    sleep 1
    counter=$((counter + 1))
done

if [ $counter -eq 30 ]; then
    echo "Warning: FPGA device not found after 30 seconds"
    echo "Server will start in simulation mode"
fi

# Change to the server directory
cd /var/www/html/pynq

# Debug: Print environment
echo "=== Environment ==="
echo "XILINX_XRT: $XILINX_XRT"
echo "LD_LIBRARY_PATH: $LD_LIBRARY_PATH"
echo "PATH: $PATH"
echo "PYTHONPATH: $PYTHONPATH"

# Run the server
echo "Starting Spicer DAQ server..."
exec python server.py