<?php

class FlagController extends BaseController
{
    private $flag;

    public function __construct()
    {
        $this->checkAuth();
        $this->flag = new Flag();
    }

    public function getMode(): void
    {
        $this->json([
            'success' => true,
            'mode' => $this->flag->isDebugMode() ? 'DEBUG' : 'RUN',
            'run_mode' => $this->flag->getRunMode()
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

        $runMode = $mode === 'DEBUG' ? 1 : 0;
        
        if ($this->flag->setRunMode($runMode)) {
            $this->json(['success' => true, 'mode' => $mode]);
        } else {
            $this->json(['success' => false, 'error' => 'Failed to update mode'], 500);
        }
    }
}
