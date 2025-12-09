#!/bin/bash
# Source PYNQ environment
source /etc/profile.d/pynq_environment.sh
source /home/xilinx/.bashrc

# Activate the Python environment
export PYTHONPATH=/usr/local/lib/python3.8/dist-packages:$PYTHONPATH
export PATH=/usr/local/bin:$PATH

# Run the server
cd /var/www/html/pynq
exec python3 server.py