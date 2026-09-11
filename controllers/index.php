<?php
// Class AutoLoader implements
declare(strict_types=1);
spl_autoload_register(function ($class) {
    include_once  __DIR__ . "/$class.php";
});
require_once "config.php";
// Db instant
$database = new Database(DB_HOST, DB_NAME, DB_USER, DB_PASS);

// Controllers intialized
$auth = new AuthController($database);
$log = new LogController($database);
$module = new ModuleController($database);
 
 ?>