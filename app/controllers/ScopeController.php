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

    public function modeAction()
    {
        if (!$this->isPost()) {
            $this->json(['success' => false, 'error' => 'POST required'], 405);
        }

        $mode = strtoupper(trim($this->post('mode', '')));
        if (!in_array($mode, ['RUN', 'DEBUG'], true)) {
            $this->json(['success' => false, 'error' => 'Invalid mode'], 400);
        }

        $runMode = $mode === 'DEBUG' ? 1 : 0;

        try {
            if (!$this->flag->setRunMode($runMode)) {
                throw new RuntimeException('Failed to update database');
            }

            $pynqDirectory = getenv('SPICER_PYNQ_DIR');
            if (!$pynqDirectory) {
                $pynqDirectory = is_dir('/var/www/html/pynq') ? '/var/www/html/pynq' : ROOT . '/pynq';
            }
            $modeFile = $pynqDirectory . '/.mode';
            if (@file_put_contents($modeFile, $mode . PHP_EOL) === false) {
                throw new RuntimeException('Unable to write mode file');
            }

            $this->json(['success' => true, 'mode' => $mode]);
        } catch (Throwable $exception) {
            $this->json(['success' => false, 'error' => 'Mode switch failed'], 500);
        }
    }
}