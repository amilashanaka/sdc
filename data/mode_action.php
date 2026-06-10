<?php
include_once '../session.php';

header('Content-Type: application/json');

if (!isset($_SESSION['login'])) {
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

$mode = isset($_POST['mode']) ? strtoupper(trim($_POST['mode'])) : '';
if ($mode !== 'RUN' && $mode !== 'DEBUG') {
    echo json_encode(['success' => false, 'error' => 'Invalid mode']);
    exit;
}

$allowedModes = ['RUN' => 'run', 'DEBUG' => 'debug'];
$command = escapeshellcmd("sudo /var/www/html/pynq/mode_manager.sh " . $allowedModes[$mode]);
$output = [];
$return_var = 0;
exec($command . ' 2>&1', $output, $return_var);

if ($return_var !== 0) {
    echo json_encode([
        'success' => false,
        'error' => 'Mode switch failed',
        'details' => implode("\n", $output),
    ]);
    exit;
}

$message = ($mode === 'DEBUG') ? 'System is switching to DEBUG mode.' : 'System is switching to RUN mode.';

echo json_encode(['success' => true, 'message' => $message]);
