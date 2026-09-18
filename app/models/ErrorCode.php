<?php

require_once __DIR__ . '/Base.php';

class ErrorCode extends Base
{
    protected $table = 'error_codes';

    public function get_all(): array
    {
        return $this->db->fetchAll(
            "SELECT * FROM {$this->table} ORDER BY code ASC, id ASC"
        );
    }
}
