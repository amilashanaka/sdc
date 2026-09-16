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

        // Get all POST data and filter to allowed fields
        $allowedFields = ['f1', 'f2', 'f3', 'f4', 'f5', 'f6', 'device_type'];
        $postData = $this->post();
        
        $data = [];
        foreach ($postData as $key => $value) {
            if (in_array($key, $allowedFields)) {
                $data[$key] = trim((string) $value);
            }
        }

        // Handle secret key separately - only update if provided
        if (empty($data['f2'])) {
            unset($data['f2']);
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