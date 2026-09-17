<?php

class ModuleController extends BaseController
{
    private Module $module;

    public function __construct()
    {
        $this->checkAuth();
        $this->module = new Module();
    }

    public function index(): void
    {
        $id = (int) base64_decode((string) ($_GET['id'] ?? ''));
        $module = $id > 0 ? new Module($id) : new Module();
        $this->view('module', ['module' => $module]);
    }

    public function activate($id): void
    {
        $decodedId = (int) base64_decode((string) $id);
        if ($decodedId > 0) {
            $this->module->update($decodedId, ['status' => 1]);
            $this->setFlash('success', 'Module activated successfully');
        }
        $redirect = $this->get('redirect', 'module_list');
        $this->redirect($redirect);
    }

    public function deactivate($id): void
    {
        $decodedId = (int) base64_decode((string) $id);
        if ($decodedId > 0) {
            $this->module->update($decodedId, ['status' => 0]);
            $this->setFlash('success', 'Module deactivated successfully');
        }
        $redirect = $this->get('redirect', 'module_list');
        $this->redirect($redirect);
    }

    public function save(): void
    {
        if (!$this->isPost()) {
            $this->redirect('module_list');
        }

        $data = $this->post();
        $id = !empty($data['id']) ? (int) $data['id'] : 0;
        $name = trim((string) ($data['f1'] ?? ''));
        $seq = (int) ($data['f2'] ?? 0);
        $desc = trim((string) ($data['f3'] ?? ''));
        $baseAddr = trim((string) ($data['f5'] ?? ''));
        $icon = trim((string) ($data['f6'] ?? ''));

        if (empty($name)) {
            $this->setData('danger', 'Module name is required.');
            $this->redirect($id > 0 ? 'module?id=' . base64_encode($id) : 'module');
        }

        $saveData = [
            'f1' => $name,
            'f2' => $seq,
            'f3' => $desc,
            'f5' => $baseAddr,
            'f6' => $icon,
        ];

        if ($id > 0) {
            $this->module->update($id, $saveData);
            $this->setData('success', 'Module updated successfully.');
        } else {
            $this->module->insert($saveData);
            $this->setData('success', 'Module created successfully.');
        }

        $this->redirect('module_list');
    }
}