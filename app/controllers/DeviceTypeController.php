<?php 

class DeviceTypeController extends BaseController
{

    public function __construct()
    {
        $this->checkAuth();
        $this->deviceType = new DeviceType();
    }

    public function index(): void
    {
        $id = (int) base64_decode((string) ($_GET['id'] ?? ''));
        $device_type = $id > 0 ? new DeviceType($id) : new Log();
        $this->view('device_type', ['device_type' => $device_type]);
    }
 
}