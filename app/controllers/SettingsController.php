<?php

class SettingsController extends BaseController
{
    /** @var Setting */
    private $settings;

    public function __construct()
    {
        $this->checkAuth();
        $this->settings = new Setting(1);
    }

    public function index(): void
    {
        $this->view('settings', [
            'setting' => $this->settings,
            'title' => 'System Configuration',
        ]);
    }

    public function save(): void
    {
        if (!$this->isPost()) {
            $this->redirect('settings');
        }

        $data = [];
        foreach (['f1', 'f3', 'f4', 'f5'] as $field) {
            $data[$field] = trim((string) $this->post($field, ''));
        }

        $secret = trim((string) $this->post('f2', ''));
        if ($secret !== '') {
            $data['f2'] = $secret;
        }

        if ($this->settings->row) {
            $this->settings->update(1, $data);
        } else {
            $data['id'] = 1;
            $this->settings->insert($data);
        }

        $this->setFlash('success', 'Settings updated successfully.');
        $this->redirect('settings');
    }

}