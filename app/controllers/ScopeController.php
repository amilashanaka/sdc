<?php

class ScopeController 
{
    public function __construct() 
    {
        $this->checkAuth();
    }

    public function index()
    {
        $this->view('scope');
    }

    private function checkAuth() 
    {
        if (!isset($_SESSION['user_id'])) {
            header('Location: ' . BASE_URL . '/login');
            exit;
        }
    }

    private function view($file, $data = []) 
    {
        extract($data);
        require VIEWS . "/{$file}.php";
    }
}