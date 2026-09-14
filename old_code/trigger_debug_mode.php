<?php
/**
 * Spicer DAQ Debug Mode Trigger
 * This file is called when web portal is accessed to switch to DEBUG mode
 * Called via Apache rewrite rule on first request to /scope or /dashboard
 */

// Suppress errors to prevent interference with app
error_reporting(0);
ini_set('display_errors', 0);

$modeFile = '/var/www/html/pynq/.mode';
$triggerFile = '/var/www/html/pynq/.debug_triggered';

// Check if we've already triggered in this session (within last 2 seconds)
$triggered = false;
if (file_exists($triggerFile)) {
    $lastTrigger = filemtime($triggerFile);
    if ((time() - $lastTrigger) < 2) {
        $triggered = true;
    }
}

// If not recently triggered, switch to debug mode
if (!$triggered) {
    // Check current mode
    $currentMode = file_exists($modeFile) ? trim(file_get_contents($modeFile)) : 'RUN';
    
    if ($currentMode !== 'DEBUG') {
        // Call mode manager to switch to debug mode
        @shell_exec('sudo /var/www/html/pynq/mode_manager.sh debug 2>&1');
    }
    
    // Mark that we've triggered debug mode
    @file_put_contents($triggerFile, time());
}

// No output - this is a transparent trigger
?>
