<?php

require_once __DIR__ . '/Base.php';

class Log extends Base
{
    protected $table = 'logs';

    public function get_all($status = 1): array
    {
        $statusCol = 'status';
        return $this->db->fetchAll("SELECT * FROM {$this->table} WHERE {$statusCol} = ? ORDER BY created_date DESC", [$status]);
    }

    public function get_by_id($id)
    {
        $row = $this->db->fetch(
            "SELECT l.*, m.f1 AS module_name
             FROM {$this->table} AS l
             LEFT JOIN modules AS m ON m.id = l.module
             WHERE l.id = ?",
            [$id]
        );
        $this->row = $row;

        return $row;
    }

    public function archive($id): bool
    {
        return $this->update($id, ['status' => 0]);
    }

    public function unarchive($id): bool
    {
        return $this->update($id, ['status' => 1]);
    }
}
