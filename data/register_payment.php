<?php
include_once '../session.php';
include_once './../../controllers/index.php';
include_once '../../inc/functions.php';

// Configuration Array - All configurable values centralized here
$config = [
    // File Upload Configuration
    'upload' => [
        'target_dir' => "../../uploads/payment/",
        'front_target_dir' => "./uploads/payment/",
        'image_key_pattern' => '/^img\d+$/', // Regex pattern for image keys (img1, img2, etc.)
        'allowed_extensions' => ['jpg', 'jpeg', 'png', 'gif', 'webp'],
        'max_file_size' => 5 * 1024 * 1024, // 5MB in bytes
    ],
    
    // Page Configuration
    'page' => [
        'name' => 'payment',
        'redirect_base' => '', // Base URL for redirects if needed
    ],
    
    // Database Configuration
    'database' => [
        'model_name' => 'payment', // Variable name for the model ($blog)
        'update_method' => 'update',
        'register_method' => 'register',
        'id_field' => 'id',
    ],
    
    // Data Processing Configuration
    'data_processing' => [
        'default_status' => 1,
        'integer_fields' => ['created_by', 'updated_by', 'id'], // Fields to convert to integers
        'excluded_keys' => [], // Keys to exclude from processing
        'required_fields' => [], // Fields that must be present
        'timestamp_fields' => [
            'created' => 'created_date',
            'updated' => 'updated_date'
        ]
    ],
    
    // Validation Configuration
    'validation' => [
        'enable_validation' => true,
        'min_lengths' => [], // e.g., ['title' => 5, 'content' => 10]
        'max_lengths' => [], // e.g., ['title' => 255]
        'required_on_create' => [], // Fields required only on creation
        'required_on_update' => [], // Fields required only on update
    ],
    
    // Security Configuration
    'security' => [
        'sanitize_html' => false, // Whether to sanitize HTML content
        'allowed_html_tags' => '<p><br><strong><em><u><ol><ul><li><a><img>',
        'csrf_protection' => false, // Enable CSRF token validation
        'user_permission_check' => false, // Check user permissions
    ],
    
    // Debug Configuration
    'debug' => [
        'log_operations' => false,
        'log_file' => '../../logs/blog_operations.log',
        'display_errors' => false,
    ]
];

// Extract configuration values for easier access
$target_dir = $config['upload']['target_dir'];
$targ_front = $config['upload']['front_target_dir'];
$page = $config['page']['name'];
$model_name = $config['database']['model_name'];
$id_field = $config['database']['id_field'];

// Initialize data array with default values
$data = [
    'status' => $config['data_processing']['default_status'],
];

// Get ID from POST data
$data[$id_field] = isset($_POST[$id_field]) ? (int)$_POST[$id_field] : 0;

// Dynamically define keys to process from $_POST
$wanted_keys = array_diff(
    array_keys($_POST), 
    array_merge($config['data_processing']['excluded_keys'], [$id_field])
);

// Process input keys dynamically based on configuration
foreach ($wanted_keys as $key) {
    if (!empty($_POST[$key])) {
        // Convert to integer if specified in configuration
        if (in_array($key, $config['data_processing']['integer_fields'])) {
            $data[$key] = (int)$_POST[$key];
        } else {
            // Apply security filtering if enabled
            if ($config['security']['sanitize_html']) {
                $data[$key] = strip_tags($_POST[$key], $config['security']['allowed_html_tags']);
            } else {
                $data[$key] = $_POST[$key];
            }
        }
    }
}

