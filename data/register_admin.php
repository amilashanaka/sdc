<?php

include_once '../session.php';
include_once '../../controllers/index.php';
include_once '../../inc/functions.php';

// Set target directory and page name
$target_dir = "../../uploads/admin/profile/";
$targ_front = "./uploads/admin/profile/";
$page = 'admin';

// Dynamically define keys to process from $_POST
$wanted_keys = array_keys($_POST);

// Initialize data array with defaults
$data = [
    'status' => 1,
];

// Process input keys dynamically
foreach ($wanted_keys as $key) {
    if (!empty($_POST[$key])) {
        // Handle integer fields
        if (in_array($key, ['id', 'created_by', 'updated_by', 'f1'])) {
            $data[$key] = (int)$_POST[$key];
        } else {
            // Sanitize string fields
            $data[$key] = htmlspecialchars(trim($_POST[$key]), ENT_QUOTES, 'UTF-8');
        }
    }
}

// Set default ID if not provided
if (!isset($data['id'])) {
    $data['id'] = 0;
}

// Dynamically get image keys from $_FILES
$image_keys = array_filter(array_keys($_FILES), function ($key) {
    return preg_match('/^(user_profile_image|profile_img|admin_img)\d*$/', $key); // Match profile image keys
});

// Handle image uploads dynamically
foreach ($image_keys as $key) {
    if ($uploaded_file = uploadPic($key, $target_dir)) {
        // Save the file path in the corresponding data key
        $data[$key] = $targ_front . $uploaded_file;
    }
}

// Validate required action
if (!isset($data['action']) || empty($data['action'])) {
    header('Location: ../admin?error=' . base64_encode(6));
    exit;
}

// Handle different actions
switch ($data['action']) {
    case 'register':
        handleRegister($data);
        break;
        
    case 'update':
        handleUpdate($data);
        break;
        
    case 'reset_pwd':
        handlePasswordReset($data);
        break;
        
    default:
        header('Location: ../admin?error=' . base64_encode(6));
        exit;
}

/**
 * Handle user registration
 */
function handleRegister($data) {
    global $admin, $timestamp;
    
    $role = (int)$data['f1'];
    
    // Validate role
    if (!in_array($role, [2, 3])) {
        header('Location: ../admin?role=' . base64_encode($role) . '&error=' . base64_encode(5));
        exit;
    }
    
    try {
        // Set creation timestamp
        $data['created_date'] = $timestamp;
        
        if ($role === 2) {
            // Admin registration - requires f2, f3, f6
            $result = $admin->register($data['f1'], $data['f2'] ?? '', $data['f3'] ?? '', $data['f6'] ?? '');
        } elseif ($role === 3) {
            // Carer registration - requires f4, f5, f6
            $result = $admin->register($data['f1'], $data['f4'] ?? '', $data['f5'] ?? '', $data['f6'] ?? '');
        }
        
        // Redirect based on result
        if (isset($result['code']) && $result['code'] == 200) {
            header('Location: ../admin_list?role=' . base64_encode($role) . '&error=' . base64_encode(4));
        } else {
            header('Location: ../admin?role=' . base64_encode($role) . '&error=' . base64_encode(3));
        }
        
    } catch (Exception $e) {
        error_log('Registration error: ' . $e->getMessage());
        header('Location: ../admin?role=' . base64_encode($role) . '&error=' . base64_encode(3));
    }
    
    exit;
}

/**
 * Handle user update
 */
function handleUpdate($data) {
    global $admin, $timestamp;
    
    // Validate ID
    if ($data['id'] <= 0) {
        header('Location: ../admin?error=' . base64_encode(7));
        exit;
    }
    
    try {
        // Set update timestamp
        $data['updated_date'] = $timestamp;
        
        // Remove action from data before update
        unset($data['action']);
        
        $result = $admin->update($data);
        
        // Prepare redirect parameters
        $params = ['id' => base64_encode($data['id'])];
        
        if (isset($result['error']) && $result['error'] === null && isset($result['status']) && $result['status'] == 1) {
            $params['error'] = base64_encode(1); // Success
            if (isset($result['message'])) {
                $info = is_array($result['message']) ? implode(" ", $result['message']) : $result['message'];
                $params['info'] = base64_encode($info);
            }
        } else {
            $params['error'] = base64_encode(2); // Error
            if (isset($result['message'])) {
                $info = is_array($result['message']) ? implode(" ", $result['message']) : $result['message'];
                $params['info'] = base64_encode($info);
            }
        }
        
        // Build redirect URL
        $redirect_url = '../admin.php?' . http_build_query($params);
        header("Location: $redirect_url");
        
    } catch (Exception $e) {
        error_log('Update error: ' . $e->getMessage());
        header('Location: ../admin.php?id=' . base64_encode($data['id']) . '&error=' . base64_encode(2));
    }
    
    exit;
}

/**
 * Handle password reset
 */
function handlePasswordReset($data) {
    global $admin;
    
    // Validate ID
    if ($data['id'] <= 0) {
        header('Location: ../admin?error=' . base64_encode(7));
        exit;
    }
    
    // Validate passwords
    if (empty($data['pwd']) || empty($data['pwd_conf'])) {
        header('Location: ../admin.php?id=' . base64_encode($data['id']) . '&error=' . base64_encode(2));
        exit;
    }
    
    if ($data['pwd'] !== $data['pwd_conf']) {
        $role = (int)$data['f1'];
        
        if ($role === 3) {
            // Special error handling for carer pin mismatch
            $error_data = [
                'id' => 0,
                'message' => 'Pin Miss Match',
                'topic' => 'Please check',
                'type' => 1
            ];
            
            header('Location: ../admin.php?id=' . base64_encode($data['id']) . '&error_c=' . base64_encode(json_encode($error_data)));
        } else {
            header('Location: ../admin.php?id=' . base64_encode($data['id']) . '&error=' . base64_encode(2));
        }
        exit;
    }
    
    try {
        $role = (int)$data['f1'];
        $result = $admin->reset_passeord($role, $data['id'], $data['pwd']);
        
        // Prepare redirect parameters
        $params = ['id' => base64_encode($data['id'])];
        
        if (isset($result['status']) && $result['status'] == 1) {
            $params['error'] = base64_encode(1); // Success
            if (isset($result['message'])) {
                $info = is_array($result['message']) ? implode(" ", $result['message']) : $result['message'];
                $params['info'] = base64_encode($info);
            }
        } else {
            $params['error'] = base64_encode(2); // Error
            if (isset($result['message'])) {
                $info = is_array($result['message']) ? implode(" ", $result['message']) : $result['message'];
                $params['info'] = base64_encode($info);
            }
        }
        
        // Build redirect URL
        $redirect_url = '../admin.php?' . http_build_query($params);
        header("Location: $redirect_url");
        
    } catch (Exception $e) {
        error_log('Password reset error: ' . $e->getMessage());
        header('Location: ../admin.php?id=' . base64_encode($data['id']) . '&error=' . base64_encode(2));
    }
    
    exit;
}