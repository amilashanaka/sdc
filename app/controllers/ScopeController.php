<?php

class ScopeController 
{
    public function __construct() 
    {
        $this->checkAuth();
    }

    public function index()
    {
        $this->view('scope');
    }

    public function modeAction()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json(['success' => false, 'error' => 'POST required'], 405);
        }

        $mode = strtoupper(trim($_POST['mode'] ?? ''));
        if (!in_array($mode, ['RUN', 'DEBUG'], true)) {
            $this->json(['success' => false, 'error' => 'Invalid mode'], 400);
        }

        try {
            $db = Database::getInstance();
            $db->query(
                'INSERT INTO run_mode (id, f1, status) VALUES (1, ?, 1) '
                . 'ON DUPLICATE KEY UPDATE f1 = VALUES(f1), status = 1',
                [$mode === 'DEBUG' ? 1 : 0]
            );

            $pynqDirectory = getenv('SPICER_PYNQ_DIR');
            if (!$pynqDirectory) {
                $pynqDirectory = is_dir('/var/www/html/pynq') ? '/var/www/html/pynq' : ROOT . '/pynq';
            }
            $modeFile = $pynqDirectory . '/.mode';
            $modeFile .= '/.mode';
            if (@file_put_contents($modeFile, $mode . PHP_EOL) === false) {
                throw new RuntimeException('Unable to write mode file');
            }

            $this->json(['success' => true, 'mode' => $mode]);
        } catch (Throwable $exception) {
            $this->json(['success' => false, 'error' => 'Mode switch failed'], 500);
        }
    }

    private function checkAuth() 
    {
        if (!isset($_SESSION['user_id'])) {
            header('Location: ' . BASE_URL . '/login');
            exit;
        }
    }

    private function view($file, $data = []) 
    {
        extract($data);
        require VIEWS . "/{$file}.php";
    }

    private function json($data, $statusCode = 200)
    {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }
}