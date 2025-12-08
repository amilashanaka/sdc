<?php
class User {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function login($username, $password) {
        $sql = "SELECT * FROM users WHERE username = ? OR email = ?";
        $user = $this->db->fetch($sql, [$username, $username]);
        
        if ($user && password_verify($password, $user->password)) {
            return $user;
        }
        return false;
    }

    public function findById($id) {
        return $this->db->fetch("SELECT * FROM users WHERE id = ?", [$id]);
    }

    public function getAll() {
        return $this->db->fetchAll("SELECT * FROM users ORDER BY created_at DESC");
    }

    public function create($data) {
        $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        return $this->db->insert('users', $data);
    }

    public function update($id, $data) {
        return $this->db->update('users', $data, "id = $id");
    }

    public function delete($id) {
        return $this->db->delete('users', 'id = ?', [$id]);
    }
}