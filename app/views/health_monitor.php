<?php
include_once 'header.php';
include_once 'loader.php';
include_once 'sidebar.php';
include_once 'navbar.php';

$settings = $settings ?? new Setting(1);
$deviceType = $deviceType ?? new DeviceType(0);
$modules = $modules ?? [];
$channels = $channels ?? [];
$active_channel_count = $active_channel_count ?? 0;
$logs = $logs ?? [];
$error_count = $error_count ?? 0;
$warning_count = $warning_count ?? 0;
$flags_row = $flags_row ?? null;
$overall_status = $overall_status ?? 'running';
$overall_label = $overall_label ?? 'Healthy';
$overall_icon = $overall_icon ?? 'fa-circle-check';

$device_name = $settings->f1 ?? 'SDC';
$serial = $settings->f6 ?? 'SN-XXXX';
$firmware = $settings->f5 ?? '1.0.0';
$local_ip = $settings->f4 ?? 'N/A';
$office = $settings->f3 ?? 'N/A';
$device_type_name = $deviceType->name ?? 'Unknown';

$run_mode = 0;
if ($flags_row && isset($flags_row->run_mode)) {
    $run_mode = (int) $flags_row->run_mode;
}
$run_mode_label = $run_mode === 1 ? 'Debug' : 'Run';
$run_mode_class = $run_mode === 1 ? 'warn' : 'ok';
?>

<link rel="stylesheet" href="./assets/css/dashboard.css">

<script>
    document.body.classList.add('debug-mode');
</script>

