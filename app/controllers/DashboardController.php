<?php
class DashboardController {
    public function __construct() {
        $this->checkAuth();
    }

    public function index() {
        $user = new User();
        $currentUser = $user->find_by_id($_SESSION['user_id']);
        $users = $user->all();

        $moduleModel = new Module();
        $modules_data = $moduleModel->get_all();
        $fpga_modules = [];
        foreach ($modules_data as $idx => $m) {
            $is_active = ($m->status == 1);
            $fpga_modules[] = [
                'key'     => strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $m->f1)),
                'primary' => true,
                'name'    => $m->f1,
                'icon'    => $m->f6,
                'base'    => $m->f5,
                'irq'     => '-',
                'status'  => $is_active ? '0x00000001' : '0x00000000',
                'reset'   => 'De-asserted',
                'state'   => $is_active ? 'running' : 'idle',
                'label'   => $is_active ? 'Running' : 'Idle',
                'url'     => '',
            ];
        }

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
                'color'       => $h->f6,
                'state'       => $is_active ? 'running' : 'idle',
                'label'       => $is_active ? 'OK' : 'Idle',
            ];
        }

        $channelModel = new Channel();
        $channels_data = $channelModel->get_all();
        $channels = [];
        foreach ($channels_data as $c) {
            $channels[] = [
                'name'       => $c->f1,
                'sequence'   => $c->f2,
                'decimation' => $c->f3,
                'sample_rate'=> $c->f4,
                'status'     => $c->status,
            ];
        }

        $settingModel = new Setting();
        $settings = $settingModel->find_by_id(1);

        $this->view('dashboard', [
            'user' => $currentUser,
            'users' => $users,
            'title' => 'Dashboard',
            'fpga_modules' => $fpga_modules,
            'health_items' => $health_items,
            'channels' => $channels,
            'settings' => $settings,
        ]);
    }

    public function profile() {
        $user = new User();
        $currentUser = $user->find_by_id($_SESSION['user_id']);

        $moduleModel = new Module();
        $modules_data = $moduleModel->get_all();
        $fpga_modules = [];
        foreach ($modules_data as $idx => $m) {
            $is_active = ($m->status == 1);
            $fpga_modules[] = [
                'key'     => strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $m->f1)),
                'primary' => true,
                'name'    => $m->f1,
                'icon'    => $m->f6,
                'base'    => $m->f5,
                'irq'     => '-',
                'status'  => $is_active ? '0x00000001' : '0x00000000',
                'reset'   => 'De-asserted',
                'state'   => $is_active ? 'running' : 'idle',
                'label'   => $is_active ? 'Running' : 'Idle',
                'url'     => '',
            ];
        }

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
                'color'       => $h->f6,
                'state'       => $is_active ? 'running' : 'idle',
                'label'       => $is_active ? 'OK' : 'Idle',
            ];
        }

        $channelModel = new Channel();
        $channels_data = $channelModel->get_all();
        $channels = [];
        foreach ($channels_data as $c) {
            $channels[] = [
                'name'       => $c->f1,
                'sequence'   => $c->f2,
                'decimation' => $c->f3,
                'sample_rate'=> $c->f4,
                'status'     => $c->status,
            ];
        }

        $settingModel = new Setting();
        $settings = $settingModel->find_by_id(1);

        $this->view('dashboard', [
            'user' => $currentUser,
            'title' => 'Profile',
            'fpga_modules' => $fpga_modules,
            'health_items' => $health_items,
            'channels' => $channels,
            'settings' => $settings,
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