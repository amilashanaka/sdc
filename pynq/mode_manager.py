#!/usr/bin/env python3
"""
Spicer DAQ mode manager.

Source of truth is the run_mode column of the flags table (single row, id=1):
    run_mode = 0 -> RUN mode   -> ./startup   (main.py + udp.py, legacy TCP)
    run_mode = 1 -> DEBUG mode -> server.py   (FastAPI/WebSocket, web streaming)

This process watches that column and keeps the running services in sync with
it. When the flag changes the other mode's process group is stopped and the
requested mode is started; no reboot is performed.

The .mode file is only a fallback cache used when the database cannot be read.
It is never allowed to override the database.
"""

from __future__ import annotations

import argparse
import logging
import os
import signal
import socket
import subprocess
import sys
import time
from pathlib import Path
from typing import Optional


BASE_DIR = Path(os.environ.get("SPICER_PYNQ_DIR", "/var/www/html/pynq"))
MODE_FILE = BASE_DIR / ".mode"
RUN_PID_FILE = BASE_DIR / ".run_pid"
DEBUG_PID_FILE = BASE_DIR / ".debug_pid"
LOG_FILE = Path(os.environ.get("SPICER_MODE_LOG", "/var/log/spicer-mode.log"))

DB_HOST = os.environ.get("SPICER_DB_HOST", "127.0.0.1")
DB_USER = os.environ.get("SPICER_DB_USER", "root")
DB_PASS = os.environ.get("SPICER_DB_PASS", "daq")
DB_NAME = os.environ.get("SPICER_DB_NAME", "daq")

POLL_SECONDS = float(os.environ.get("SPICER_MODE_POLL_SECONDS", "5"))
REBOOT_ON_MODE_CHANGE = os.environ.get("SPICER_REBOOT_ON_MODE_CHANGE", "0") != "0"
STARTUP_SETTLE_SECONDS = float(os.environ.get("SPICER_STARTUP_SETTLE_SECONDS", "2"))
STOP_GRACE_SECONDS = float(os.environ.get("SPICER_STOP_GRACE_SECONDS", "2"))
DB_WAIT_SECONDS = float(os.environ.get("SPICER_DB_WAIT_SECONDS", "60"))

# /run is tmpfs, so this marker exists only until the next reboot. Its absence
# means "fresh boot": the device must start in RUN mode no matter what the
# flag was when it was powered off. A crash/restart of this service keeps the
# marker, so an active web (DEBUG) session is not thrown back to RUN.
BOOT_MARKER = Path(os.environ.get("SPICER_BOOT_MARKER", "/run/spicer-mode-boot"))

RUN_MODE = "RUN"
DEBUG_MODE = "DEBUG"

FLAG_ROW_ID = 1
DEBUG_FLAG = 1
RUN_FLAG = 0

RUN_PROCESS_PATTERN = "python3? .*(main|udp)\\.py"
DEBUG_PROCESS_PATTERN = "python3? .*server\\.py"

PYTHON_BIN = os.environ.get("SPICER_PYTHON") or sys.executable or "/usr/bin/python3"
DEBUG_PORT = int(os.environ.get("SPICER_DEBUG_PORT", "8000"))

# SIGKILL only exists on POSIX; fall back so the module stays importable.
SIGKILL = getattr(signal, "SIGKILL", signal.SIGTERM)


def setup_logging() -> None:
    global LOG_FILE
    log_kwargs = {
        "level": logging.INFO,
        "format": "[%(asctime)s] %(message)s",
        "datefmt": "%Y-%m-%d %H:%M:%S",
    }
    try:
        LOG_FILE.parent.mkdir(parents=True, exist_ok=True)
        LOG_FILE.touch(exist_ok=True)
        logging.basicConfig(filename=str(LOG_FILE), **log_kwargs)
        return
    except OSError:
        LOG_FILE = BASE_DIR / "spicer-mode.log"
        try:
            LOG_FILE.touch(exist_ok=True)
            logging.basicConfig(filename=str(LOG_FILE), **log_kwargs)
            return
        except OSError:
            logging.basicConfig(stream=sys.stderr, **log_kwargs)


def mode_from_flag(flag: object) -> str:
    try:
        return DEBUG_MODE if int(flag) == DEBUG_FLAG else RUN_MODE
    except (TypeError, ValueError):
        return RUN_MODE