// Validation function based on configuration
function validateData($data, $config, $is_update = false) {
    $errors = [];
    
    if (!$config['validation']['enable_validation']) {
        return $errors; // Skip validation if disabled
    }
    
    // Check required fields
    $required_fields = $is_update ? 
        $config['validation']['required_on_update'] : 
        $config['validation']['required_on_create'];
    
    foreach ($required_fields as $field) {
        if (empty($data[$field])) {
            $errors[] = "Field '$field' is required";
        }
    }
    
    // Check minimum lengths
    foreach ($config['validation']['min_lengths'] as $field => $min_length) {
        if (isset($data[$field]) && strlen($data[$field]) < $min_length) {
            $errors[] = "Field '$field' must be at least $min_length characters";
        }
    }
    
    // Check maximum lengths
    foreach ($config['validation']['max_lengths'] as $field => $max_length) {
        if (isset($data[$field]) && strlen($data[$field]) > $max_length) {
            $errors[] = "Field '$field' must not exceed $max_length characters";
        }
    }
    
    return $errors;
}

// Enhanced image upload function with configuration
function uploadImageWithConfig($key, $config) {
    if (!isset($_FILES[$key]) || $_FILES[$key]['error'] !== UPLOAD_ERR_OK) {
        return false;
    }
    
    $file = $_FILES[$key];
    $target_dir = $config['upload']['target_dir'];
    
    // Validate file extension
    $file_extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($file_extension, $config['upload']['allowed_extensions'])) {
        return false;
    }
    
    // Validate file size
    if ($file['size'] > $config['upload']['max_file_size']) {
        return false;
    }
    
    // Use existing uploadPic function or implement custom logic
    return uploadPic($key, $target_dir);
}

// Dynamically get image keys from $_FILES based on configuration pattern
$image_keys = array_filter(array_keys($_FILES), function ($key) use ($config) {
    return preg_match($config['upload']['image_key_pattern'], $key);
});

// Handle image uploads dynamically with configuration
foreach ($image_keys as $key) {
    if ($uploaded_file = uploadImageWithConfig($key, $config)) {
        $data[$key] = $targ_front . $uploaded_file;
    }
}

// Determine operation (Update or Register)
$is_update = $data[$id_field] > 0;

// Validate data based on configuration
$validation_errors = validateData($data, $config, $is_update);

if (!empty($validation_errors) && $config['validation']['enable_validation']) {
    // Handle validation errors
    if ($config['debug']['display_errors']) {
        die("Validation errors: " . implode(', ', $validation_errors));
    } else {
        // Redirect back with error
        header("Location: ../{$page}.php?error=validation");
        exit;
    }
}

// Add timestamp based on configuration
if ($is_update) {
    $timestamp_field = $config['data_processing']['timestamp_fields']['updated'];
    $data[$timestamp_field] = $timestamp;
    
    // Perform update operation
    $update_method = $config['database']['update_method'];
    $result = $$model_name->$update_method($data);
    
    // Log operation if enabled
    if ($config['debug']['log_operations']) {
        error_log("Updated record ID: {$data[$id_field]}", 3, $config['debug']['log_file']);
    }
    
} else {
    $timestamp_field = $config['data_processing']['timestamp_fields']['created'];
    $data[$timestamp_field] = $timestamp;

    // Perform register operation
    $register_method = $config['database']['register_method'];
    $result = $$model_name->$register_method($data);
    
    // Log operation if enabled
    if ($config['debug']['log_operations']) {
        error_log("Created new record", 3, $config['debug']['log_file']);
    }
}

// Enhanced redirect function with configuration
function getRedirectUrl($config, $is_update, $result, $id, $page) {
    $base_url = $config['page']['redirect_base'];
    
    // Use existing redirect_page function if available, otherwise create custom logic
    if (function_exists('redirect_page')) {
        return redirect_page($is_update, $result, $id, $page);
    }
    
    // Custom redirect logic based on configuration
    $status = $result ? 'success' : 'error';
    $action = $is_update ? 'update' : 'create';
    
    return $base_url . "../{$page}.php?status={$status}&action={$action}" . 
           ($id > 0 ? "&id=" . base64_encode($id) : "");
}

// Redirect to appropriate page based on configuration and result
$redirect_url = getRedirectUrl($config, $is_update, $result, $data[$id_field], $page);

header("Location: $redirect_url");
exit;