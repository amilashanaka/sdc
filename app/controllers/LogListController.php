<?php

class LogListController extends BaseController
{
    public function __construct()
    {
        $this->checkAuth();
    }

    public function index(): void
    {
        $this->view('log_list');
    }
}
