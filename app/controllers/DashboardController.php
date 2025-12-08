<?php
class DashboardController {
    public function __construct() {
        $this->checkAuth();
    }

    public function index() {
        $user = new User();
        $currentUser = $user->findById($_SESSION['user_id']);
        $users = $user->getAll();
        
        $this->view('dashboard', [
            'user' => $currentUser,
            'users' => $users,
            'title' => 'Dashboard'
        ]);
    }

    public function profile() {
        $user = new User();
        $currentUser = $user->findById($_SESSION['user_id']);
        
        $this->view('dashboard', [
            'user' => $currentUser,
            'title' => 'Profile'
        ]);
    }

    private function checkAuth() {
        if (!isset($_SESSION['user_id'])) {
            header('Location: ' . BASE_URL . '/login');
            exit;
        }
    }

    private function view($view, $data = []) {
        extract($data);
        require VIEWS . "/$view.php";
    }
}