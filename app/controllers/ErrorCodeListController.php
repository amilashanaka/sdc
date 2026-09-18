<?php

class ErrorCodeListController extends BaseController
{
    public function __construct()
    {
        $this->checkAuth();
    }

    public function index(): void
    {
        $this->view('error_code_list');
    }
}
