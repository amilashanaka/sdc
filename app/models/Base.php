<?php

class Base {
    protected $db;
    protected $table;

    protected $timestamps = true;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function insert(array $data) {
        if ($this->timestamps) {
            $data['created_date'] = date('Y-m-d H:i:s');
        }
        return $this->db->insert($this->table, $data);
    }

    public function update($id, array $data) {
        if ($this->timestamps) {
            $data['updated_date'] = date('Y-m-d H:i:s');
        }
        return $this->db->update($this->table, $data, "id = $id");
    }

    public function find_by_id($id){
        return $this->db->fetch("SELECT * FROM {$this->table} WHERE id = ?", [$id]);
    }

    public function all(){
        return $this->db->fetchAll("SELECT * FROM {$this->table}");
    }

    public function whereFirst($col, $val){
        return $this->db->fetch("SELECT * FROM {$this->table} WHERE $col = ? LIMIT 1", [$val]);
    }

    public function delete($id){
        return $this->db->delete($this->table, 'id = ?', [$id]);
    }

    public function update_or_insert(array $where, array $data){
        $col = array_key_first($where);
        $val = $where[$col];

        if($ex = $this->whereFirst($col, $val)){
            return $this->update($ex->id, $data);
        }
        return $this->insert($data);
    }
}
