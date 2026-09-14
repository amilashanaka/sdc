<?php

class LogController extends BaseController
{
    private Log $log;

    public function __construct()
    {
        $this->checkAuth();
        $this->log = new Log();
    }

    public function index(): void
    {
        $id = (int) base64_decode((string) ($_GET['id'] ?? ''));
        $log = $id > 0 ? new Log($id) : new Log();
        $this->view('log', ['log' => $log]);
    }
}
