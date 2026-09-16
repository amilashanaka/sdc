<?php 

class HealthListController extends BaseController
{
    public function __construct()
    {
        $this->checkAuth();
    }

    public function index(): void
    {
        $this->view('health_list');
    }
}