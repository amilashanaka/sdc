<?php

class UserListController
{
    public function __construct()
    {
        $this->checkAuth();
    }

    public function index()
    {
        $user = new User();

        // Get all users (uses Base::all())
        $users = $user->all();

        // Load view
        $this->view('user_list', [
            'users' => $users,
            'title' => 'User List'
        ]);
    }

    /**
     * Auth protection (same logic as DashboardController)
     */
    private function checkAuth()
    {
        if (!isset($_SESSION['user_id'])) {
            header('Location: /login');
            exit;
        }
    }

    /**
     * View loader (same as DashboardController)
     */
    private function view($view, $data = [])
    {
        extract($data);
        require VIEWS . "/$view.php";
    }
}
