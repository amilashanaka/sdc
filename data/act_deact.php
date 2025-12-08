<?php


include_once  './../controllers/index.php';

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

$id = $_POST['id'];
$table_name = $_POST['tbl'];
$id_name = $_POST['id_name'];


if($_POST['action']=='activate' && $id > 0) {
    $data = [
        'id' => $id,
        'status' => 1,
        'updated_date' => date('Y-m-d H:i:s'),
        'updated_by' => $_SESSION['login']
    ];

}elseif($_POST['action']=='deactivate' && $id > 0) {
    $data = [
        'id' => $id,
        'status' => 0,
        'updated_date' => date('Y-m-d H:i:s'),
        'updated_by' => $_SESSION['login']
    ];
}

$result=$db->update($table_name, $data);
echo json_encode($result);

