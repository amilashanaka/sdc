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

        foreach (['img1', 'img2', 'img3'] as $field) {
            $path = $this->uploadImage($field);
            if ($path !== null) {
                $data[$field] = $path;
            }
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

    private function uploadImage(string $field): ?string
    {
        if (empty($_FILES[$field]) || $_FILES[$field]['error'] === UPLOAD_ERR_NO_FILE) {
            return null;
        }

        if ($_FILES[$field]['error'] !== UPLOAD_ERR_OK || $_FILES[$field]['size'] > 5 * 1024 * 1024) {
            return null;
        }

        $extension = strtolower(pathinfo($_FILES[$field]['name'], PATHINFO_EXTENSION));
        if (!in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'webp'], true)) {
            return null;
        }

        $directory = ROOT . '/uploads/settings';
        if (!is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        $filename = bin2hex(random_bytes(8)) . '.' . $extension;
        $target = $directory . '/' . $filename;
        if (!move_uploaded_file($_FILES[$field]['tmp_name'], $target)) {
            return null;
        }

        return BASE_URL . '/uploads/settings/' . $filename;
    }
}