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
$user = new UserController($database);
$admin = new AdminController($database);
$blog = new BlogController($database);
//$log = new LogController($database);
$signal = new SignalController($database);
$devices = new UserDeviceController($database);
$course = new CourseController($database);
// Db controller 
$db = new DbController($database);
$setting = new SettingController($database);
$payment = new PaymentController($database);
$slide = new SlideController($database);
$package = new PackageController($database);
$about = new AboutusController($database);
 

function dd($res){
var_dump($res);
exit;
}