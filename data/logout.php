<?php

session_start();

// Switch to RUN mode before logout
$modeFile = '/var/www/html/pynq/.mode';
$currentMode = 'RUN';
if (file_exists($modeFile)) {
    $currentMode = trim(file_get_contents($modeFile));
}

if ($currentMode !== 'RUN') {
    $command = escapeshellcmd("sudo /var/www/html/pynq/mode_manager.sh run");
    exec($command . ' 2>&1', $output, $return_var);
}

unset($_SESSION['login']);
unset($_SESSION['login_name']);
unset($_SESSION['role']);

session_destroy();
header('Location: ../index');
exit();
