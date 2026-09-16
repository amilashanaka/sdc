<?php 

 class DeviceTypeListController extends BaseController
 {
        public function __construct()
    {
        $this->checkAuth();
    }

    public function index(): void
    {
        $this->view('device_type_list');
    }
 }