<?php

session_start();

// Switch to RUN mode before logout
$modeFile = '/var/www/html/pynq/.mode';
$currentMode = 'RUN';

// Check database first
try {
    include_once '../inc/database.php';
    if (isset($database) && $database->connection) {
        $result = $database->query("SELECT f1 FROM run_mode WHERE id=1 LIMIT 1");
        if ($result && $database->num_rows($result) > 0) {
            $row = $database->fetch_set($result);
            $currentMode = ($row['f1'] == 1) ? 'DEBUG' : 'RUN';
        }
    }
} catch (Exception $e) {
    // Fallback to file
}

if ($currentMode === 'RUN' && file_exists($modeFile)) {
    $fileMode = trim(file_get_contents($modeFile));
    if ($fileMode === 'DEBUG') {
        $currentMode = 'DEBUG';
    }
}

if ($currentMode !== 'RUN') {
    // Update database directly
    try {
        include_once '../inc/database.php';
        if (isset($database) && $database->connection) {
            $database->query("UPDATE run_mode SET f1=0 WHERE id=1");
        }
    } catch (Exception $e) {
        // Fallback to shell
        $command = escapeshellcmd("sudo /var/www/html/pynq/mode_manager.sh run");
        exec($command . ' 2>&1', $output, $return_var);
    }
    
    // Also update file
    if (file_exists($modeFile)) {
        file_put_contents($modeFile, 'RUN');
    }
}

unset($_SESSION['login']);
unset($_SESSION['login_name']);
unset($_SESSION['role']);

session_destroy();
header('Location: ../index');
exit();
