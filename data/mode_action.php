<?php
include_once '../session.php';
require_once '../inc/database.php';

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

try {
    $dbValue = ($mode === 'DEBUG') ? 1 : 0;
    
    if (isset($database) && $database->connection) {
        $result = $database->query("UPDATE run_mode SET f1 = $dbValue WHERE id = 1");
        if ($result) {
            $modeFile = '/var/www/html/pynq/.mode';
            @file_put_contents($modeFile, $mode);
            
            echo json_encode(['success' => true, 'message' => 'System switched to ' . $mode . ' mode.']);
            exit();
        }
    }
    
    $modeFile = '/var/www/html/pynq/.mode';
    if (@file_put_contents($modeFile, $mode) !== false) {
        echo json_encode(['success' => true, 'message' => 'System switched to ' . $mode . ' mode.']);
        exit();
    }
    
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Failed to update mode']);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Server error']);
}
