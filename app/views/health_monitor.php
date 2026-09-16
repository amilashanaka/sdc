<?php
include_once 'header.php';
include_once 'loader.php';
include_once 'sidebar.php';
include_once 'navbar.php';

?>

<link rel="stylesheet" href="./assets/css/dashboard.css">


<!-- =========================================================
     DEBUG MODE ALWAYS ON
     ========================================================= -->

<script>
    document.body.classList.add('debug-mode');
</script>


<div class="content-wrapper" id="contentWrapper">

    <div class="daq-container">

        <!-- =====================================================
     HEADER
     ===================================================== -->

        <div class="daq-header">

            <div>

                <h1 class="daq-title">
                    <?php echo  $settings->app_name ?? 'Device Dashboard' ?>
                </h1>

                <div class="daq-subtitle">

                    SPICER DAQ
                    &nbsp;•&nbsp;
                    Serial: SP-0001
                    &nbsp;•&nbsp;
                    FW: 1.0.0
                    &nbsp;•&nbsp;
                    FPGA: 2.0.1

                </div>

            </div>

            <div class="daq-header-right">

                <div class="debug-badge" id="debugBadge">
                    <i class="fas fa-circle"></i>
                    Debug Mode
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


        <!-- =====================================================
     HEALTH MONITOR
     ===================================================== -->

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
                            class="health-overall status-running"
                            id="healthOverall">

                            <i class="fas fa-circle-check" id="healthOverallIcon"></i>

                            <span id="healthOverallText">
                                Healthy
                            </span>

                        </span>

                    </div>

                    <div class="daq-card-body">


                        <?php
                        $healthIconColors = [
                            'FPGA Temperature' => 'temp',
                            'CPU Load' => 'cpu',
                            'Memory Used' => 'mem',
                            'Core Voltage' => 'volt',
                        ];
                        foreach ($health_items as $index => $h):
                            $colorClass = $healthIconColors[$h['name']] ?? $index;
                        ?>
                        <div class="health-item">

                            <div class="health-icon health-icon-<?= $colorClass ?>">
                                <i class="fas <?= $h['icon'] ?>"></i>
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

                        </div>


                    </div>

                </div>

            </div>

        </div>

        <!-- =====================================================
     HEALTH DETAIL TABLE
     ===================================================== -->

        <div class="daq-card">

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

                        <?php foreach ($health_items as $h): ?>

                            <tr>

                                <td>
                                    <i class="fas <?= $h['icon'] ?>" style="color:var(--daq-blue);margin-right:6px;"></i>
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