<?php 

require_once 'Base.php';

/**
 * Single-row flag table holding the device operating mode.
 *
 * flags.run_mode is the source of truth that pynq/mode_manager.py watches:
 *   0 = RUN    -> ./startup  (main.py + udp.py, legacy TCP)
 *   1 = DEBUG  -> server.py  (FastAPI/WebSocket, web streaming)
 */
class Flag extends Base {
    protected $table = 'flags';

    /** The flags table has no timestamp columns. */
    protected $timestamps = false;

    const RUN = 0;
    const DEBUG = 1;

    /** @var string Path of the mode manager cache file, kept in sync for fallbacks. */
    private static function modeFile(): string
    {
        $directory = getenv('SPICER_PYNQ_DIR');
        if (!$directory) {
            $directory = is_dir('/var/www/html/pynq') ? '/var/www/html/pynq' : ROOT . '/pynq';
        }
        return rtrim($directory, '/') . '/.mode';
    }

    public function getRunMode(): int
    {
        $row = $this->find_by_id(1);
        return $row ? (int) $row->run_mode : self::RUN;
    }

    /**
     * Set the operating mode on the controlling row.
     *
     * @param int $mode self::RUN or self::DEBUG
     * @return bool
     */
    public function setRunMode(int $mode): bool
    {
        $mode = $mode === self::DEBUG ? self::DEBUG : self::RUN;

        try {
            $row = $this->find_by_id(1);
            if ($row) {
                $this->db->update('flags', ['run_mode' => $mode], 'id = 1');
            } else {
                $this->db->insert('flags', ['id' => 1, 'run_mode' => $mode]);
            }
        } catch (Throwable $exception) {
            return false;
        }

        $this->writeModeFile($mode);
        return true;
    }

    public function getModeName(): string
    {
        return $this->isDebugMode() ? 'DEBUG' : 'RUN';
    }

    public function isDebugMode(): bool
    {
        return $this->getRunMode() === self::DEBUG;
    }

    private function writeModeFile(int $mode): void
    {
        @file_put_contents(self::modeFile(), ($mode === self::DEBUG ? 'DEBUG' : 'RUN') . PHP_EOL);
    }
}
