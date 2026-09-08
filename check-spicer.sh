#!/bin/bash
echo "=== Spicer DAQ Status Check ==="
echo "Time: $(date)"
echo ""

# Check current mode
echo "1. Current Mode:"
MODE=$(cat /var/www/html/pynq/.mode 2>/dev/null || echo "UNKNOWN")
echo "   Mode: $MODE"

# Check systemd service
echo -e "\n2. Systemd Service (Mode Manager):"
sudo systemctl is-active spicer-daq.service

# Check Apache
echo -e "\n3. Apache Status:"
sudo systemctl is-active apache2.service

# Check ports
echo -e "\n4. Ports Listening:"
sudo netstat -tlnp | grep -E ":80|:8000" || echo "Ports not listening"

# Check processes
echo -e "\n5. Running Processes:"
echo "   Startup (RUN mode):"
if [ -f /var/www/html/pynq/.run_pid ]; then
    RUN_PID=$(cat /var/www/html/pynq/.run_pid)
    if ps -p $RUN_PID > /dev/null 2>&1; then
        echo "   ✓ Running (PID: $RUN_PID)"
    else
        echo "   ✗ PID file exists but process not running"
    fi
else
    echo "   Not active"
fi

echo "   Server.py (DEBUG mode):"
if [ -f /var/www/html/pynq/.debug_pid ]; then
    DEBUG_PID=$(cat /var/www/html/pynq/.debug_pid)
    if ps -p $DEBUG_PID > /dev/null 2>&1; then
        echo "   ✓ Running (PID: $DEBUG_PID)"
    else
        echo "   ✗ PID file exists but process not running"
    fi
else
    echo "   Not active"
fi

# Check WebSocket connection
echo -e "\n6. Testing WebSocket (timeout 2s):"
timeout 2 curl -i -H "Connection: Upgrade" -H "Upgrade: websocket" -H "Sec-WebSocket-Key: test" -H "Sec-WebSocket-Version: 13" http://127.0.0.1:8000/ws 2>/dev/null | head -5 || echo "   WebSocket not responding"

# Check logs
echo -e "\n7. Recent Log Files:"
echo "   Mode Manager Log (last 3 lines):"
sudo tail -3 /var/log/spicer-mode.log 2>/dev/null || echo "   No log entries"

echo -e "\n   Main Service Log (last 3 lines):"
sudo journalctl -u spicer-daq.service -n 3 --no-pager 2>/dev/null || echo "   No log entries"

echo ""
echo "Mode Manager Commands:"
echo "  sudo /var/www/html/pynq/mode_manager.sh debug    - Switch to DEBUG mode"
echo "  sudo /var/www/html/pynq/mode_manager.sh run      - Switch to RUN mode"
echo "  sudo /var/www/html/pynq/mode_manager.sh status   - Show detailed status"