<?php

include_once 'header.php';
include_once 'loader.php';
include_once 'sidebar.php';
include_once 'navbar.php';

/* FPGA modules are loaded from the database via DashboardController */

$kpi_cards = array();

$active_channels = 0;
$total_channels = count($channels);
foreach ($channels as $ch) {
    if ($ch['status'] == 1) {
        $active_channels++;
    }
}

$kpi_cards[] = array(
    'icon'   => 'fa fa-wave-square',
    'color'  => 'green',
    'label'  => 'Active ADC',
    'value'  => $active_channels . '/' . $total_channels,
    'id'     => 'adcStatus',
    'url'    => 'scope',
);

$max_sample_rate = 0;
foreach ($channels as $ch) {
    if ($ch['sample_rate'] > $max_sample_rate) {
        $max_sample_rate = $ch['sample_rate'];
    }
}
$sample_rate_k = $max_sample_rate >= 1000 ? ($max_sample_rate / 1000) . ' kSPS' : $max_sample_rate . ' SPSS';
$kpi_cards[] = array(
    'icon'   => 'fa fa-tachometer-alt',
    'color'  => 'blue',
    'label'  => 'Sample Rate',
    'value'  => $sample_rate_k,
    'id'     => 'sampleRate',
    'url'    => 'channel_list',
);

$fpga_temp = 42;
foreach ($health_items as $h) {
    if (strtolower($h['name']) === 'temperature') {
        $fpga_temp = $h['value'];
        break;
    }
}
$kpi_cards[] = array(
    'icon'   => 'fa fa-microchip',
    'color'  => 'orange',
    'label'  => 'FPGA Temperature',
    'value'  => $fpga_temp . ' °C',
    'id'     => 'fpgaTemp',
    'url'    => 'health_monitor',
);