def flag_from_mode(mode: str) -> int:
    return DEBUG_FLAG if mode.upper() == DEBUG_MODE else RUN_FLAG


def normalize_mode(mode: object) -> str:
    return DEBUG_MODE if str(mode).strip().upper() == DEBUG_MODE else RUN_MODE


def mode_from_name(mode: str) -> str:
    return DEBUG_MODE if mode == DEBUG_MODE else RUN_MODE


def pid_file_for(mode: str) -> Path:
    return DEBUG_PID_FILE if mode == DEBUG_MODE else RUN_PID_FILE


def process_pattern_for(mode: str) -> str:
    return DEBUG_PROCESS_PATTERN if mode == DEBUG_MODE else RUN_PROCESS_PATTERN


# ---------------------------------------------------------------------------
# Database access
# ---------------------------------------------------------------------------


def run_mysql_cli(sql: str, fetch: bool = False) -> Optional[str]:
    env = os.environ.copy()
    env["MYSQL_PWD"] = DB_PASS
    cmd = ["mysql", "-u", DB_USER, "-h", DB_HOST, "-N", "-B", DB_NAME, "-e", sql]
    result = subprocess.run(
        cmd,
        env=env,
        text=True,
        stdout=subprocess.PIPE,
        stderr=subprocess.PIPE,
        check=False,
    )
    if result.returncode != 0:
        raise RuntimeError(result.stderr.strip() or "mysql command failed")
    return result.stdout.strip() if fetch else None


def db_execute(sql: str, fetch: bool = False) -> Optional[str]:
    try:
        import pymysql  # type: ignore
    except ImportError:
        return run_mysql_cli(sql, fetch=fetch)

    connection = pymysql.connect(
        host=DB_HOST,
        user=DB_USER,
        password=DB_PASS,
        database=DB_NAME,
        autocommit=True,
        connect_timeout=3,
        read_timeout=3,
        write_timeout=3,
    )
    try:
        with connection.cursor() as cursor:
            cursor.execute(sql)
            if fetch:
                row = cursor.fetchone()
                return "" if row is None else str(row[0])
        return None
    finally:
        connection.close()


def init_database() -> None:
    """Create the flags table and guarantee the single controlling row exists."""
    db_execute(
        "CREATE TABLE IF NOT EXISTS flags ("
        "id INT NOT NULL AUTO_INCREMENT, "
        "run_mode INT DEFAULT 0, "
        "PRIMARY KEY (id) USING BTREE"
        ")"
    )
    db_execute(
        "INSERT IGNORE INTO flags (id, run_mode) VALUES ({id}, {flag})".format(
            id=FLAG_ROW_ID, flag=RUN_FLAG
        )
    )


def read_db_flag() -> Optional[int]:
    """Return flags.run_mode for the controlling row, or None if unavailable."""
    try:
        value = db_execute(
            "SELECT run_mode FROM flags WHERE id={id} LIMIT 1".format(id=FLAG_ROW_ID),
            fetch=True,
        )
    except Exception as exc:
        logging.warning("Could not read flags.run_mode from database: %s", exc)
        return None

    if value is None or value == "":
        return None

    try:
        return int(value)
    except ValueError:
        logging.warning("Unexpected flags.run_mode value %r; treating as RUN", value)
        return RUN_FLAG


def write_db_flag(flag: int) -> bool:
    """Persist run_mode on the controlling row, creating it only if missing."""
    try:
        db_execute(
            "UPDATE flags SET run_mode={flag} WHERE id={id}".format(
                flag=flag, id=FLAG_ROW_ID
            )
        )
        db_execute(
            "INSERT IGNORE INTO flags (id, run_mode) VALUES ({id}, {flag})".format(
                id=FLAG_ROW_ID, flag=flag
            )
        )
    except Exception as exc:
        logging.warning("Could not write flags.run_mode to database: %s", exc)
        return False
    return True


def wait_for_database(timeout: float) -> bool:
    """MariaDB may still be starting at boot; retry until it answers."""
    deadline = time.time() + timeout
    while True:
        try:
            init_database()
            return True
        except Exception as exc:
            if time.time() >= deadline:
                logging.warning("Database not ready after %.0fs: %s", timeout, exc)
                return False
            time.sleep(2)


