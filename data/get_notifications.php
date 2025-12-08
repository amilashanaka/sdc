<?php
include_once '../session.php';
include_once   './../controllers/index.php';
include_once '../../inc/functions.php';



//if (isset($_POST['id'])) { $id= $_POST['id'];}else{ $id= 0;}
$id=$_SESSION['login'];
$result= $notification->get_notifications($id);
echo json_encode($result);