$kpi_cards[] = array(
    'icon'   => 'fas fa-list',
    'color'  => 'purple',
    'label'  => 'System',
    'value'  => 'Logs',
    'id'     => '',
    'url'    => 'log_list',
);

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
                    <?php echo htmlspecialchars($settings->app_name ?? 'Device Dashboard') ?>
                </h1>

                <div class="daq-subtitle">

                    SPICER DAQ
                    &nbsp;•&nbsp;
                    <i class="fas fa-computer" style="width:16px;margin-right:6px;"></i>Serial: <?php echo htmlspecialchars($settings->f6 ?? 'SP-0001') ?>
                    &nbsp;•&nbsp;
                    <i class="fas fa-code" style="width:16px;margin-right:6px;"></i>FW: <?php echo htmlspecialchars($settings->f5 ?? '1.0.0') ?>
                    &nbsp;•&nbsp;
                    <i class="fas fa-microchip" style="width:16px;margin-right:6px;"></i>FPGA: 2.0.1
                    &nbsp;•&nbsp;
                    <i class="fab fa-linux" style="width:16px;margin-right:6px;"></i>Linux: PYNQ
                    &nbsp;•&nbsp;
                    <i class="fas fa-clock" style="width:16px;margin-right:6px;"></i>Uptime: 02d 14h 32m
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
     KPI
     ===================================================== -->

        <div class="row">

            <?php foreach ($kpi_cards as $k): ?>
                <div class="col-lg-3 col-md-6">

                    <a class="kpi-card-link"
                        href="<?= BASE_URL . '/' . ltrim($k['url'], '/') ?>"
                        style="text-decoration:none; color:inherit;">

                        <div class="kpi-card">

                            <div class="kpi-icon kpi-<?= $k['color'] ?>">
                                <i class="<?= $k['icon'] ?>"></i>
                            </div>

                            <div>

                                <div class="kpi-label">
                                    <?= $k['label'] ?>
                                </div>

                                <div
                                    class="kpi-value"
                                    <?php if (!empty($k['id'])) {
                                        echo 'id="' . $k['id'] . '"';
                                    } ?>>

                                    <?= $k['value'] ?>

                                </div>

                            </div>

                        </div>

                    </a>

                </div>
            <?php endforeach; ?>

        </div>


        <!-- =====================================================
     1. DEVICE HEALTH + FPGA MODULES
     ===================================================== -->

        <div class="row">


            <div class="col-lg-4">

                <div class="daq-card health-card">

                    <div class="daq-card-header">

                        <div class="health-header-title">

                            <div class="health-header-icon">
                                <i class="fas fa-heartbeat"></i>
                            </div>

                            <h3 class="daq-card-title">
                                Device Health
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

                                </div>

                                <span class="health-pill health-pill-<?= $h['state'] === 'running' ? 'ok' : 'warn' ?>" id="<?= 'health' . $index . 'Pill' ?>">
                                    <?= $h['label'] ?>
                                </span>

                            </div>
                        <?php endforeach; ?>



                    </div>

                </div>

            </div>


            <div class="col-lg-8">

                <div class="daq-card">

                    <div class="daq-card-header">

                        <h3 class="daq-card-title">

                            <i class="fas fa-microchip"></i>

                            FPGA Modules

                        </h3>

                        <span class="config-value debug-only">
                            <?= count($fpga_modules) ?> modules &mdash; grid view
                        </span>

                    </div>

                    <div class="daq-card-body">

                        <div class="module-grid">

                            <?php foreach ($fpga_modules as $m) { ?>

                                <div class="module-card<?= $m['primary'] ? '' : ' debug-only' ?>">

                                    <div class="module-card-top">

                                        <div class="module-icon">
                                            <i class="fas <?= $m['icon'] ?>"></i>
                                        </div>

                                        <span class="module-card-name">
                                            <?= $m['name'] ?>
                                        </span>

                                        <span class="module-status status-<?= $m['state'] ?>">
                                            <?= $m['label'] ?>
                                        </span>

                                    </div>

                                    <div class="module-debug-line">

                                        <span>BASE <b><?= $m['base'] ?></b></span>
                                        <span>IRQ <b><?= $m['irq'] ?></b></span>
                                        <span>STATUS <b><?= $m['status'] ?></b></span>
                                        <span>RESET <b><?= $m['reset'] ?></b></span>

                                    </div>

                                </div>

                            <?php } ?>

                        </div>

                    </div>

                </div>

            </div>

        </div>


        <!-- =====================================================
     2. DEBUG CONSOLE
     ===================================================== -->

        <div class="daq-card debug-only" id="debugConsole">

            <div class="daq-card-header">

                <h3 class="daq-card-title">

                    <i class="fas fa-terminal"></i>

                    Debug Console &mdash; All Modules

                </h3>

                <span class="debug-badge" style="display:inline-flex">
                    <i class="fas fa-circle"></i>
                    <?= count($fpga_modules) ?> Registered
                </span>

            </div>

            <div class="daq-card-body">

                <table class="daq-table">

                    <thead>
                        <tr>
                            <th>Module</th>
                            <th>Base Address</th>
                            <th>IRQ</th>
                            <th>Status Reg</th>
                            <th>Reset</th>
                            <th>State</th>
                        </tr>
                    </thead>

                    <tbody>

                        <?php foreach ($fpga_modules as $m) { ?>

                            <tr>

                                <td>
                                    <i class="fas <?= $m['icon'] ?>" style="color:var(--daq-blue);margin-right:6px;"></i>
                                    <?= $m['name'] ?>
                                </td>

                                <td style="font-family:'SFMono-Regular','Courier New',monospace;">
                                    <?= $m['base'] ?>
                                </td>

                                <td style="font-family:'SFMono-Regular','Courier New',monospace;">
                                    <?= $m['irq'] ?>
                                </td>

                                <td style="font-family:'SFMono-Regular','Courier New',monospace;">
                                    <?= $m['status'] ?>
                                </td>

                                <td>
                                    <?= $m['reset'] ?>
                                </td>

                                <td>
                                    <span class="module-status status-<?= $m['state'] ?>">
                                        <?= $m['label'] ?>
                                    </span>
                                </td>

                            </tr>

                        <?php } ?>

                    </tbody>

                </table>

            </div>

        </div>


        <!-- =====================================================
     3. LIVE ADC CHANNELS  (status + decimation)
     ===================================================== -->

        <div class="daq-card">

            <div class="daq-card-header">

                <h3 class="daq-card-title">

                    <i class="fas fa-grip-horizontal"></i>

                    Live ADC Channels

                </h3>

                <span class="config-value">
                    <?= count($channels) ?> Channels
                </span>

            </div>

            <div class="daq-card-body">

                <div class="row">

                    <?php foreach ($channels as $index => $ch):
                        $is_active = ($ch['status'] == 1);
                    ?>

                        <div class="col-xl-3 col-lg-3 col-md-4 col-6">

                            <div class="channel-card">

                                <div class="channel-top">

                                    <span class="channel-name">

                                        CH<?= $index + 1 ?>

                                    </span>

                                    <span class="channel-status status-<?= $is_active ? 'running' : 'idle' ?>" title="<?= $is_active ? 'Active' : 'Inactive' ?>">
                                    </span>

                                </div>

                                <div
                                    class="channel-value"
                                    id="ch<?= $index + 1 ?>">

                                    0.000

                                </div>

                                <div class="channel-meta">

                                    <span class="channel-unit">

                                        mG

                                    </span>

                                    <span
                                        class="channel-decimation"
                                        id="chDec<?= $index + 1 ?>"
                                        title="Active decimation factor">

                                        &divide;<?= $ch['decimation'] ?: 8 ?>

                                    </span>

                                </div>

                            </div>

                        </div>

                    <?php endforeach; ?>

                </div>

            </div>

        </div>


        <!-- =====================================================
     4. CHANNEL CONFIGURATION LIST
     ===================================================== -->

        <div class="daq-card">

            <div class="daq-card-header">

                <h3 class="daq-card-title">

                    <i class="fas fa-list-ol"></i>

                    Channel Configuration

                </h3>

                <span class="config-value">
                    Per-channel settings
                </span>

            </div>

            <div class="daq-card-body">

                <div class="channel-config-list">

                    <?php foreach ($channels as $index => $ch):
                        $is_active = ($ch['status'] == 1);
                    ?>

                        <div class="channel-config-row">

                            <span class="channel-config-tag">
                                CH<?= $index + 1 ?>
                            </span>

                            <div class="channel-config-field">

                                <span class="channel-config-field-label">
                                    Status
                                </span>

                                <span class="module-status status-<?= $is_active ? 'running' : 'idle' ?>" style="display:inline-block;padding:2px 7px;font-size:10px;">
                                    <?= $is_active ? 'Enabled' : 'Disabled' ?>
                                </span>

                            </div>

                            <div class="channel-config-field">

                                <span class="channel-config-field-label">
                                    Decimation
                                </span>

                                <span
                                    class="channel-config-field-value ch-config-dec"
                                    id="chCfgDec<?= $index + 1 ?>">

                                    &divide;<?= $ch['decimation'] ?: 8 ?>

                                </span>

                            </div>

                            <div class="channel-config-field">

                                <span class="channel-config-field-label">
                                    Input Range
                                </span>

                                <select class="channel-config-select">

                                    <option selected>&plusmn;10 mG</option>
                                    <option>&plusmn;20 mG</option>
                                    <option>&plusmn;50 mG</option>
                                    <option>&plusmn;100 mG</option>

                                </select>

                            </div>

                            <div class="channel-config-field">

                                <span class="channel-config-field-label">
                                    Gain
                                </span>

                                <select class="channel-config-select">

                                    <option>x1</option>
                                    <option selected>x2</option>
                                    <option>x4</option>
                                    <option>x8</option>

                                </select>

                            </div>

                            <div class="channel-config-field">

                                <span class="channel-config-field-label">
                                    Coupling
                                </span>

                                <select class="channel-config-select">

                                    <option selected>DC</option>
                                    <option>AC</option>

                                </select>

                            </div>

                        </div>

                    <?php endforeach; ?>

                </div>

            </div>

        </div>


        <!-- =====================================================
     5. ACQUISITION CONFIGURATION
     ===================================================== -->

        <div class="daq-card">

            <div class="daq-card-header">

                <h3 class="daq-card-title">

                    <i class="fas fa-sliders-h"></i>

                    Acquisition Configuration

                </h3>

                <button
                    class="daq-button button-primary"
                    id="saveConfig">

                    Save

                </button>

            </div>

            <div class="daq-card-body">

                <div class="row">

                    <div class="col-lg-6">

                        <div class="config-row">

                            <span class="config-label">
                                ADC Sample Rate
                            </span>

                            <select
                                class="daq-select"
                                id="sampleRateConfig">

                                <option value="100000">
                                    100 kSPS
                                </option>

                                <option value="200000" selected>
                                    200 kSPS
                                </option>

                                <option value="250000">
                                    250 kSPS
                                </option>

                                <option value="500000">
                                    500 kSPS
                                </option>

                            </select>

                        </div>


                        <div class="config-row">

                            <span class="config-label">
                                Decimation
                            </span>

                            <select
                                class="daq-select"
                                id="decimation">

                                <option value="1">x1</option>
                                <option value="2">x2</option>
                                <option value="4">x4</option>
                                <option value="8" selected>x8</option>
                                <option value="16">x16</option>
                                <option value="32">x32</option>

                            </select>

                        </div>


                        <div class="config-row">

                            <span class="config-label">
                                ADC Channels
                            </span>

                            <select
                                class="daq-select"
                                id="channelCount">

                                <option value="4">4 Channels</option>
                                <option value="8">8 Channels</option>
                                <option value="16" <?= count($channels) == 16 ? 'selected' : '' ?>>16 Channels</option>

                            </select>

                        </div>


                    </div>

                    <div class="col-lg-6">

                        <div class="config-row">

                            <span class="config-label">
                                ADC Resolution
                            </span>

                            <select
                                class="daq-select"
                                id="adcResolution">

                                <option>12-bit</option>
                                <option selected>16-bit</option>
                                <option>18-bit</option>

                            </select>

                        </div>


                        <div class="config-row">

                            <span class="config-label">
                                Buffer Size
                            </span>

                            <select
                                class="daq-select"
                                id="bufferSize">

                                <option>1024 Samples</option>
                                <option selected>4096 Samples</option>
                                <option>8192 Samples</option>
                                <option>16384 Samples</option>

                            </select>

                        </div>


                        <div class="config-row">

                            <span class="config-label">
                                Trigger
                            </span>

                            <select
                                class="daq-select"
                                id="triggerMode">

                                <option selected>Disabled</option>
                                <option>Rising Edge</option>
                                <option>Falling Edge</option>

                            </select>

                        </div>


                    </div>

                </div>

            </div>

        </div>


        <!-- =====================================================
     6. SPI / I2C MODULES  —  separate blocks
     ===================================================== -->

        <div class="row">


            <!-- SPI BLOCK -->

            <div class="col-lg-6">

                <div class="daq-card">

                    <div class="daq-card-header">

                        <h3 class="daq-card-title">

                            <i class="fas fa-random"></i>

                            SPI Modules

                        </h3>

                        <span class="module-status status-running">
                            3 Active
                        </span>

                    </div>

                    <div class="daq-card-body">

                        <div class="bus-block">

                            <div class="bus-block-header">

                                <div class="bus-block-icon bus-block-icon-spi">
                                    <i class="fas fa-broadcast-tower"></i>
                                </div>

                                <div>

                                    <h4 class="bus-block-title">
                                        SPI Bus
                                    </h4>

                                    <div class="bus-block-sub">
                                        Master controller &mdash; 0x44A00000
                                    </div>

                                </div>

                            </div>


                            <div class="bus-device">

                                <div class="bus-device-left">

                                    <div class="bus-device-icon">
                                        <i class="fas fa-microchip"></i>
                                    </div>

                                    <div>

                                        <div class="bus-device-name">
                                            SPI-1 &mdash; ADC Front-End
                                        </div>

                                        <div class="bus-device-meta">
                                            CS0 &middot; 5 MHz &middot; Mode 0
                                        </div>

                                    </div>

                                </div>

                                <div class="bus-device-right">

                                    <span class="bus-device-value">
                                        1.2 Mbit/s
                                    </span>

                                    <span class="module-status status-running">
                                        Active
                                    </span>

                                </div>

                            </div>


                            <div class="bus-device">

                                <div class="bus-device-left">

                                    <div class="bus-device-icon">
                                        <i class="fas fa-microchip"></i>
                                    </div>

                                    <div>

                                        <div class="bus-device-name">
                                            SPI-2 &mdash; EEPROM
                                        </div>

                                        <div class="bus-device-meta">
                                            CS1 &middot; 10 MHz &middot; Mode 0
                                        </div>

                                    </div>

                                </div>

                                <div class="bus-device-right">

                                    <span class="bus-device-value">
                                        480 kbit/s
                                    </span>

                                    <span class="module-status status-running">
                                        Active
                                    </span>

                                </div>

                            </div>


                            <div class="bus-device">

                                <div class="bus-device-left">

                                    <div class="bus-device-icon">
                                        <i class="fas fa-microchip"></i>
                                    </div>

                                    <div>

                                        <div class="bus-device-name">
                                            SPI-3 &mdash; DAC
                                        </div>

                                        <div class="bus-device-meta">
                                            CS2 &middot; 5 MHz &middot; Mode 1
                                        </div>

                                    </div>

                                </div>

                                <div class="bus-device-right">

                                    <span class="bus-device-value">
                                        0 bit/s
                                    </span>

                                    <span class="module-status status-idle">
                                        Idle
                                    </span>

                                </div>

                            </div>


                        </div>

                    </div>

                </div>

            </div>


            <!-- I2C BLOCK -->

            <div class="col-lg-6">

                <div class="daq-card">

                    <div class="daq-card-header">

                        <h3 class="daq-card-title">

                            <i class="fas fa-project-diagram"></i>

                            I2C Modules

                        </h3>

                        <span class="module-status status-running">
                            2 Active
                        </span>

                    </div>

                    <div class="daq-card-body">

                        <div class="bus-block">

                            <div class="bus-block-header">

                                <div class="bus-block-icon bus-block-icon-i2c">
                                    <i class="fas fa-network-wired"></i>
                                </div>

                                <div>

                                    <h4 class="bus-block-title">
                                        I2C Bus 0
                                    </h4>

                                    <div class="bus-block-sub">
                                        400 kHz &mdash; 0x40800000
                                    </div>

                                </div>

                            </div>


                            <div class="bus-device">

                                <div class="bus-device-left">

                                    <div class="bus-device-icon">
                                        <i class="fas fa-thermometer-half"></i>
                                    </div>

                                    <div>

                                        <div class="bus-device-name">
                                            Temperature Sensor
                                        </div>

                                        <div class="bus-device-meta">
                                            ADDR 0x48 &middot; 100 kHz
                                        </div>

                                    </div>

                                </div>

                                <div class="bus-device-right">

                                    <span class="bus-device-value">
                                        41.8 &deg;C
                                    </span>

                                    <span class="module-status status-running">
                                        Active
                                    </span>

                                </div>

                            </div>


                            <div class="bus-device">

                                <div class="bus-device-left">

                                    <div class="bus-device-icon">
                                        <i class="fas fa-tachometer-alt"></i>
                                    </div>

                                    <div>

                                        <div class="bus-device-name">
                                            IMU / Gyro
                                        </div>

                                        <div class="bus-device-meta">
                                            ADDR 0x68 &middot; 400 kHz
                                        </div>

                                    </div>

                                </div>

                                <div class="bus-device-right">

                                    <span class="bus-device-value">
                                        1 kHz ODR
                                    </span>

                                    <span class="module-status status-running">
                                        Active
                                    </span>

                                </div>

                            </div>


                        </div>


                        <div class="bus-block">

                            <div class="bus-block-header">

                                <div class="bus-block-icon bus-block-icon-i2c">
                                    <i class="fas fa-network-wired"></i>
                                </div>

                                <div>

                                    <h4 class="bus-block-title">
                                        I2C Bus 1
                                    </h4>

                                    <div class="bus-block-sub">
                                        100 kHz &mdash; 0x40810000
                                    </div>

                                </div>

                            </div>


                            <div class="bus-device">

                                <div class="bus-device-left">

                                    <div class="bus-device-icon">
                                        <i class="fas fa-memory"></i>
                                    </div>

                                    <div>

                                        <div class="bus-device-name">
                                            RTC / NVRAM
                                        </div>

                                        <div class="bus-device-meta">
                                            ADDR 0x57 &middot; 100 kHz
                                        </div>

                                    </div>

                                </div>

                                <div class="bus-device-right">

                                    <span class="bus-device-value">
                                        &mdash;
                                    </span>

                                    <span class="module-status status-idle">
                                        Idle
                                    </span>

                                </div>

                            </div>


                        </div>

                    </div>

                </div>

            </div>

        </div>


        <!-- =====================================================
     7. NETWORK + DATA STREAM
     ===================================================== -->

        <div class="row">


            <div class="col-lg-6">

                <div class="daq-card">

                    <div class="daq-card-header">

                        <h3 class="daq-card-title">

                            <i class="fas fa-network-wired"></i>

                            Network

                        </h3>

                    </div>

                    <div class="daq-card-body">

                        <div class="config-row">

                            <span class="config-label">
                                Ethernet
                            </span>

                            <span class="module-status status-running">
                                Connected
                            </span>

                        </div>

                        <div class="config-row">

                            <span class="config-label">
                                IP Address
                            </span>

                            <span class="config-value">
                                192.168.1.100
                            </span>

                        </div>

                        <div class="config-row">

                            <span class="config-label">
                                Link Speed
                            </span>

                            <span class="config-value">
                                1 Gbps
                            </span>

                        </div>

                        <div class="config-row">

                            <span class="config-label">
                                TLS
                            </span>

                            <span class="module-status status-running">
                                Enabled
                            </span>

                        </div>

                        <div class="config-row">

                            <span class="config-label">
                                WebSocket
                            </span>

                            <span class="module-status status-running">
                                Connected
                            </span>

                        </div>

                    </div>

                </div>

            </div>


            <div class="col-lg-6">

                <div class="daq-card">

                    <div class="daq-card-header">

                        <h3 class="daq-card-title">

                            <i class="fas fa-database"></i>

                            Data Stream

                        </h3>

                    </div>

                    <div class="daq-card-body">

                        <div class="config-row">

                            <span class="config-label">
                                Input
                            </span>

                            <span class="config-value">
                                <?= count($channels) ?> CH
                            </span>

                        </div>

                        <div class="config-row">

                            <span class="config-label">
                                Raw Rate
                            </span>

                            <span class="config-value">
                                6.4 MB/s
                            </span>

                        </div>

                        <div class="config-row">

                            <span class="config-label">
                                Output
                            </span>

                            <span class="config-value">
                                800 kSPS
                            </span>

                        </div>

                        <div class="config-row">

                            <span class="config-label">
                                Protocol
                            </span>

                            <span class="config-value">
                                TLS TCP
                            </span>

                        </div>

                        <div class="config-row">

                            <span class="config-label">
                                Buffer
                            </span>

                            <span class="config-value">
                                34%
                            </span>

                        </div>

                    </div>

                </div>

            </div>

        </div>


        <!-- =====================================================
     8. ACTIVE CONFIGURATION SNAPSHOT
     ===================================================== -->

        <div class="daq-card">

            <div class="daq-card-header">

                <h3 class="daq-card-title">

                    <i class="fas fa-clipboard-check"></i>

                    Active Configuration

                </h3>

                <span class="config-value" id="configLastSaved">
                    Last saved: &mdash;
                </span>

            </div>

            <div class="daq-card-body">

                <div class="row">

                    <div class="col-lg-2 col-md-4 col-6">
                        <div class="config-row" style="flex-direction:column;align-items:flex-start;gap:4px;">
                            <span class="config-label">Sample Rate</span>
                            <span class="config-value" id="activeSampleRate">200 kSPS</span>
                        </div>
                    </div>

                    <div class="col-lg-2 col-md-4 col-6">
                        <div class="config-row" style="flex-direction:column;align-items:flex-start;gap:4px;">
                            <span class="config-label">Decimation</span>
                            <span class="config-value" id="activeDecimation">&divide;8</span>
                        </div>
                    </div>

                    <div class="col-lg-2 col-md-4 col-6">
                        <div class="config-row" style="flex-direction:column;align-items:flex-start;gap:4px;">
                            <span class="config-label">Channels</span>
                            <span class="config-value" id="activeChannels">16 Channels</span>
                        </div>
                    </div>

                    <div class="col-lg-2 col-md-4 col-6">
                        <div class="config-row" style="flex-direction:column;align-items:flex-start;gap:4px;">
                            <span class="config-label">Resolution</span>
                            <span class="config-value" id="activeResolution">16-bit</span>
                        </div>
                    </div>

                    <div class="col-lg-2 col-md-4 col-6">
                        <div class="config-row" style="flex-direction:column;align-items:flex-start;gap:4px;">
                            <span class="config-label">Buffer Size</span>
                            <span class="config-value" id="activeBuffer">4096 Samples</span>
                        </div>
                    </div>

                    <div class="col-lg-2 col-md-4 col-6">
                        <div class="config-row" style="flex-direction:column;align-items:flex-start;gap:4px;">
                            <span class="config-label">Trigger</span>
                            <span class="config-value" id="activeTrigger">Disabled</span>
                        </div>
                    </div>

                </div>

            </div>

        </div>


        <!-- =====================================================
     9. EVENT LOG
     ===================================================== -->

        <div class="daq-card">

            <div class="daq-card-header">

                <h3 class="daq-card-title">

                    <i class="fas fa-list"></i>

                    System Events

                </h3>

                <button
                    class="daq-button button-light"
                    id="clearLog">

                    Clear

                </button>

            </div>

            <div class="daq-card-body">

                <table class="daq-table">

                    <thead>

                        <tr>

                            <th>Time</th>
                            <th>Level</th>
                            <th>Module</th>
                            <th>Message</th>

                        </tr>

                    </thead>

                    <tbody id="eventLog">

                        <tr>

                            <td>10:52:01</td>

                            <td>
                                <span class="module-status status-running">
                                    INFO
                                </span>
                            </td>

                            <td>ADC</td>

                            <td>Acquisition started</td>

                        </tr>

                        <tr>

                            <td>10:52:02</td>

                            <td>
                                <span class="module-status status-running">
                                    INFO
                                </span>
                            </td>

                            <td>DMA</td>

                            <td>Buffer ready</td>

                        </tr>

                        <tr>

                            <td>10:52:04</td>

                            <td>
                                <span class="module-status status-running">
                                    INFO
                                </span>
                            </td>

                            <td>Network</td>

                            <td>TLS connection established</td>

                        </tr>

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

    const channelCount = <?= count($channels) ?>;

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


    /* =========================================================
       CHANNEL DECIMATION DISPLAY
       Keeps the live channel badges AND the per-channel
       configuration list in sync with the Acquisition panel.
       ========================================================= */

    function applyDecimationToChannels(rawValue) {
        const label =
            '\u00F7' + rawValue;

        for (let i = 1; i <= channelCount; i++) {
            const live =
                document.getElementById('chDec' + i);

            if (live) {
                live.innerText = label;
            }

            const cfg =
                document.getElementById('chCfgDec' + i);

            if (cfg) {
                cfg.innerText = label;
            }
        }

        const activeDec =
            document.getElementById('activeDecimation');

        if (activeDec) {
            activeDec.innerText = label;
        }
    }


    /* =========================================================
       SAVE CONFIGURATION
       ========================================================= */

    document
        .getElementById('saveConfig')
        .addEventListener(
            'click',
            function() {

                const sampleRateSelect =
                    document.getElementById('sampleRateConfig');

                const decimationSelect =
                    document.getElementById('decimation');

                const channelSelect =
                    document.getElementById('channelCount');

                const resolutionSelect =
                    document.getElementById('adcResolution');

                const bufferSelect =
                    document.getElementById('bufferSize');

                const triggerSelect =
                    document.getElementById('triggerMode');

                const configuration = {

                    sample_rate: sampleRateSelect.value,

                    decimation: decimationSelect.value,

                    channels: channelSelect.value

                };

                console.log(
                    'Configuration:',
                    configuration
                );

                applyDecimationToChannels(decimationSelect.value);

                document.getElementById('activeSampleRate').innerText =
                    sampleRateSelect.options[sampleRateSelect.selectedIndex].text.trim();

                document.getElementById('activeChannels').innerText =
                    channelSelect.options[channelSelect.selectedIndex].text.trim();

                document.getElementById('activeResolution').innerText =
                    resolutionSelect.options[resolutionSelect.selectedIndex].text.trim();

                document.getElementById('activeBuffer').innerText =
                    bufferSelect.options[bufferSelect.selectedIndex].text.trim();

                document.getElementById('activeTrigger').innerText =
                    triggerSelect.options[triggerSelect.selectedIndex].text.trim();

                document.getElementById('configLastSaved').innerText =
                    'Last saved: ' + new Date().toLocaleTimeString();

                alert('Configuration saved');

            }
        );


    applyDecimationToChannels(
        document.getElementById('decimation').value
    );


    /* =========================================================
       CLEAR LOG
       ========================================================= */

    document
        .getElementById('clearLog')
        .addEventListener(
            'click',
            function() {

                document.getElementById(
                    'eventLog'
                ).innerHTML = '';

            }
        );


    /* =========================================================
       EXAMPLE REAL-TIME CHANNEL DATA
       ========================================================= */

    setInterval(
        function() {

            for (let i = 1; i <= channelCount; i++) {

                const value =
                    (
                        Math.random() * 20 - 10
                    ).toFixed(3);

                const element =
                    document.getElementById('ch' + i);

                if (element) {
                    element.innerText = value;
                }

            }

        },
        500
    );


    /* =========================================================
       DEVICE HEALTH
       ========================================================= */

    function setHealthBar(
        barId,
        valueId,
        pillId,
        percent,
        displayText,
        warnAt,
        critAt
    ) {
        const bar =
            document.getElementById(barId);

        const val =
            document.getElementById(valueId);

        const pill =
            document.getElementById(pillId);

        if (!bar) {
            return 'health-ok';
        }

        bar.style.width =
            Math.min(percent, 100).toFixed(1) + '%';

        bar.classList.remove(
            'health-ok', 'health-warn', 'health-crit'
        );

        let level = 'ok';

        if (percent >= critAt) {
            level = 'crit';
        } else if (percent >= warnAt) {
            level = 'warn';
        }

        bar.classList.add('health-' + level);

        if (val && displayText !== undefined && displayText !== null) {
            val.innerText = displayText;
        }

        if (pill) {
            pill.classList.remove(
                'health-pill-ok',
                'health-pill-warn',
                'health-pill-crit'
            );

            pill.classList.add('health-pill-' + level);

            pill.innerText =
                level === 'crit' ?
                'CRIT' :
                (level === 'warn' ? 'WARN' : 'OK');
        }

        return 'health-' + level;
    }

    setInterval(
        function() {

            const cpu =
                18 + Math.random() * 15;

            const mem =
                42 + Math.random() * 10;

            const tempC =
                40 + Math.random() * 10;

            const tempPercent =
                (tempC / 90) * 100;

            const cpuClass =
                setHealthBar(
                    'cpuBar',
                    'cpuUsage',
                    'cpuPill',
                    cpu,
                    cpu.toFixed(0) + '%',
                    70,
                    90
                );

            const memClass =
                setHealthBar(
                    'memBar',
                    'memoryUsage',
                    'memPill',
                    mem,
                    mem.toFixed(0) + '%',
                    75,
                    90
                );

            const tempClass =
                setHealthBar(
                    'fpgaTempBar',
                    'fpgaTempValue',
                    'fpgaTempPill',
                    tempPercent,
                    tempC.toFixed(0) + ' \u00B0C',
                    (60 / 90) * 100,
                    (75 / 90) * 100
                );

            const kpiTemp =
                document.getElementById('fpgaTemp');

            if (kpiTemp) {
                kpiTemp.innerText =
                    tempC.toFixed(0) + ' \u00B0C';
            }

            const overall =
                document.getElementById('healthOverall');

            const overallText =
                document.getElementById('healthOverallText');

            const overallIcon =
                document.getElementById('healthOverallIcon');

            if (overall && overallText && overallIcon) {
                overall.classList.remove(
                    'status-running',
                    'status-idle',
                    'status-disabled'
                );

                let level = 'ok';

                if (
                    tempClass === 'health-crit' ||
                    cpuClass === 'health-crit' ||
                    memClass === 'health-crit'
                ) {
                    level = 'crit';
                } else if (
                    tempClass === 'health-warn' ||
                    cpuClass === 'health-warn' ||
                    memClass === 'health-warn'
                ) {
                    level = 'warn';
                }

                if (level === 'crit') {
                    overall.classList.add('status-disabled');
                    overallIcon.className = 'fas fa-circle-exclamation';
                    overallText.innerText = 'Critical';
                } else if (level === 'warn') {
                    overall.classList.add('status-idle');
                    overallIcon.className = 'fas fa-triangle-exclamation';
                    overallText.innerText = 'Warning';
                } else {
                    overall.classList.add('status-running');
                    overallIcon.className = 'fas fa-circle-check';
                    overallText.innerText = 'Healthy';
                }
            }

        },
        2000
    );
</script>


<?php include 'footer.php'; ?>