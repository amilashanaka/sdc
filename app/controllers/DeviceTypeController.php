<?php 

class DeviceTypeController extends BaseController
{
    private DeviceType $deviceType;

    public function __construct()
    {
        $this->checkAuth();
        $this->deviceType = new DeviceType();
    }

    public function index(): void
    {
        $id = (int) base64_decode((string) ($_GET['id'] ?? ''));
        $device_type = $id > 0 ? new DeviceType($id) : new DeviceType();
        $this->view('device_type', ['device_type' => $device_type]);
    }

    public function activate($id): void
    {
        $decodedId = (int) base64_decode((string) $id);
        if ($decodedId > 0) {
            $this->deviceType->update($decodedId, ['status' => 1]);
            $this->setFlash('success', 'Device type activated successfully');
        }
        $redirect = $this->get('redirect', 'device_type_list');
        $this->redirect($redirect);
    }

    public function deactivate($id): void
    {
        $decodedId = (int) base64_decode((string) $id);
        if ($decodedId > 0) {
            $this->deviceType->update($decodedId, ['status' => 0]);
            $this->setFlash('success', 'Device type deactivated successfully');
        }
        $redirect = $this->get('redirect', 'device_type_list');
        $this->redirect($redirect);
    }

    public function save(): void
    {
        if (!$this->isPost()) {
            $this->redirect('device_type_list');
        }

        $data = $this->post();
        $id = !empty($data['id']) ? (int) $data['id'] : 0;
        $name = trim((string) ($data['name'] ?? ''));

        if (empty($name)) {
            $this->setFlash('danger', 'Device type name is required.');
            $this->redirect($id > 0 ? 'device_type?id=' . base64_encode($id) : 'device_type');
        }

        $saveData = ['name' => $name];

        if ($id > 0) {
            $this->deviceType->update($id, $saveData);
            $this->setFlash('success', 'Device type updated successfully.');
        } else {
            $this->deviceType->insert($saveData);
            $this->setFlash('success', 'Device type created successfully.');
        }

        $this->redirect('device_type_list');
    }
}