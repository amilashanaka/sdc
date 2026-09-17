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

    public function archive($id): void
    {
        $decodedId = (int) base64_decode((string) $id);
        if ($decodedId > 0) {
            $this->log->archive($decodedId);
            $this->setFlash('success', 'Log archived successfully');
        }
        $redirect = $this->get('redirect', 'log_list');
        $this->redirect($redirect);
    }

    public function unarchive($id): void
    {
        $decodedId = (int) base64_decode((string) $id);
        if ($decodedId > 0) {
            $this->log->unarchive($decodedId);
            $this->setFlash('success', 'Log restored successfully');
        }
        $redirect = $this->get('redirect', 'log_list');
        $this->redirect($redirect);
    }
}