def apply_boot_reset() -> bool:
    """Force flags.run_mode back to RUN once per boot. True when done."""
    if not write_db_flag(RUN_FLAG):
        return False
    write_file_mode(RUN_MODE)
    try:
        BOOT_MARKER.touch()
    except OSError as exc:
        logging.warning("Could not create boot marker %s: %s", BOOT_MARKER, exc)
    logging.info("Fresh boot: flags.run_mode reset to RUN")
    return True


# ---------------------------------------------------------------------------
# Mode file fallback cache
# ---------------------------------------------------------------------------


def read_file_mode() -> str:
    try:
        return normalize_mode(MODE_FILE.read_text().strip())
    except OSError:
        return RUN_MODE


def write_file_mode(mode: str) -> None:
    try:
        MODE_FILE.write_text(mode + "\n")
    except OSError as exc:
        logging.warning("Could not update mode file %s: %s", MODE_FILE, exc)


# ---------------------------------------------------------------------------
# Mode resolution
# ---------------------------------------------------------------------------


def get_mode() -> str:
    """Database flag wins; the mode file is only used when the DB is unreadable."""
    flag = read_db_flag()
    if flag is None:
        logging.warning("Falling back to %s file for mode", MODE_FILE)
        return read_file_mode()
    return mode_from_flag(flag)


def set_mode(mode: str) -> str:
    """Record the requested mode in the flags table and refresh the cache file."""
    normalized = normalize_mode(mode)
    write_db_flag(flag_from_mode(normalized))
    write_file_mode(normalized)
    logging.info(
        "Mode flag set to %s (flags.run_mode=%s)", normalized, flag_from_mode(normalized)
    )
    return normalized


# ---------------------------------------------------------------------------
# Process control
# ---------------------------------------------------------------------------


def read_pid(pid_file: Path) -> Optional[int]:
    try:
        return int(pid_file.read_text().strip())
    except (OSError, ValueError):
        return None


def reap_children() -> None:
    """Reap exited children so their zombie PIDs are not mistaken for running."""
    while True:
        try:
            pid, _status = os.waitpid(-1, os.WNOHANG)
        except (ChildProcessError, OSError):
            return
        if pid == 0:
            return


def is_running(pid: Optional[int]) -> bool:
    if not pid or pid <= 0:
        return False
    try:
        os.kill(pid, 0)
    except OSError:
        return False
    return not is_zombie(pid)


def is_zombie(pid: int) -> bool:
    """An exited child that has not been waited for still answers signal 0."""
    try:
        stat = Path(f"/proc/{pid}/stat").read_text()
    except OSError:
        return False
    state = stat.rpartition(")")[2].strip().split(" ", 1)[0]
    return state == "Z"


def is_mode_running(mode: str) -> bool:
    reap_children()
    if is_running(read_pid(pid_file_for(mode))):
        return True
    return matching_pids(process_pattern_for(mode)) != []


def matching_pids(pattern: str) -> list:
    try:
        result = subprocess.run(
            ["pgrep", "-f", pattern],
            text=True,
            stdout=subprocess.PIPE,
            stderr=subprocess.DEVNULL,
            check=False,
        )
    except OSError:
        return []
    if result.returncode != 0:
        return []
    pids = []
    for line in result.stdout.split():
        try:
            pid = int(line)
        except ValueError:
            continue
        if pid != os.getpid():
            pids.append(pid)
    return pids


def signal_pid(pid: int, sig: int) -> None:
    """Signal the whole process group, falling back to the single process."""
    killpg = getattr(os, "killpg", None)
    if killpg is not None:
        try:
            killpg(pid, sig)
            return
        except OSError:
            pass
    try:
        os.kill(pid, sig)
    except OSError:
        pass


def stop_mode(mode: str) -> None:
    """Stop every process belonging to a mode, tracked or leftover."""
    label = mode_from_name(mode)
    pid_file = pid_file_for(label)
    pid = read_pid(pid_file)
    targets = set(matching_pids(process_pattern_for(label)))
    if pid and is_running(pid):
        targets.add(pid)

    if not targets:
        logging.info("No %s mode process to stop", label)
        pid_file.unlink(missing_ok=True)
        return

    logging.info("Stopping %s mode processes: %s", label, sorted(targets))

    # Signal the whole set first, then escalate once, so a mode switch is not
    # delayed by a sleep per process.
    for target in targets:
        signal_pid(target, signal.SIGTERM)
    time.sleep(STOP_GRACE_SECONDS)

    reap_children()
    survivors = [target for target in targets if is_running(target)]
    for target in survivors:
        signal_pid(target, SIGKILL)
    if survivors:
        time.sleep(STOP_GRACE_SECONDS)

    pid_file.unlink(missing_ok=True)


