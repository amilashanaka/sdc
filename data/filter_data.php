<?php

include_once __DIR__ . '/../controllers/index.php';



$data = isset($_POST['data']) ? $_POST['data'] : [];
$table_name = isset($_POST['table']) ? $_POST['table'] : '';




if ($table_name == '') {
    $data = [
        'data' => [],
        'error' => 'invalid table',
        'status' => 0
    ];
    echo json_encode($response);
}


$result = $db->get_data($table_name, $data);
echo json_encode($result);
