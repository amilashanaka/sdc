#!/usr/bin/env python3
"""
Spicer DAQ mode manager.

Watches daq.flags.run_mode forever:
  0 = RUN mode   -> ./startup
  1 = DEBUG mode -> /usr/bin/python3 server.py

The active process is kept alive, .mode is kept in sync, and the system is
rebooted after a real mode change so the correct service starts cleanly.
"""

from __future__ import annotations

import argparse
import logging
import os
import signal
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
REBOOT_ON_MODE_CHANGE = os.environ.get("SPICER_REBOOT_ON_MODE_CHANGE", "1") != "0"

RUN_MODE = "RUN"
DEBUG_MODE = "DEBUG"


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
        return DEBUG_MODE if int(flag) == 1 else RUN_MODE
    except (TypeError, ValueError):
        return RUN_MODE


def flag_from_mode(mode: str) -> int:
    return 1 if mode.upper() == DEBUG_MODE else 0


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
        with connection:
            with connection.cursor() as cursor:
                cursor.execute(sql)
                if fetch:
                    row = cursor.fetchone()
                    return "" if row is None else str(row[0])
        return None
    except ImportError:
        return run_mysql_cli(sql, fetch=fetch)


def init_database() -> None:
    create_sql = """
        CREATE TABLE IF NOT EXISTS flags (
            id INT NOT NULL AUTO_INCREMENT,
            run_mode INT DEFAULT 0,
            PRIMARY KEY (id) USING BTREE
        )
    """
    db_execute(create_sql)
    db_execute("INSERT IGNORE INTO flags (run_mode) VALUES (0)")


def read_db_mode() -> Optional[str]:
    try:
        value = db_execute("SELECT run_mode FROM flags WHERE id=1 LIMIT 1", fetch=True)
    except Exception as exc:
        logging.warning("Could not read flags.run_mode from database: %s", exc)
        return None

    if value == "":
        return None
    return mode_from_flag(value)


def write_db_mode(mode: str) -> None:
    flag = flag_from_mode(mode)
    try:
        db_execute(
            "INSERT INTO flags (run_mode) VALUES ({flag}) "
            "ON DUPLICATE KEY UPDATE run_mode={flag}".format(flag=flag)
        )
    except Exception as exc:
        logging.warning("Could not write flags.run_mode to database: %s", exc)


def read_file_mode() -> str:
    try:
        return DEBUG_MODE if MODE_FILE.read_text().strip().upper() == DEBUG_MODE else RUN_MODE
    except FileNotFoundError:
        return RUN_MODE


def write_file_mode(mode: str) -> None:
    MODE_FILE.write_text(mode + "\n")


def get_mode() -> str:
    return read_db_mode() or read_file_mode()


def set_mode(mode: str, update_db: bool = True) -> None:
    normalized = DEBUG_MODE if mode.upper() == DEBUG_MODE else RUN_MODE
    write_file_mode(normalized)
    if update_db:
        write_db_mode(normalized)
    logging.info("Mode set to %s (db flag: %s)", normalized, flag_from_mode(normalized))


def read_pid(pid_file: Path) -> Optional[int]:
    try:
        return int(pid_file.read_text().strip())
    except (FileNotFoundError, ValueError):
        return None


def is_running(pid: Optional[int]) -> bool:
    if not pid:
        return False
    try:
        os.kill(pid, 0)
        return True
    except OSError:
        return False


def stop_process(pid_file: Path, label: str) -> None:
    pid = read_pid(pid_file)
    if not pid:
        pid_file.unlink(missing_ok=True)
        return

    if is_running(pid):
        logging.info("Stopping %s mode process (PID: %s)", label, pid)
        for sig, delay in ((signal.SIGTERM, 2), (signal.SIGKILL, 0)):
            try:
                os.killpg(pid, sig)
            except OSError:
                try:
                    os.kill(pid, sig)
                except OSError:
                    pass
            if delay:
                time.sleep(delay)
            if not is_running(pid):
                break

    pid_file.unlink(missing_ok=True)


def stop_run_mode() -> None:
    stop_process(RUN_PID_FILE, RUN_MODE)


def stop_debug_mode() -> None:
    stop_process(DEBUG_PID_FILE, DEBUG_MODE)


