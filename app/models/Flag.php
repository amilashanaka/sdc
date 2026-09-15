<?php 

require_once 'Base.php';

class Flag extends Base {
    protected $table = 'flags';

    public function getRunMode(): int
    {
        $row = $this->find_by_id(1);
        return $row ? (int)$row->run_mode : 0;
    }

    public function setRunMode(int $mode): bool
    {
        return $this->update_or_insert(['id' => 1], ['run_mode' => $mode]) !== false;
    }

    public function isDebugMode(): bool
    {
        return $this->getRunMode() === 1;
    }
}