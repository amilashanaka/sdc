<?php


// Define a function to validate the session key
function validateSessionKey() { 
    
    

   
    if (!isset($_SESSION['session_key'], $_SESSION['csrf_token'], $_SESSION['email'])) {
        return false;
    }

    $session_auth_key = $_SESSION['csrf_token'] . $_SESSION['email'] . session_id();

    $key = base64_encode($session_auth_key);


    return hash_equals($_SESSION['session_key'], $key);

}

function validateCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}



if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Check if the CSRF token is valid
    if (!isset($_POST['csrf_token']) || !validateCSRFToken($_POST['csrf_token'])) {
        http_response_code(404);
        exit();
    }

   
}



 // Validate session key (if needed)
 if (!validateSessionKey()) {
    session_unset();
    session_destroy();
    
    // Redirect to login page
    header('Location: ../index');
    exit();
}