def start_process(command: list[str], pid_file: Path, log_path: str, label: str) -> None:
    if is_running(read_pid(pid_file)):
        return

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

    process = subprocess.Popen(
        command,
        cwd=str(BASE_DIR),
        stdout=log_handle,
        stderr=subprocess.STDOUT,
        start_new_session=True,
    )
    pid_file.write_text(str(process.pid) + "\n")
    logging.info("Started %s mode process (PID: %s)", label, process.pid)


def start_run_mode() -> None:
    logging.info("Starting RUN mode (./startup)")
    start_process(["/bin/bash", "startup"], RUN_PID_FILE, "/var/log/spicer-startup.log", RUN_MODE)
    set_mode(RUN_MODE)


def start_debug_mode() -> None:
    logging.info("Starting DEBUG mode (server.py)")
    start_process(["/usr/bin/python3", "server.py"], DEBUG_PID_FILE, "/var/log/spicer-server.log", DEBUG_MODE)
    set_mode(DEBUG_MODE)


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


def switch_to_debug(reboot: bool = False) -> None:
    if read_file_mode() == DEBUG_MODE and is_running(read_pid(DEBUG_PID_FILE)):
        set_mode(DEBUG_MODE)
        logging.info("Already in DEBUG mode")
        return

    logging.info("Switching to DEBUG mode")
    stop_run_mode()
    time.sleep(1)
    start_debug_mode()
    if reboot:
        reboot_system("DEBUG mode switch")


def switch_to_run(reboot: bool = False) -> None:
    if read_file_mode() == RUN_MODE and is_running(read_pid(RUN_PID_FILE)):
        set_mode(RUN_MODE)
        logging.info("Already in RUN mode")
        return

    logging.info("Switching to RUN mode")
    stop_debug_mode()
    time.sleep(1)
    start_run_mode()
    if reboot:
        reboot_system("RUN mode switch")


def ensure_active_process(mode: str) -> None:
    if mode == DEBUG_MODE:
        if not is_running(read_pid(DEBUG_PID_FILE)):
            logging.warning("DEBUG mode process is not active; restarting")
            DEBUG_PID_FILE.unlink(missing_ok=True)
            start_debug_mode()
    else:
        if not is_running(read_pid(RUN_PID_FILE)):
            logging.warning("RUN mode process is not active; restarting")
            RUN_PID_FILE.unlink(missing_ok=True)
            start_run_mode()


def cleanup(signum: int, _frame: object) -> None:
    logging.info("Mode manager stopping after signal %s", signum)
    stop_debug_mode()
    stop_run_mode()
    raise SystemExit(0)


def startup() -> None:
    signal.signal(signal.SIGTERM, cleanup)
    signal.signal(signal.SIGINT, cleanup)

    try:
        init_database()
    except Exception as exc:
        logging.warning("Database initialization failed: %s", exc)

    current_mode = get_mode()
    set_mode(current_mode, update_db=read_db_mode() is None)
    logging.info("=== Mode Manager Starting in %s mode ===", current_mode)

    if current_mode == DEBUG_MODE:
        stop_run_mode()
        start_debug_mode()
    else:
        stop_debug_mode()
        start_run_mode()

    while True:
        requested_mode = get_mode()
        if requested_mode != current_mode:
            logging.info("Detected mode flag change: %s -> %s", current_mode, requested_mode)
            if requested_mode == DEBUG_MODE:
                switch_to_debug(reboot=True)
            else:
                switch_to_run(reboot=True)
            current_mode = requested_mode
        else:
            ensure_active_process(current_mode)

        time.sleep(POLL_SECONDS)


def status() -> None:
    mode = get_mode()
    print(f"Current Mode: {mode}")
    run_pid = read_pid(RUN_PID_FILE)
    debug_pid = read_pid(DEBUG_PID_FILE)
    if run_pid:
        print(f"RUN Mode PID: {run_pid} ({'running' if is_running(run_pid) else 'stopped'})")
    if debug_pid:
        print(f"DEBUG Mode PID: {debug_pid} ({'running' if is_running(debug_pid) else 'stopped'})")
    print(f"Log: {LOG_FILE}")


def main() -> int:
    parser = argparse.ArgumentParser(description="Spicer DAQ mode manager")
    parser.add_argument(
        "command",
        nargs="?",
        default="startup",
        choices=("startup", "debug", "run", "get-mode", "set-mode", "status"),
    )
    parser.add_argument("mode", nargs="?")
    args = parser.parse_args()

    setup_logging()

    if args.command == "startup":
        startup()
    elif args.command == "debug":
        switch_to_debug(reboot=True)
    elif args.command == "run":
        switch_to_run(reboot=True)
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
