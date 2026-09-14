<?php
// ===============================================

// Dynamically define keys to process from $_POST
$wanted_keys = array_keys($_POST);

// Initialize data array and set ID
$data = [
    'id' => isset($_POST['id']) ? (int)$_POST['id'] : $config['default_id'],
    'status' => $config['default_status'],
];

// Process input keys dynamically
foreach ($wanted_keys as $key) {
    if (!empty($_POST[$key])) {
        $data[$key] = in_array($key, $config['integer_keys']) ? (int)$_POST[$key] : $_POST[$key];
    }
}

// Dynamically get image keys from $_FILES
$image_keys = array_filter(array_keys($_FILES), function ($key) use ($config) {
    return preg_match($config['image_key_pattern'], $key);
});

// Handle image uploads dynamically
foreach ($image_keys as $key) {
    if ($uploaded_file = uploadPic($key, $config['target_dir'])) {
        // Save the file path in the corresponding data key
        $data[$key] = $config['targ_front'] . $uploaded_file;
    }
}

// Determine operation (Update or Register)
$is_update = $data['id'] > 0;

if ($is_update) {
    $data[$config['date_fields']['updated_date']] = $timestamp;
    $result = $config['model']->update($data);
    // var_dump($result);
    // exit;
} else {
    $data[$config['date_fields']['created_date']] = $timestamp;
    $result = $config['model']->register($data);

    //    var_dump($result);
    // exit;
}

// Redirect to appropriate page based on the operation and result
$redirect_url = redirect_page($is_update, $result, $data['id'], $config['page']);

header("Location: $redirect_url");
?>