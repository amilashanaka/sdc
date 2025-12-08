<?php
// Start session
session_start();

// Define paths
define('ROOT', dirname(__FILE__));
define('APP', ROOT . '/app');
define('CONFIG', ROOT . '/config');
define('VIEWS', APP . '/views');
define('ASSETS', ROOT . '/assets');

// Auto-detect base URL
$isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || 
           (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') ||
           (isset($_SERVER['HTTP_X_FORWARDED_SSL']) && $_SERVER['HTTP_X_FORWARDED_SSL'] === 'on');

$protocol = $isHttps ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'];
$scriptDir = dirname($_SERVER['SCRIPT_NAME']);

// Clean path (handle Windows backslashes)
$basePath = rtrim(str_replace('\\', '/', $scriptDir), '/');
$baseUrl = $protocol . '://' . $host . $basePath;

// Load config
$config = require CONFIG . '/config.php';

// Allow config to override, otherwise use auto-detected
if (!empty($config['base_url'])) {
    define('BASE_URL', rtrim($config['base_url'], '/'));
} else {
    define('BASE_URL', $baseUrl);
}

// Simple autoloader
spl_autoload_register(function($class) {
    $paths = [APP . '/core/', APP . '/controllers/', APP . '/models/'];
    foreach ($paths as $path) {
        $file = $path . $class . '.php';
        if (file_exists($file)) {
            require_once $file;
            return;
        }
    }
});

// Start the app
new App();