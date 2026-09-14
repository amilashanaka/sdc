<?php

/**
 * Handles System Mode Switch (RUN <-> DEBUG)
 */

require_once '../session.php';
require_once '../controllers/index.php';
require_once '../inc/functions.php';
require_once '../inc/database.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit();
}

if (!isset($_POST['csrf_token']) || !isset($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Invalid CSRF token']);
    exit();
}

$targetMode = isset($_POST['mode']) ? strtoupper(trim($_POST['mode'])) : '';
if (!in_array($targetMode, ['RUN', 'DEBUG'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid mode']);
    exit();
}

try {
    $dbValue = ($targetMode === 'DEBUG') ? 1 : 0;
    
    if (isset($database) && $database->connection) {
        $result = $database->query("UPDATE run_mode SET f1 = $dbValue WHERE id = 1");
        if ($result) {
            $modeFile = '/var/www/html/pynq/.mode';
            @file_put_contents($modeFile, $targetMode);
            
            echo json_encode(['success' => true, 'mode' => $targetMode]);
            exit();
        }
    }
    
    $modeFile = '/var/www/html/pynq/.mode';
    if (@file_put_contents($modeFile, $targetMode) !== false) {
        echo json_encode(['success' => true, 'mode' => $targetMode]);
        exit();
    }
    
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Failed to update mode']);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Server error']);
}