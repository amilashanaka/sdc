<?php

/**
 * Controls the device operating mode stored in flags.run_mode.
 *
 * The database flag is the only thing this controller writes. pynq/mode_manager.py
 * polls that column and starts or stops the matching services, so there is no
 * service control from the web layer.
 */
class FlagController extends BaseController
{
    /** @var Flag */
    private $flag;

    public function __construct()
    {
        $this->checkAuth();
        $this->flag = new Flag();
    }

    public function index(): void
    {
        $this->redirect('health_monitor');
    }

    public function getMode(): void
    {
        $this->json([
            'success' => true,
            'mode' => $this->flag->getModeName(),
            'run_mode' => $this->flag->getRunMode(),
        ]);
    }

    public function setMode(): void
    {
        if (!$this->isPost()) {
            $this->json(['success' => false, 'error' => 'POST required'], 405);
        }

        $mode = strtoupper(trim($this->post('mode', '')));
        if (!in_array($mode, ['RUN', 'DEBUG'], true)) {
            $this->json(['success' => false, 'error' => 'Invalid mode'], 400);
        }

        if (!$this->flag->setRunMode($mode === 'DEBUG' ? Flag::DEBUG : Flag::RUN)) {
            $this->json(['success' => false, 'error' => 'Failed to update mode'], 500);
        }

        $this->json([
            'success' => true,
            'mode' => $mode,
            'run_mode' => $mode === 'DEBUG' ? Flag::DEBUG : Flag::RUN,
        ]);
    }
}
