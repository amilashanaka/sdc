<?php
class HealthController {
    public function __construct() {
        $this->checkAuth();
    }

    public function index() {
        $user = new User();
        $currentUser = $user->find_by_id($_SESSION['user_id']);

        $healthModel = new Health();
        $health_data = $healthModel->get_all();
        $health_items = [];
        foreach ($health_data as $h) {
            $is_active = ($h->status == 1);
            $health_items[] = [
                'name'        => $h->f1,
                'sequence'    => $h->f2,
                'description' => $h->f3,
                'value'       => $h->f4,
                'icon'        => $h->f5,
                'state'       => $is_active ? 'running' : 'idle',
                'label'       => $is_active ? 'OK' : 'Idle',
            ];
        }

        $this->view('health', [
            'user' => $currentUser,
            'title' => 'Health Monitor',
            'health_items' => $health_items,
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