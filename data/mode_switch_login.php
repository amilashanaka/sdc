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
require_once '../inc/database.php';

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

    // 5. Switch to DEBUG mode (update database and file)
    try {
        $dbValue = 1; // DEBUG mode
        
        if (isset($database) && $database->connection) {
            $result = $database->query("UPDATE run_mode SET f1 = $dbValue WHERE id = 1");
            if ($result) {
                $modeFile = '/var/www/html/pynq/.mode';
                @file_put_contents($modeFile, 'DEBUG');
                
                echo json_encode([
                    'success' => true,
                    'message' => 'Authenticated. System switched to DEBUG mode.',
                    'redirect' => 'dashboard'
                ]);
                exit();
            }
        }
        
        // Fallback to file if database not available
        $modeFile = '/var/www/html/pynq/.mode';
        if (@file_put_contents($modeFile, 'DEBUG') !== false) {
            echo json_encode([
                'success' => true,
                'message' => 'Authenticated. System switched to DEBUG mode.',
                'redirect' => 'dashboard'
            ]);
            exit();
        }
        
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Failed to update mode']);
        
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Server error']);
    }

} else {
    // --- Login Failure ---
    echo json_encode(['success' => false, 'error' => 'Invalid username or password.']);
    exit;
}