def port_in_use(port: int) -> bool:
    with socket.socket(socket.AF_INET, socket.SOCK_STREAM) as probe:
        probe.settimeout(1)
        return probe.connect_ex(("127.0.0.1", port)) == 0


def start_process(command: list, pid_file: Path, log_path: str, label: str) -> bool:
    try:
        log_handle = open(log_path, "ab")
    except OSError:
        fallback_log = BASE_DIR / Path(log_path).name
        try:
            log_handle = fallback_log.open("ab")
            logging.warning("Could not write %s; using %s", log_path, fallback_log)
        except OSError:
            log_handle = subprocess.DEVNULL
            logging.warning("Could not write process log %s; discarding output", log_path)

    try:
        process = subprocess.Popen(
            command,
            cwd=str(BASE_DIR),
            stdout=log_handle,
            stderr=subprocess.STDOUT,
            start_new_session=True,
        )
    except OSError as exc:
        logging.error("Failed to start %s mode: %s", label, exc)
        return False

    pid_file.write_text(str(process.pid) + "\n")
    logging.info("Started %s mode process (PID: %s)", label, process.pid)
    return True


def start_run_mode() -> bool:
    logging.info("Starting RUN mode (./startup -> main.py, udp.py)")
    return start_process(
        ["/bin/bash", "startup"], RUN_PID_FILE, "/var/log/spicer-startup.log", RUN_MODE
    )


def start_debug_mode() -> bool:
    logging.info("Starting DEBUG mode (server.py)")
    started = start_process(
        [PYTHON_BIN, "server.py"],
        DEBUG_PID_FILE,
        "/var/log/spicer-server.log",
        DEBUG_MODE,
    )
    if not started:
        return False

    if not wait_for_port(DEBUG_PORT, timeout=30):
        logging.warning("DEBUG mode server did not open port %s", DEBUG_PORT)
        return False
    return True


def wait_for_port(port: int, timeout: float) -> bool:
    deadline = time.time() + timeout
    while time.time() < deadline:
        if port_in_use(port):
            return True
        time.sleep(1)
    return False


# ---------------------------------------------------------------------------
# Applying a mode
# ---------------------------------------------------------------------------


def apply_mode(mode: str) -> None:
    """Make the running services match the requested mode."""
    target = mode_from_name(mode)
    other = DEBUG_MODE if target == RUN_MODE else RUN_MODE

    if target == RUN_MODE and is_running(read_pid(DEBUG_PID_FILE)):
        logging.info("DEBUG server still active while entering RUN mode")

    stop_mode(other)
    time.sleep(STARTUP_SETTLE_SECONDS)

    if is_mode_running(target):
        logging.info("%s mode already active", target)
        return

    if target == RUN_MODE and port_in_use(DEBUG_PORT):
        logging.warning("Port %s still bound after stopping DEBUG mode", DEBUG_PORT)

    if target == DEBUG_MODE:
        start_debug_mode()
    else:
        start_run_mode()


def switch_to_debug(reboot: bool = False) -> None:
    logging.info("Switching to DEBUG mode")
    set_mode(DEBUG_MODE)
    apply_mode(DEBUG_MODE)
    if reboot:
        reboot_system("DEBUG mode switch")


def switch_to_run(reboot: bool = False) -> None:
    logging.info("Switching to RUN mode")
    set_mode(RUN_MODE)
    apply_mode(RUN_MODE)
    if reboot:
        reboot_system("RUN mode switch")


def reconcile(mode: str) -> None:
    """Keep only the requested mode's services running."""
    target = mode_from_name(mode)
    other = DEBUG_MODE if target == RUN_MODE else RUN_MODE

    if is_mode_running(other):
        logging.info("Stopping %s mode: %s mode is active", other, target)
        stop_mode(other)
        time.sleep(STARTUP_SETTLE_SECONDS)

    if not is_mode_running(target):
        logging.warning("%s mode process is not active; restarting", target)
        pid_file_for(target).unlink(missing_ok=True)
        if target == DEBUG_MODE:
            start_debug_mode()
        else:
            start_run_mode()


