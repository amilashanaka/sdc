#!/bin/bash
echo "=== Spicer DAQ Status Check ==="
echo "Time: $(date)"
echo ""

# Check systemd service
echo "1. Systemd Service:"
sudo systemctl is-active spicer-daq.service

# Check Apache
echo -e "\n2. Apache Status:"
sudo systemctl is-active apache2.service

# Check ports
echo -e "\n3. Ports Listening:"
sudo netstat -tlnp | grep -E ":80|:8000"

# Check WebSocket connection
echo -e "\n4. Testing WebSocket (timeout 2s):"
timeout 2 curl -i -H "Connection: Upgrade" -H "Upgrade: websocket" -H "Sec-WebSocket-Key: test" -H "Sec-WebSocket-Version: 13" http://127.0.0.1:8000/ws 2>/dev/null | head -5

# Check logs
echo -e "\n5. Recent Logs (last 5 lines):"
sudo journalctl -u spicer-daq.service -n 5 --no-pager