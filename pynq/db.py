"""
Name:    db.py
Version: 1.00
Date:    06-10-2026
By:      Don Gunasinha, Spicer Consulting
Note:    Centralised DAQ database access. All DB functions used by the DAQ
         subsystem (channels table, flags table, decimation) live here so
         daq.py and mode_manager.py share one connection path and one set of
         SPICER_DB_* defaults.

         Provisioning-time remote registration (MySQLDeviceRegistrar in
         db_manager.py) is intentionally kept separate: it uses different
         DEVICE_REG_DB_* env vars and inserts into the remote devices table.
"""

from __future__ import annotations

import os
import subprocess
from typing import Any, List, Optional


# ----------------------------------------------------------------------------
# Database settings (same variables and defaults as mode_manager.py / daq.py)
# ----------------------------------------------------------------------------
DB_HOST = os.environ.get("SPICER_DB_HOST", "127.0.0.1")
DB_USER = os.environ.get("SPICER_DB_USER", "root")
DB_PASS = os.environ.get("SPICER_DB_PASS", "daq")
DB_NAME = os.environ.get("SPICER_DB_NAME", "daq")


# SQL helpers used by the DAQ subsystem
CHANNEL_SQL = (
    "SELECT id, f1, f2, f3, f4 FROM channels WHERE status=1 ORDER BY f2, id"
)
DECIM_SQL = "SELECT f2, f3 FROM channels WHERE status=1 ORDER BY f2, id"


def _connect() -> Any:
    """Return a DB-API 2.0 connection using the first available driver.

    Preference order: PyMySQL -> mysql CLI fallback handled by callers.
    """
    try:
        import pymysql  # type: ignore
    except ImportError:
        pymysql = None

    if pymysql is not None:
        return pymysql.connect(
            host=DB_HOST,
            user=DB_USER,
            password=DB_PASS,
            database=DB_NAME,
            connect_timeout=3,
            read_timeout=3,
            write_timeout=3,
        )

    raise RuntimeError(
        "No MySQL driver available. Install PyMySQL or use the mysql CLI."
    )


def _mysql_cli(sql: str) -> str:
    """Run a query via the mysql command-line client as a fallback."""
    env = os.environ.copy()
    env["MYSQL_PWD"] = DB_PASS
    cmd = ["mysql", "-u", DB_USER, "-h", DB_HOST, "-N", "-B", DB_NAME, "-e", sql]
    result = subprocess.run(
        cmd,
        env=env,
        universal_newlines=True,
        stdout=subprocess.PIPE,
        stderr=subprocess.PIPE,
        check=False,
    )
    if result.returncode != 0:
        raise RuntimeError(result.stderr.strip() or "mysql command failed")
    return result.stdout


def db_query(sql: str) -> List[list]:
    """Run a SELECT and return rows as lists.

    Uses PyMySQL when installed, otherwise falls back to the mysql CLI.
    """
    try:
        connection = _connect()
    except RuntimeError:
        # No driver available -> try the CLI before giving up.
        text = _mysql_cli(sql)
        return [line.split("\t") for line in text.splitlines() if line.strip()]

    try:
        with connection.cursor() as cursor:
            cursor.execute(sql)
            return [list(row) for row in cursor.fetchall()]
    finally:
        connection.close()


def db_execute(sql: str, fetch: bool = False) -> Optional[str]:
    """Execute a statement (INSERT/UPDATE/CREATE) and optionally fetch a scalar.

    Returns the first column of the first row when ``fetch`` is True, else None.
    """
    try:
        connection = _connect()
    except RuntimeError:
        text = _mysql_cli(sql)
        return text.splitlines()[0] if (fetch and text) else None

    try:
        with connection.cursor() as cursor:
            cursor.execute(sql)
            if fetch:
                row = cursor.fetchone()
                return "" if row is None else str(row[0])
        connection.commit()
        return None
    finally:
        connection.close()


def db_query_retry(sql: str, wait_seconds: float) -> List[list]:
    """DB query that retries for ``wait_seconds`` (MariaDB may still start)."""
    import time

    deadline = time.time() + wait_seconds
    while True:
        try:
            return db_query(sql)
        except Exception:
            if time.time() >= deadline:
                raise
            time.sleep(1.0)


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
        "INSERT IGNORE INTO flags (id, run_mode) VALUES (1, 0)"
    )


def read_db_flag() -> Optional[int]:
    """Return flags.run_mode for the controlling row, or None if unavailable."""
    try:
        value = db_execute(
            "SELECT run_mode FROM flags WHERE id=1 LIMIT 1", fetch=True
        )
    except Exception:
        return None

    if value is None or value == "":
        return None

    try:
        return int(value)
    except ValueError:
        return 0


def write_db_flag(flag: int) -> bool:
    """Persist run_mode on the controlling row, creating it only if missing.

    The flag is an int (0 or 1) so it is safe to interpolate directly; both the
    PyMySQL path and the mysql-CLI fallback execute raw SQL.
    """
    flag = int(flag)
    try:
        db_execute("UPDATE flags SET run_mode=%d WHERE id=1" % flag)
        db_execute("INSERT IGNORE INTO flags (id, run_mode) VALUES (1, %d)" % flag)
    except Exception:
        return False
    return True