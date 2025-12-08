<?php
include_once '../session.php';
include_once './../../controllers/index.php';
include_once '../../inc/functions.php';

// Assuming you have a Transaction class defined somewhere included by index.php or elsewhere,
// you need to create the $transaction object here if it's not created yet.

 
// ========== CONFIGURATION SECTION ==========
// Change this module name to switch between different modules (transaction, product, user, etc.)
$module_name = 'package';

 
$config = [
    // Directory paths
    'target_dir' => "../../uploads/{$module_name}/",
    'targ_front' => "./uploads/{$module_name}/",

    // Page identifier
    'page' => $module_name,

    // Data model (object instance) - use the $transaction object, NOT string
    'model' => $package,

    // Default values
    'default_status' => 1,
    'default_id' => 0,

    // Keys that should be cast to integers
    'integer_keys' => ['created_by', 'updated_by'],

    // Image key pattern (regex for matching image keys like img1, img2, etc.)
    'image_key_pattern' => '/^img\d+$/',

    // Date field names
    'date_fields' => [
        'created_date' => 'created_date',
        'updated_date' => 'updated_date'
    ]
];

// Now include your register logic
include_once './register.php';
?>
