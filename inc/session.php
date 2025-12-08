<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

if (session_status() !== PHP_SESSION_ACTIVE) {session_start();}
include_once 'functions.php';

$today= date("Y-m-d H:i:s"); 



