<?php

class HealthController extends BaseController
{
    private Health $health;

    public function __construct()
    {
        $this->checkAuth();
        $this->health = new Health();
    }

    public function index(): void
    {
        // Check if detail view requested via ?id= parameter
        if (isset($_GET['id']) && !empty($_GET['id'])) {
            $this->detail();
            return;
        }

        $user = $this->getCurrentUser();

        $health_data = $this->health->get_all();
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

        $this->view('health_monitor', [
            'user' => $user,
            'title' => 'Health Monitor',
            'health_items' => $health_items,
        ]);
    }

    public function detail(): void
    {
        $id = (int) base64_decode((string) ($_GET['id'] ?? ''));
        $health = $id > 0 ? new Health($id) : new Health();
        $this->view('health', ['health' => $health]);
    }

    public function save(): void
    {
        if (!$this->isPost()) {
            $this->redirect('health_list');
        }

        $data = $this->post();
        $id = !empty($data['id']) ? (int) $data['id'] : 0;
        $name = trim((string) ($data['f1'] ?? ''));
        $seq = (int) ($data['f2'] ?? 0);
        $desc = trim((string) ($data['f3'] ?? ''));
        $value = trim((string) ($data['f4'] ?? ''));
        $icon = trim((string) ($data['f5'] ?? ''));

        if (empty($name)) {
            $this->setFlash('danger', 'Health parameter name is required.');
            $this->redirect($id > 0 ? 'health?id=' . base64_encode($id) : 'health');
        }

        $saveData = [
            'f1' => $name,
            'f2' => $seq,
            'f3' => $desc,
            'f4' => $value,
            'f5' => $icon,
        ];

        if ($id > 0) {
            $this->health->update($id, $saveData);
            $this->setFlash('success', 'Health parameter updated successfully.');
        } else {
            $this->health->insert($saveData);
            $this->setFlash('success', 'Health parameter created successfully.');
        }

        $this->redirect('health_list');
    }
}