<div class="content-wrapper" id="contentWrapper">

    <div class="daq-container">

        <!-- HEADER -->
        <div class="daq-header">

            <div>

                <h1 class="daq-title">
                    <?php echo $device_name; ?>
                </h1>

                <div class="daq-subtitle">

                    SPICER DAQ
                    &nbsp;•&nbsp;
                    Serial: <?php echo htmlspecialchars($serial); ?>
                    &nbsp;•&nbsp;
                    FW: <?php echo htmlspecialchars($firmware); ?>
                    &nbsp;•&nbsp;
                    Type: <?php echo htmlspecialchars($device_type_name); ?>
                    &nbsp;•&nbsp;
                    IP: <?php echo htmlspecialchars($local_ip); ?>
                    &nbsp;•&nbsp;
                    Office: <?php echo htmlspecialchars($office); ?>

                </div>

            </div>

            <div class="daq-header-right">

                <div class="debug-badge" id="debugBadge">
                    <i class="fas fa-circle"></i>
                    <?php echo $run_mode_label; ?> Mode
                </div>

                <div class="connection-status">

                    <span class="connection-dot"></span>

                    <span id="connectionText">
                        Connected
                    </span>

                </div>

                <button
                    class="theme-button"
                    id="themeToggle"
                    title="Change theme">

                    <i class="fas fa-moon"></i>

                </button>

            </div>

        </div>


        <!-- OVERALL HEALTH SUMMARY -->
        <div class="row mb-3">

            <div class="col-lg-3 col-md-6 mb-3">
                <div class="daq-card health-summary-card h-100">
                    <div class="daq-card-body text-center">
                        <div class="health-summary-icon">
                            <i class="fas <?php echo $overall_icon; ?> fa-2x" style="color: <?php echo $overall_status === 'running' ? '#10b981' : '#ef4444'; ?>;"></i>
                        </div>
                        <h4 class="daq-card-title mt-2">Overall Status</h4>
                        <span class="health-overall status-<?php echo $overall_status; ?>">
                            <?php echo $overall_label; ?>
                        </span>
                    </div>
                </div>
            </div>

            <div class="col-lg-3 col-md-6 mb-3">
                <div class="daq-card health-summary-card h-100">
                    <div class="daq-card-body text-center">
                        <div class="health-summary-icon">
                            <i class="fas fa-exclamation-triangle fa-2x" style="color: #ef4444;"></i>
                        </div>
                        <h4 class="daq-card-title mt-2">Errors</h4>
                        <span class="health-overall status-idle">
                            <?php echo $error_count; ?>
                        </span>
                    </div>
                </div>
            </div>

            <div class="col-lg-3 col-md-6 mb-3">
                <div class="daq-card health-summary-card h-100">
                    <div class="daq-card-body text-center">
                        <div class="health-summary-icon">
                            <i class="fas fa-exclamation-circle fa-2x" style="color: #f59e0b;"></i>
                        </div>
                        <h4 class="daq-card-title mt-2">Warnings</h4>
                        <span class="health-overall status-running">
                            <?php echo $warning_count; ?>
                        </span>
                    </div>
                </div>
            </div>

            <div class="col-lg-3 col-md-6 mb-3">
                <div class="daq-card health-summary-card h-100">
                    <div class="daq-card-body text-center">
                        <div class="health-summary-icon">
                            <i class="fas fa-plug fa-2x" style="color: #3b82f6;"></i>
                        </div>
                        <h4 class="daq-card-title mt-2">Active Channels</h4>
                        <span class="health-overall status-running">
                            <?php echo $active_channel_count; ?>
                        </span>
                    </div>
                </div>
            </div>

        </div>


        <!-- DEVICE HEALTH MONITOR -->
        <div class="row">

            <div class="col-lg-12">

                <div class="daq-card health-card">

                    <div class="daq-card-header">

                        <div class="health-header-title">

                            <div class="health-header-icon">
                                <i class="fas fa-heartbeat"></i>
                            </div>

                            <h3 class="daq-card-title">
                                Device Health Monitor
                            </h3>

                        </div>

                        <span
                            class="health-overall status-<?php echo $overall_status; ?>"
                            id="healthOverall">

                            <i class="fas <?php echo $overall_icon; ?>" id="healthOverallIcon"></i>

                            <span id="healthOverallText">
                                <?php echo $overall_label; ?>
                            </span>

                        </span>

                    </div>

                    <div class="daq-card-body">

                        <?php foreach ($health_items as $index => $h):
                            $color = $h['color'] ?? '#2563eb';
                        ?>
                        <div class="health-item">

                            <div class="health-icon" style="background:linear-gradient(135deg, <?= $color ?>, <?= $color ?>88);box-shadow:0 4px 12px <?= $color ?>55;">
                                <i class="fas <?= $h['icon'] ?>" style="color:#fff;"></i>
                            </div>

                            <div class="health-content">

                                <div class="health-top">

                                    <span class="health-label">
                                        <?= $h['name'] ?>
                                    </span>

                                    <span class="health-value" id="<?= 'health' . $index . 'Value' ?>">
                                        <?= $h['value'] ?>
                                    </span>

                                </div>

                                <div class="health-track">
                                    <div class="health-fill health-<?= $h['state'] === 'running' ? 'ok' : 'warn' ?>" id="<?= 'health' . $index . 'Bar' ?>" style="width:<?= $h['value'] ?>%"></div>
                                </div>

                                <div class="health-description">
                                    <?= $h['description'] ?? '' ?>
                                </div>

                            </div>

                            <span class="health-pill health-pill-<?= $h['state'] === 'running' ? 'ok' : 'warn' ?>" id="<?= 'health' . $index . 'Pill' ?>">
                                <?= $h['label'] ?>
                            </span>

                        </div>
                        <?php endforeach; ?>


                        <div class="health-footer">

                            <div class="config-row">
                                <span class="config-label"><i class="fas fa-microchip" style="width:16px;margin-right:6px;"></i>FPGA</span>
                                <span class="config-value">Zynq-7020</span>
                            </div>

                            <div class="config-row">
                                <span class="config-label"><i class="fab fa-linux" style="width:16px;margin-right:6px;"></i>Linux</span>
                                <span class="config-value">PYNQ</span>
                            </div>

                            <div class="config-row">
                                <span class="config-label"><i class="fas fa-clock" style="width:16px;margin-right:6px;"></i>Uptime</span>
                                <span class="config-value" id="uptime">02d 14h 32m</span>
                            </div>

                            <div class="config-row">
                                <span class="config-label"><i class="fas fa-server" style="width:16px;margin-right:6px;"></i>Device</span>
                                <span class="config-value"><?php echo htmlspecialchars($device_type_name); ?></span>
                            </div>

                            <div class="config-row">
                                <span class="config-label"><i class="fas fa-tag" style="width:16px;margin-right:6px;"></i>Firmware</span>
                                <span class="config-value"><?php echo htmlspecialchars($firmware); ?></span>
                            </div>

                        </div>


                    </div>

                </div>

            </div>

        </div>


        <!-- MODULES STATUS -->
        <div class="row mt-4">

            <div class="col-lg-12">

                <div class="daq-card">

                    <div class="daq-card-header">

                        <h3 class="daq-card-title">

                            <i class="fas fa-puzzle-piece"></i>

                            Modules Status

                        </h3>

                    </div>

                    <div class="daq-card-body">

                        <div class="row">

                            <?php foreach ($modules as $m):
                                $color = '#2563eb';
                            ?>
                            <div class="col-lg-3 col-md-4 col-sm-6 mb-3">

                                <div class="daq-card module-status-card h-100">

                                    <div class="daq-card-body text-center p-3">

                                        <div class="module-icon mb-2">
                                            <i class="fas <?= htmlspecialchars($m['icon'] ?? 'fa-cube') ?> fa-2x" style="color:<?= $m['state'] === 'running' ? '#10b981' : '#6b7280'; ?>;"></i>
                                        </div>

                                        <h5 class="mb-1">
                                            <?= htmlspecialchars($m['name']) ?>
                                        </h5>

                                        <p class="text-muted small mb-2">
                                            <?= htmlspecialchars($m['address'] ?? 'N/A') ?>
                                        </p>

                                        <span class="module-status status-<?= $m['state'] ?>">
                                            <?= $m['label'] ?>
                                        </span>

                                    </div>

                                </div>

                            </div>
                            <?php endforeach; ?>

                        </div>

                    </div>

                </div>

            </div>

        </div>


        <!-- CHANNELS STATUS -->
        <div class="row mt-4">

            <div class="col-lg-12">

                <div class="daq-card">

                    <div class="daq-card-header">

                        <h3 class="daq-card-title">

                            <i class="fas fa-waves"></i>

                            Channels Status

                        </h3>

                    </div>

                    <div class="daq-card-body">

                        <table class="daq-table">

                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Channel</th>
                                    <th>Sequence</th>
                                    <th>Decimation</th>
                                    <th>Sample Rate</th>
                                    <th>Status</th>
                                </tr>
                            </thead>

                            <tbody>

                                <?php foreach ($channels as $c): ?>
                                <tr>

                                    <td>
                                        <?= htmlspecialchars($c['sequence'] ?? '') ?>
                                    </td>

                                    <td>
                                        <?= htmlspecialchars($c['name']) ?>
                                    </td>

                                    <td>
                                        <?= htmlspecialchars($c['sequence'] ?? '') ?>
                                    </td>

                                    <td>
                                        <?= htmlspecialchars($c['decimation'] ?? '0') ?>
                                    </td>

                                    <td>
                                        <span style="font-family:'SFMono-Regular','Courier New',monospace;">
                                            <?= htmlspecialchars($c['sample_rate'] ?? '0') ?> Hz
                                        </span>
                                    </td>

                                    <td>
                                        <span class="module-status status-<?= $c['state'] ?>">
                                            <?= $c['label'] ?>
                                        </span>
                                    </td>

                                </tr>
                                <?php endforeach; ?>

                            </tbody>

                        </table>

                    </div>

                </div>

            </div>

        </div>


        <!-- RECENT LOGS -->
        <div class="row mt-4">

            <div class="col-lg-12">

                <div class="daq-card">

                    <div class="daq-card-header">

                        <h3 class="daq-card-title">

                            <i class="fas fa-list-alt"></i>

                            Recent Logs

                        </h3>

                    </div>

                    <div class="daq-card-body">

                        <table class="daq-table">

                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Time</th>
                                    <th>Module</th>
                                    <th>Message</th>
                                    <th>Severity</th>
                                </tr>
                            </thead>

                            <tbody>

                                <?php if (empty($logs)): ?>
                                <tr>
                                    <td colspan="5" class="text-center text-muted">No recent logs.</td>
                                </tr>
                                <?php else: ?>
                                    <?php foreach ($logs as $index => $l): ?>
                                    <tr>

                                        <td>
                                            <?= $index + 1 ?>
                                        </td>

                                        <td style="font-family:'SFMono-Regular','Courier New',monospace;">
                                            <?= htmlspecialchars($l['date']) ?>
                                        </td>

                                        <td>
                                            <?= htmlspecialchars($l['module'] ?? '-') ?>
                                        </td>

                                        <td>
                                            <?= htmlspecialchars($l['message']) ?>
                                        </td>

                                        <td>
                                            <span class="module-status status-<?= $l['severity'] === 'error' ? 'idle' : ($l['severity'] === 'warning' ? 'warn' : 'running') ?>">
                                                <?= $l['label'] ?>
                                            </span>
                                        </td>

                                    </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>

                            </tbody>

                        </table>

                    </div>

                </div>

            </div>

        </div>


        <!-- HEALTH DETAIL TABLE -->
        <div class="daq-card mt-4">

            <div class="daq-card-header">

                <h3 class="daq-card-title">

                    <i class="fas fa-table"></i>

                    Health Details

                </h3>

            </div>

            <div class="daq-card-body">

                <table class="daq-table">

                    <thead>
                        <tr>
                            <th>Parameter</th>
                            <th>Value</th>
                            <th>Description</th>
                            <th>Status</th>
                        </tr>
                    </thead>

                    <tbody>

                        <?php foreach ($health_items as $index => $h):
                            $color = $h['color'] ?? '#2563eb';
                        ?>

                            <tr>

                                <td>
                                    <i class="fas <?= $h['icon'] ?>" style="color:<?= $color ?>;margin-right:6px;"></i>
                                    <?= $h['name'] ?>
                                </td>

                                <td style="font-family:'SFMono-Regular','Courier New',monospace;">
                                    <?= $h['value'] ?>
                                </td>

                                <td>
                                    <?= $h['description'] ?? '&mdash;' ?>
                                </td>

                                <td>
                                    <span class="module-status status-<?= $h['state'] ?>">
                                        <?= $h['label'] ?>
                                    </span>
                                </td>

                            </tr>

                        <?php endforeach; ?>

                    </tbody>

                </table>

            </div>

        </div>

    </div>

</div>


<script>
    /* =========================================================
   DARK / LIGHT MODE
   ========================================================= */

    const themeButton =
        document.getElementById('themeToggle');

    const savedTheme =
        localStorage.getItem('daqTheme');

    if (savedTheme === 'dark') {
        document.body.classList.add('daq-dark');

        themeButton.innerHTML =
            '<i class="fas fa-sun"></i>';
    }

    themeButton.addEventListener(
        'click',
        function() {

            document.body.classList.toggle(
                'daq-dark'
            );

            const dark =
                document.body.classList.contains(
                    'daq-dark'
                );

            localStorage.setItem(
                'daqTheme',
                dark ? 'dark' : 'light'
            );

            themeButton.innerHTML =
                dark ?
                '<i class="fas fa-sun"></i>' :
                '<i class="fas fa-moon"></i>';

        }
    );
</script>

<?php include_once 'footer.php'; ?>
