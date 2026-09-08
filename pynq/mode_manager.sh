#!/bin/bash
# Spicer DAQ Mode Manager
# Manages switching between RUN mode (TCP/startup) and DEBUG mode (web/server.py)

MODE_FILE="/var/www/html/pynq/.mode"
RUN_PID_FILE="/var/www/html/pynq/.run_pid"
DEBUG_PID_FILE="/var/www/html/pynq/.debug_pid"
LOG_FILE="/var/log/spicer-mode.log"

# Ensure log directory exists
mkdir -p /var/log
touch "$LOG_FILE"

log_message() {
    echo "[$(date '+%Y-%m-%d %H:%M:%S')] $1" >> "$LOG_FILE"
}

cleanup() {
    log_message "Mode manager stopping"
    stop_debug_mode
    stop_run_mode
    exit 0
}

trap cleanup SIGINT SIGTERM

# Initialize mode file to RUN mode by default
init_mode() {
    if [ ! -f "$MODE_FILE" ]; then
        echo "RUN" > "$MODE_FILE"
        log_message "Initialized mode file to RUN"
    fi
}

# Get current mode
get_mode() {
    if [ -f "$MODE_FILE" ]; then
        cat "$MODE_FILE"
    else
        echo "RUN"
    fi
}

# Set mode
set_mode() {
    local mode=$1
    echo "$mode" > "$MODE_FILE"
    log_message "Mode changed to: $mode"
}

# Stop run mode (TCP/startup)
stop_run_mode() {
    log_message "Stopping RUN mode..."
    if [ -f "$RUN_PID_FILE" ]; then
        local pid
        pid=$(cat "$RUN_PID_FILE")
        if kill -0 "$pid" 2>/dev/null; then
            kill "$pid" 2>/dev/null
            sleep 2
            kill -9 "$pid" 2>/dev/null || true
            log_message "Killed RUN mode process (PID: $pid)"
        fi
        rm -f "$RUN_PID_FILE"
    fi
}

# Start run mode (TCP/startup)
start_run_mode() {
    log_message "Starting RUN mode (./startup)..."
    cd /var/www/html/pynq || return 1
    nohup ./startup > /var/log/spicer-startup.log 2>&1 &
    local pid=$!
    echo "$pid" > "$RUN_PID_FILE"
    log_message "Started RUN mode process (PID: $pid)"
    set_mode "RUN"
}

# Stop debug mode (server.py/websocket)
stop_debug_mode() {
    log_message "Stopping DEBUG mode..."
    if [ -f "$DEBUG_PID_FILE" ]; then
        local pid
        pid=$(cat "$DEBUG_PID_FILE")
        if kill -0 "$pid" 2>/dev/null; then
            kill "$pid" 2>/dev/null
            sleep 2
            kill -9 "$pid" 2>/dev/null || true
            log_message "Killed DEBUG mode process (PID: $pid)"
        fi
        rm -f "$DEBUG_PID_FILE"
    fi
}

# Start debug mode (server.py/websocket)
start_debug_mode() {
    log_message "Starting DEBUG mode (server.py)..."
    cd /var/www/html/pynq || return 1
    nohup /usr/bin/python3 server.py > /var/log/spicer-server.log 2>&1 &
    local pid=$!
    echo "$pid" > "$DEBUG_PID_FILE"
    log_message "Started DEBUG mode process (PID: $pid)"
    set_mode "DEBUG"
}

# Switch from RUN to DEBUG mode
switch_to_debug() {
    local current_mode
    current_mode=$(get_mode)

    if [ "$current_mode" = "DEBUG" ]; then
        log_message "Already in DEBUG mode"
        return 0
    fi

    log_message "Switching to DEBUG mode..."
    stop_run_mode
    sleep 1
    start_debug_mode
    log_message "Successfully switched to DEBUG mode"
}

# Switch from DEBUG to RUN mode
switch_to_run() {
    local current_mode
    current_mode=$(get_mode)

    if [ "$current_mode" = "RUN" ]; then
        log_message "Already in RUN mode"
        return 0
    fi

    log_message "Switching to RUN mode..."
    stop_debug_mode
    sleep 1
    start_run_mode
    log_message "Successfully switched to RUN mode"
}

monitor_mode() {
    local current_mode
    current_mode=$(get_mode)

    while true; do
        local mode
        mode=$(get_mode)

        if [ "$mode" != "$current_mode" ]; then
            if [ "$mode" = "DEBUG" ]; then
                switch_to_debug
            else
                switch_to_run
            fi
            current_mode="$mode"
        fi

        if [ "$current_mode" = "RUN" ]; then
            if [ -f "$RUN_PID_FILE" ]; then
                local pid
                pid=$(cat "$RUN_PID_FILE")
                if ! kill -0 "$pid" 2>/dev/null; then
                    log_message "RUN mode process died unexpectedly"
                    rm -f "$RUN_PID_FILE"
                    start_run_mode
                fi
            else
                log_message "RUN mode not active, starting RUN mode"
                start_run_mode
            fi
        else
            if [ -f "$DEBUG_PID_FILE" ]; then
                local pid
                pid=$(cat "$DEBUG_PID_FILE")
                if ! kill -0 "$pid" 2>/dev/null; then
                    log_message "DEBUG mode process died unexpectedly"
                    rm -f "$DEBUG_PID_FILE"
                    start_debug_mode
                fi
            else
                log_message "DEBUG mode not active, starting DEBUG mode"
                start_debug_mode
            fi
        fi

        sleep 5
    done
}

# Main startup routine
startup() {
    init_mode
    log_message "=== Mode Manager Starting ==="

    local mode
    mode=$(get_mode)

    if [ "$mode" = "DEBUG" ]; then
        log_message "Resuming DEBUG mode"
        start_debug_mode
    else
        log_message "Starting in RUN mode"
        start_run_mode
    fi

    monitor_mode
}

# Main logic
case "${1:-startup}" in
    startup)
        startup
        ;;
    debug)
        switch_to_debug
        ;;
    run)
        switch_to_run
        ;;
    get-mode)
        get_mode
        ;;
    set-mode)
        set_mode "$2"
        ;;
    status)
        echo "Current Mode: $(get_mode)"
        if [ -f "$RUN_PID_FILE" ]; then
            echo "RUN Mode PID: $(cat "$RUN_PID_FILE")"
        fi
        if [ -f "$DEBUG_PID_FILE" ]; then
            echo "DEBUG Mode PID: $(cat "$DEBUG_PID_FILE")"
        fi
        echo "Log: $LOG_FILE"
        ;;
    *)
        echo "Usage: $0 {startup|debug|run|get-mode|set-mode <mode>|status}"
        exit 1
        ;;
esac
