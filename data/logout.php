<?php


session_start();
unset($_SESSION['login']);
unset($_SESSION['login_name']);
unset($_SESSION['role']);

session_destroy();
header('Location: ../../admin/index');
exit();
