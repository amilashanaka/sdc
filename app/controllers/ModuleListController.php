<?php 

class ModuleListController extends BaseController
{
    public function __construct()
    {
        $this->checkAuth();
    }

    public function index(): void
    {
        $this->view('module_list');
    }
}