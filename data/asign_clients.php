<?php

 include_once 'session.php';
 include_once __DIR__.'/../controllers/index.php';
 include_once '../../inc/functions.php';

 //Fetching Values from URL
 if (isset($_POST['admin'])) { $admin= $_POST['admin'];}else{ $admin='';}

 if (isset($_POST['user'])) { $user= $_POST['user'];}else{ $user= '';}
 
 if (isset($_POST['role'])) { $role= $_POST['role'];}else{ $role= 0;}

 if (isset($_POST['created_by'])) { $created_by= $_POST['created_by'];}else{ $created_by= 0;}
 if (isset($_POST['updated_by'])) { $updated_by= $_POST['updated_by'];}else{ $updated_by= 0;}
 if (isset($_POST['created_date'])) { $created_date= $_POST['created_date'];}else{ $created_date= '';}
 if (isset($_POST['updated_date'])) { $updated_date= $_POST['updated_date'];}else{ $updated_date= '';}

 
 
 

 $result=$my_clients->register($admin,$user,$created_by);



 if($result['code']==200)
 {
     header('Location: ../admin?id=' . base64_encode($admin) . '&role=' . base64_encode($role) . '&error=' . base64_encode(4));
 }else
 {
     header('Location: ../admin?id=' . base64_encode($admin) . '&role=' . base64_encode($role) . '&error=' . base64_encode(3));
 }