<?php

/**
 * Handles DEBUG mode switch with login authentication
 * 
 * Accepts POST with a_username, a_password, csrf_token
 * On successful login, switches system to DEBUG mode
 */

require_once '../session.php';
require_once '../controllers/index.php';
require_once '../inc/functions.php';

header('Content-Type: application/json');

// 1. Ensure the request is a POST request.
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Invalid request method']);
    exit;
}

// 2. CSRF Token Validation
if (!isset($_POST['csrf_token']) || !isset($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
    echo json_encode(['success' => false, 'error' => 'Invalid request. Please try again.']);
    exit;
}

// Unset the token after use
unset($_SESSION['csrf_token']);

// 3. Input Validation
if (empty($_POST['a_username']) || empty($_POST['a_password'])) {
    echo json_encode(['success' => false, 'error' => 'Username and password are required.']);
    exit;
}

// 4. Authentication
$username = $_POST['a_username'];
$password = $_POST['a_password'];

$result = $auth->admin_login($username, $password);

if ($result['error'] === null && isset($result['data'])) {
    // --- Login Success ---
    
    // Regenerate session ID
    session_regenerate_id(true);

    $admin = $result['data'];

    // Set session variables
    $_SESSION['login'] = $admin['id'];
    $_SESSION['role'] = $admin['f1'];
    $_SESSION['email'] = $username;
    $_SESSION['login_name'] = ($admin['f1'] < 3) ? $admin['f6'] : $admin['f4'];
    
    // Generate session key
    generateSessionKey($username);

    // 5. Switch to DEBUG mode
    $modeFile = '/var/www/html/pynq/.mode';
    $currentMode = 'RUN';
    if (file_exists($modeFile)) {
        $currentMode = trim(file_get_contents($modeFile));
    }

    if ($currentMode !== 'DEBUG') {
        $command = escapeshellcmd("sudo /var/www/html/pynq/mode_manager.sh debug");
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
    }

    echo json_encode([
        'success' => true,
        'message' => 'Authenticated. System is switching to DEBUG mode.',
        'redirect' => 'dashboard'
    ]);
    exit;

} else {
    // --- Login Failure ---
    echo json_encode(['success' => false, 'error' => 'Invalid username or password.']);
    exit;
}