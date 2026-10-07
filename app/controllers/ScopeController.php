<?php

class ScopeController extends BaseController
{
    private $flag;

    public function __construct()
    {
        $this->checkAuth();
        $this->flag = new Flag();
    }

    public function index()
    {
        $this->view('scope');
    }

    /**
     * Put the device into DEBUG mode so the oscilloscope can stream.
     *
     * Only flags.run_mode is written; mode_manager.py watches that column and
     * stops RUN mode / starts DEBUG mode on its own.
     */
    public function modeAction()
    {
        if (!$this->isPost()) {
            $this->json(['success' => false, 'error' => 'POST required'], 405);
        }

        $mode = strtoupper(trim($this->post('mode', '')));
        if (!in_array($mode, ['RUN', 'DEBUG'], true)) {
            $this->json(['success' => false, 'error' => 'Invalid mode'], 400);
        }

        if (!$this->flag->setRunMode($mode === 'DEBUG' ? Flag::DEBUG : Flag::RUN)) {
            $this->json(['success' => false, 'error' => 'Mode switch failed'], 500);
        }

        $this->json(['success' => true, 'mode' => $mode]);
    }
}