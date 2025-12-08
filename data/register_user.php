<?php

include_once '../session.php';
include_once __DIR__ . '/../../controllers/index.php';
include_once '../../inc/functions.php';

// Set target directory and page name
$target_dir = "../../uploads/user/profile/";
$targ_front = "./uploads/user/profile/";
$page = 'user';

// Dynamically define keys to process from $_POST
$wanted_keys = array_keys($_POST);

// Initialize data array with defaults
$data = [
    'status' => 1,
    'level' => 1,
];

// Process input keys dynamically
foreach ($wanted_keys as $key) {
    if (!empty($_POST[$key])) {
        // Handle integer fields
        if (in_array($key, ['id', 'level', 'created_by', 'updated_by'])) {
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
    return preg_match('/^(user_profile_image|profile_img|img)\d*$/', $key); // Match profile image keys
});

// Handle image uploads dynamically
foreach ($image_keys as $key) {
    if ($uploaded_file = uploadPic($key, $target_dir)) {
        // Save the file path in the corresponding data key
        $img_key = ($key === 'user_profile_image') ? 'img1' : $key;
        $data[$img_key] = $uploaded_file;
    }
}

if (isset($data['action'])) {
    unset($data['action']); // Remove action key if exists
}

// Determine operation (Update or Register)
$is_update = $data['id'] > 0;

// Set timestamps and user info
if ($is_update) {



    // Update user information
    $result = $user->update($data);

} else {



    $result = $user->register($data, ['f1']);
}

 

// Redirect to appropriate page based on the operation and result
$redirect_url = redirect_page($is_update, $result, $data['id'], $page);

header("Location: $redirect_url");
