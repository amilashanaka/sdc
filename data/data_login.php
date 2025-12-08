<?php

/**
 * Handles Admin Login Authentication
 *
 * @package YourApplication
 * @version 1.1
 */

// It's a good practice to use require_once for essential files.
require_once  '../session.php';
require_once  '../controllers/index.php';
require_once  '../inc/functions.php';

// --- Security Checks ---

// 1. Ensure the request is a POST request.
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    // Respond with a generic error or redirect.
    // This prevents the script from being accessed directly via URL.
    header('Location: ../index.php');
    exit();
}

// 2. CSRF Token Validation
// Use hash_equals for a timing-attack-safe comparison.
if (!isset($_POST['csrf_token']) || !isset($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
    // Token is invalid or missing.
    $_SESSION['error'] = 'Invalid request. Please try again.';
    header('Location: ../index.php?error=1');
    exit();
}

// Unset the token after use to prevent reuse. A new one will be generated on the form page.
unset($_SESSION['csrf_token']);


// --- Input Validation ---

// Ensure username and password are provided.
if (empty($_POST['a_username']) || empty($_POST['a_password'])) {
    $_SESSION['error'] = 'Username and password are required.';
    header('Location: ../index.php?error=1');
    exit();
}

// --- Authentication ---

$username = $_POST['a_username'];
$password = $_POST['a_password'];

$result = $auth->admin_login($username, $password);

if ($result['error'] === null && isset($result['data'])) {
    // --- Login Success ---

    // Regenerate session ID to prevent session fixation attacks.
    session_regenerate_id(true);

    $admin = $result['data'];

    // Set session variables
    $_SESSION['login'] = $admin['id'];
    $_SESSION['role'] = $admin['f1'];
    $_SESSION['email'] = $username;
    $_SESSION['login_name'] = ($admin['f1'] < 3) ? $admin['f6'] : $admin['f4'];
    
    // Generate the session key required by the application
    generateSessionKey($username);

    // Redirect to the dashboard
    header('Location: ../dashboard');
    exit();

} else {
    // --- Login Failure ---

    // Provide a generic error message to avoid leaking information
    // about whether the username exists or not.
    $_SESSION['error'] = 'Invalid username or password.';
    
    // Redirect back to the login page
    header('Location: ../index.php?error=1');
    exit();
}