def reboot_system(reason: str) -> None:
    if not REBOOT_ON_MODE_CHANGE:
        logging.info("Reboot skipped after %s because SPICER_REBOOT_ON_MODE_CHANGE=0", reason)
        return

    logging.info("Rebooting system after %s", reason)
    time.sleep(2)
    for command in (["/bin/systemctl", "reboot"], ["/sbin/reboot"], ["reboot"]):
        try:
            subprocess.Popen(command)
            return
        except OSError:
            continue
    logging.error("Unable to execute reboot command")


# ---------------------------------------------------------------------------
# Service lifecycle
# ---------------------------------------------------------------------------


def cleanup(signum: int, _frame: object) -> None:
    logging.info("Mode manager stopping after signal %s", signum)
    stop_mode(DEBUG_MODE)
    stop_mode(RUN_MODE)
    raise SystemExit(0)


def startup() -> None:
    signal.signal(signal.SIGTERM, cleanup)
    signal.signal(signal.SIGINT, cleanup)

    wait_for_database(DB_WAIT_SECONDS)

    # Fresh boot -> always RUN. If the DB is still unreachable the reset is
    # retried inside the loop below, and we stay in RUN until it succeeds.
    boot_reset_pending = not BOOT_MARKER.exists()
    if boot_reset_pending:
        write_file_mode(RUN_MODE)
        boot_reset_pending = not apply_boot_reset()

    current_mode = get_mode()
    write_file_mode(current_mode)
    logging.info("=== Mode Manager Starting in %s mode ===", current_mode)

    apply_mode(current_mode)

    while True:
        if boot_reset_pending:
            boot_reset_pending = not apply_boot_reset()

        flag = read_db_flag()
        if flag is None:
            # Database unreadable: keep serving the current mode rather than
            # acting on the cache file, and never write the flag back.
            logging.warning("flags.run_mode unavailable; staying in %s mode", current_mode)
            reconcile(current_mode)
            time.sleep(POLL_SECONDS)
            continue

        requested_mode = mode_from_flag(flag)
        if requested_mode != current_mode:
            logging.info("Mode flag changed: %s -> %s", current_mode, requested_mode)
            write_file_mode(requested_mode)
            apply_mode(requested_mode)
            current_mode = requested_mode
        else:
            reconcile(current_mode)

        time.sleep(POLL_SECONDS)


def status() -> None:
    flag = read_db_flag()
    print("flags.run_mode: {}".format("unavailable" if flag is None else flag))
    print("Current Mode: {}".format(get_mode()))
    for mode in (RUN_MODE, DEBUG_MODE):
        pid = read_pid(pid_file_for(mode))
        if pid:
            state = "running" if is_running(pid) else "stopped"
            print("{} Mode PID: {} ({})".format(mode, pid, state))
    if port_in_use(DEBUG_PORT):
        print("Port {}: listening".format(DEBUG_PORT))
    else:
        print("Port {}: closed".format(DEBUG_PORT))
    print("Log: {}".format(LOG_FILE))


def main() -> int:
    parser = argparse.ArgumentParser(description="Spicer DAQ mode manager")
    parser.add_argument(
        "command",
        nargs="?",
        default="startup",
        choices=("startup", "apply", "debug", "run", "get-mode", "set-mode", "status"),
    )
    parser.add_argument("mode", nargs="?")
    parser.add_argument(
        "--reboot",
        action="store_true",
        help="reboot the device after a manual mode switch",
    )
    args = parser.parse_args()

    setup_logging()

    if args.command == "startup":
        startup()
    elif args.command == "apply":
        apply_mode(get_mode())
    elif args.command == "debug":
        switch_to_debug(reboot=args.reboot)
    elif args.command == "run":
        switch_to_run(reboot=args.reboot)
    elif args.command == "get-mode":
        print(get_mode())
    elif args.command == "set-mode":
        if not args.mode:
            parser.error("set-mode requires RUN or DEBUG")
        set_mode(args.mode)
    elif args.command == "status":
        status()
    return 0


if __name__ == "__main__":
    sys.exit(main())
