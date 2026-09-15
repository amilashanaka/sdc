<?php

include_once 'header.php';
include_once 'loader.php';
include_once 'sidebar.php';
include_once 'navbar.php';

/* =========================================================
   FPGA / SYSTEM MODULE MAP
   Single source of truth for the "FPGA Modules" summary
   card and the full Debug Console table. Replace these
   static entries with live reads from /sys, an AXI-Lite
   status register map, or your control API.
   ========================================================= */

$fpga_modules = array(

    array(
        'key'     => 'adc',
        'primary' => true,
        'name'    => 'ADC Core',
        'icon'    => 'fa-wave-square',
        'base'    => '0x43C00000',
        'irq'     => '29',
        'status'  => '0x00000001',
        'reset'   => 'De-asserted',
        'state'   => 'running',
        'label'   => 'Running',
    ),
    array(
        'key'     => 'dma',
        'primary' => true,
        'name'    => 'DMA Engine',
        'icon'    => 'fa-exchange-alt',
        'base'    => '0x40400000',
        'irq'     => '30',
        'status'  => '0x00000003',
        'reset'   => 'De-asserted',
        'state'   => 'running',
        'label'   => 'Running',
    ),
    array(
        'key'     => 'filter',
        'primary' => true,
        'name'    => 'Digital Filter',
        'icon'    => 'fa-filter',
        'base'    => '0x43C10000',
        'irq'     => '-',
        'status'  => '0x00000001',
        'reset'   => 'De-asserted',
        'state'   => 'running',
        'label'   => 'Running',
    ),
    array(
        'key'     => 'trigger',
        'primary' => true,
        'name'    => 'Trigger',
        'icon'    => 'fa-bolt',
        'base'    => '0x41200000',
        'irq'     => '31',
        'status'  => '0x00000000',
        'reset'   => 'De-asserted',
        'state'   => 'idle',
        'label'   => 'Idle',
    ),
    array(
        'key'     => 'spi',
        'primary' => true,
        'name'    => 'SPI Controller',
        'icon'    => 'fa-broadcast-tower',
        'base'    => '0x44A00000',
        'irq'     => '28',
        'status'  => '0x00000007',
        'reset'   => 'De-asserted',
        'state'   => 'running',
        'label'   => 'Running',
    ),
    array(
        'key'     => 'tls',
        'primary' => true,
        'name'    => 'TLS Server',
        'icon'    => 'fa-lock',
        'base'    => 'PS (software)',
        'irq'     => 'n/a',
        'status'  => 'ESTABLISHED',
        'reset'   => 'n/a',
        'state'   => 'running',
        'label'   => 'Running',
    ),
    array(
        'key'     => 'interconnect',
        'primary' => false,
        'name'    => 'AXI Interconnect',
        'icon'    => 'fa-sitemap',
        'base'    => 'Fabric',
        'irq'     => 'n/a',
        'status'  => '0x00000000',
        'reset'   => 'De-asserted',
        'state'   => 'running',
        'label'   => 'Active',
    ),
    array(
        'key'     => 'clkwiz',
        'primary' => false,
        'name'    => 'Clocking Wizard',
        'icon'    => 'fa-clock',
        'base'    => '0x43C20000',
        'irq'     => 'n/a',
        'status'  => 'LOCKED',
        'reset'   => 'De-asserted',
        'state'   => 'running',
        'label'   => 'Locked',
    ),
    array(
        'key'     => 'sysreset',
        'primary' => false,
        'name'    => 'Processor Sys Reset',
        'icon'    => 'fa-power-off',
        'base'    => 'PS7',
        'irq'     => 'n/a',
        'status'  => '0x00000000',
        'reset'   => 'De-asserted',
        'state'   => 'running',
        'label'   => 'Stable',
    ),
    array(
        'key'     => 'gpio',
        'primary' => false,
        'name'    => 'AXI GPIO',
        'icon'    => 'fa-toggle-on',
        'base'    => '0x41220000',
        'irq'     => '-',
        'status'  => '0x0000A5A5',
        'reset'   => 'De-asserted',
        'state'   => 'running',
        'label'   => 'Active',
    ),
    array(
        'key'     => 'intc',
        'primary' => false,
        'name'    => 'Interrupt Controller',
        'icon'    => 'fa-project-diagram',
        'base'    => '0x41800000',
        'irq'     => '61',
        'status'  => '0x40000000',
        'reset'   => 'De-asserted',
        'state'   => 'running',
        'label'   => 'Active',
    ),
    array(
        'key'     => 'fifo',
        'primary' => false,
        'name'    => 'DMA FIFO / BRAM',
        'icon'    => 'fa-memory',
        'base'    => '0x43C30000',
        'irq'     => '-',
        'status'  => '34% full',
        'reset'   => 'De-asserted',
        'state'   => 'idle',
        'label'   => '34% Full',
    ),

);

?>

<style>

/* =========================================================
   PYNQ-Z1 DAQ DASHBOARD
   Lightweight Industrial UI
   ========================================================= */

:root {

    --daq-bg: #f4f6f9;
    --daq-card: #ffffff;
    --daq-text: #172033;
    --daq-muted: #718096;
    --daq-border: #e5e9f0;

    --daq-blue: #2563eb;
    --daq-green: #10b981;
    --daq-orange: #f59e0b;
    --daq-red: #ef4444;
    --daq-purple: #7c3aed;

    --daq-shadow: 0 3px 15px rgba(15,23,42,.06);
}

/* DARK MODE */

body.daq-dark {

    --daq-bg: #0f172a;
    --daq-card: #172033;
    --daq-text: #e5e7eb;
    --daq-muted: #94a3b8;
    --daq-border: #263247;

    --daq-shadow: 0 4px 20px rgba(0,0,0,.25);
}

body.daq-dark .content-wrapper {
    background: var(--daq-bg);
}

body.daq-dark .daq-card {
    background: var(--daq-card);
    border-color: var(--daq-border);
}

body.daq-dark .daq-table td,
body.daq-dark .daq-table th {
    border-color: var(--daq-border);
}

body.daq-dark .daq-input,
body.daq-dark .daq-select {
    background: #111827;
    color: #e5e7eb;
    border-color: var(--daq-border);
}

body.daq-dark .scope-container {
    background: #0b1220;
}

body.daq-dark .status-running {
    color: #34d399;
    background: rgba(16,185,129,.18);
}

body.daq-dark .status-idle {
    color: #fbbf24;
    background: rgba(245,158,11,.18);
}

body.daq-dark .status-disabled {
    color: #94a3b8;
    background: rgba(148,163,184,.16);
}

body.daq-dark .health-track {
    background: #0b1220;
}

body.daq-dark .module-debug-line {
    background: #0b1220;
}

/* GENERAL */

.content-wrapper {
    background: var(--daq-bg);
    min-height: calc(100vh - 57px);
}

.daq-container {
    padding: 20px;
    color: var(--daq-text);
}

/* HEADER */

.daq-header {

    display: flex;
    justify-content: space-between;
    align-items: center;

    margin-bottom: 20px;
}

.daq-title {

    margin: 0;
    font-size: 23px;
    font-weight: 700;
    color: var(--daq-text);
}

.daq-subtitle {

    margin-top: 4px;
    color: var(--daq-muted);
    font-size: 13px;
}

.daq-header-right {

    display: flex;
    align-items: center;
    gap: 10px;
}

/* CONNECTION */

.connection-status {

    display: flex;
    align-items: center;
    gap: 7px;

    padding: 8px 13px;

    background: var(--daq-card);

    border: 1px solid var(--daq-border);

    border-radius: 20px;

    font-size: 13px;
}

.connection-dot {

    width: 9px;
    height: 9px;

    border-radius: 50%;

    background: var(--daq-green);

    box-shadow: 0 0 8px rgba(16,185,129,.6);
}

/* THEME BUTTON */

.theme-button {

    width: 38px;
    height: 38px;

    border: 1px solid var(--daq-border);

    background: var(--daq-card);

    color: var(--daq-text);

    border-radius: 10px;

    cursor: pointer;

    transition: .2s;
}

.theme-button:hover {

    transform: translateY(-1px);

}

.theme-button.is-active {

    border-color: var(--daq-red);

    color: var(--daq-red);

}

/* DEBUG MODE */

.debug-badge {

    display: none;

    align-items: center;
    gap: 6px;

    padding: 5px 11px;

    background: rgba(239,68,68,.12);

    color: var(--daq-red);

    border: 1px solid rgba(239,68,68,.35);

    border-radius: 20px;

    font-size: 11px;
    font-weight: 700;

    text-transform: uppercase;
    letter-spacing: .5px;
}

.debug-badge i {

    font-size: 8px;

    animation: debug-pulse 1.4s infinite;
}

@keyframes debug-pulse {
    0%, 100% { opacity: 1; }
    50% { opacity: .3; }
}

body.debug-mode .debug-badge {
    display: inline-flex;
}

.debug-only {
    display: none;
}

body.debug-mode .debug-only {
    display: block;
}

body.debug-mode span.debug-only {
    display: inline;
}

body.debug-mode .module-row {

    flex-wrap: wrap;
}

.module-debug-line {

    display: none;

    width: 100%;

    margin-top: 8px;

    padding: 8px 10px;

    background: var(--daq-bg);

    border: 1px dashed var(--daq-border);

    border-radius: 7px;

    font-family: 'SFMono-Regular', 'Courier New', monospace;

    font-size: 10.5px;

    line-height: 1.6;

    color: var(--daq-muted);
}

body.debug-mode .module-debug-line {
    display: block;
}

.module-debug-line b {
    color: var(--daq-text);
}

/* CARD */

.daq-card {

    background: var(--daq-card);

    border: 1px solid var(--daq-border);

    border-radius: 14px;

    box-shadow: var(--daq-shadow);

    margin-bottom: 18px;

    overflow: hidden;
}

.daq-card-header {

    display: flex;
    justify-content: space-between;
    align-items: center;

    padding: 15px 18px;

    border-bottom: 1px solid var(--daq-border);
}

.daq-card-title {

    margin: 0;

    font-size: 15px;

    font-weight: 700;

    color: var(--daq-text);
}

.daq-card-title i {

    margin-right: 7px;

    color: var(--daq-blue);
}

.daq-card-body {

    padding: 18px;
}

/* KPI */

.kpi-card {

    display: flex;
    align-items: center;

    gap: 13px;

    padding: 17px;

    background: var(--daq-card);

    border: 1px solid var(--daq-border);

    border-radius: 14px;

    box-shadow: var(--daq-shadow);

    margin-bottom: 18px;
}

.kpi-icon {

    width: 46px;
    height: 46px;

    border-radius: 11px;

    display: flex;
    align-items: center;
    justify-content: center;

    color: #fff;

    font-size: 18px;
}

.kpi-blue {
    background: var(--daq-blue);
}

.kpi-green {
    background: var(--daq-green);
}

.kpi-orange {
    background: var(--daq-orange);
}

.kpi-purple {
    background: var(--daq-purple);
}

.kpi-label {

    color: var(--daq-muted);

    font-size: 11px;

    text-transform: uppercase;

    letter-spacing: .5px;
}

.kpi-value {

    color: var(--daq-text);

    font-size: 20px;

    font-weight: 700;

    margin-top: 2px;
}

/* SCOPE */

.scope-container {

    height: 330px;

    background: #f8fafc;

    border-radius: 10px;

    overflow: hidden;
}

/* MODULE */

.module-row {

    display: flex;

    justify-content: space-between;

    align-items: center;

    padding: 11px 0;

    border-bottom: 1px solid var(--daq-border);
}

.module-row:last-child {
    border-bottom: 0;
}

.module-name {

    display: flex;

    align-items: center;

    gap: 9px;

    font-size: 13px;

    color: var(--daq-text);
}

.module-icon {

    width: 28px;
    height: 28px;

    border-radius: 7px;

    display: flex;

    align-items: center;
    justify-content: center;

    background: rgba(37,99,235,.1);

    color: var(--daq-blue);

    font-size: 12px;
}

.module-status {

    font-size: 11px;

    font-weight: 600;

    padding: 4px 8px;

    border-radius: 20px;
}

.status-running {

    color: #059669;

    background: rgba(16,185,129,.12);
}

.status-idle {

    color: #d97706;

    background: rgba(245,158,11,.13);
}

.status-disabled {

    color: #64748b;

    background: rgba(100,116,139,.12);
}

/* CONFIGURATION */

.config-row {

    display: flex;

    justify-content: space-between;

    align-items: center;

    padding: 10px 0;

    border-bottom: 1px solid var(--daq-border);
}

.config-row:last-child {
    border-bottom: 0;
}

.config-label {

    font-size: 13px;

    color: var(--daq-muted);
}

.config-value {

    font-size: 13px;

    font-weight: 600;

    color: var(--daq-text);
}

.daq-input,
.daq-select {

    width: 130px;

    height: 34px;

    padding: 5px 9px;

    border: 1px solid var(--daq-border);

    border-radius: 7px;

    background: var(--daq-card);

    color: var(--daq-text);

    font-size: 13px;

    outline: none;
}

.daq-input:focus,
.daq-select:focus {

    border-color: var(--daq-blue);

    box-shadow: 0 0 0 2px rgba(37,99,235,.1);
}

/* BUTTONS */

.daq-button {

    border: 0;

    border-radius: 8px;

    padding: 8px 13px;

    font-size: 12px;

    font-weight: 600;

    cursor: pointer;
}

.button-primary {

    background: var(--daq-blue);

    color: #fff;
}

.button-success {

    background: var(--daq-green);

    color: #fff;
}

.button-danger {

    background: var(--daq-red);

    color: #fff;
}

.button-light {

    background: var(--daq-bg);

    color: var(--daq-text);

    border: 1px solid var(--daq-border);
}

/* CHANNELS */

.channel-card {

    background: var(--daq-card);

    border: 1px solid var(--daq-border);

    border-radius: 11px;

    padding: 13px;

    margin-bottom: 12px;

    transition: .15s;
}

.channel-card:hover {

    border-color: var(--daq-blue);

    transform: translateY(-1px);
}

.channel-top {

    display: flex;

    justify-content: space-between;

    align-items: center;
}

.channel-name {

    font-size: 12px;

    font-weight: 700;

    color: var(--daq-muted);
}

.channel-status {

    width: 7px;
    height: 7px;

    background: var(--daq-green);

    border-radius: 50%;
}

.channel-value {

    margin-top: 7px;

    font-size: 21px;

    font-weight: 700;

    color: var(--daq-text);
}

.channel-unit {

    font-size: 10px;

    color: var(--daq-muted);
}

.channel-meta {

    display: flex;

    justify-content: space-between;

    align-items: center;

    margin-top: 7px;
}

.channel-decimation {

    font-family: 'SFMono-Regular', 'Courier New', monospace;

    font-size: 10px;

    font-weight: 700;

    color: var(--daq-blue);

    background: rgba(37,99,235,.1);

    padding: 2px 6px;

    border-radius: 5px;
}

/* HEALTH */

.health-item {

    padding: 12px 0;

    border-bottom: 1px solid var(--daq-border);
}

.health-item:last-child {
    border-bottom: 0;
}

.health-top {

    display: flex;

    justify-content: space-between;

    align-items: baseline;

    margin-bottom: 7px;
}

.health-label {

    font-size: 12px;

    color: var(--daq-muted);
}

.health-label i {

    width: 16px;

    margin-right: 6px;

    color: var(--daq-muted);
}

.health-value {

    font-size: 13px;

    font-weight: 700;

    color: var(--daq-text);
}

.health-track {

    height: 6px;

    background: var(--daq-bg);

    border-radius: 4px;

    overflow: hidden;
}

.health-fill {

    height: 100%;

    border-radius: 4px;

    transition: width .3s ease;
}

.health-ok {
    background: var(--daq-green);
}

.health-warn {
    background: var(--daq-orange);
}

.health-crit {
    background: var(--daq-red);
}

/* LOG */

.daq-table {

    width: 100%;

    border-collapse: collapse;

    font-size: 12px;
}

.daq-table th {

    color: var(--daq-muted);

    font-size: 10px;

    text-transform: uppercase;

    letter-spacing: .5px;
}

.daq-table td,
.daq-table th {

    padding: 9px;

    border-bottom: 1px solid var(--daq-border);

}

/* RESPONSIVE */

@media(max-width:768px) {

    .daq-header {

        align-items: flex-start;

        flex-direction: column;

        gap: 12px;
    }

    .daq-header-right {

        width: 100%;

        justify-content: space-between;
    }

    .daq-container {

        padding: 12px;
    }

}

</style>


<div class="content-wrapper" id="contentWrapper">

<div class="daq-container">

<!-- =====================================================
     HEADER
     ===================================================== -->

<div class="daq-header">

<div>

<h1 class="daq-title">
    PYNQ-Z1 Data Acquisition System
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
    id="debugToggle"
    title="Toggle debug mode">

    <i class="fas fa-bug"></i>

</button>

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

<div class="col-lg-3 col-md-6">

<div class="kpi-card">

<div class="kpi-icon kpi-green">
<i class="fas fa-wave-square"></i>
</div>

<div>

<div class="kpi-label">
ADC
</div>

<div
    class="kpi-value"
    id="adcStatus">

Running

</div>

</div>

</div>

</div>


<div class="col-lg-3 col-md-6">

<div class="kpi-card">

<div class="kpi-icon kpi-blue">
<i class="fas fa-tachometer-alt"></i>
</div>

<div>

<div class="kpi-label">
Sample Rate
</div>

<div
    class="kpi-value"
    id="sampleRate">

200 kSPS

</div>

</div>

</div>

</div>


<div class="col-lg-3 col-md-6">

<div class="kpi-card">

<div class="kpi-icon kpi-orange">
<i class="fas fa-microchip"></i>
</div>

<div>

<div class="kpi-label">
FPGA Temperature
</div>

<div
    class="kpi-value"
    id="fpgaTemp">

42 °C

</div>

</div>

</div>

</div>


<div class="col-lg-3 col-md-6">

<div class="kpi-card">

<div class="kpi-icon kpi-purple">
<i class="fas fa-shield-alt"></i>
</div>

<div>

<div class="kpi-label">
Connection
</div>

<div
    class="kpi-value">

TLS Active

</div>

</div>

</div>

</div>

</div>


<!-- =====================================================
     MAIN AREA
     ===================================================== -->

<div class="row">


<!-- SCOPE -->

<div class="col-lg-8">

<div class="daq-card">

<div class="daq-card-header">

<h3 class="daq-card-title">

<i class="fas fa-chart-line"></i>

Real-Time ADC

</h3>

<div>

<button
    class="daq-button button-success"
    id="startButton">

<i class="fas fa-play"></i>
Start

</button>

<button
    class="daq-button button-danger"
    id="stopButton">

<i class="fas fa-stop"></i>
Stop

</button>

</div>

</div>

<div class="daq-card-body">

<div
    id="scopeChart"
    class="scope-container">
</div>

</div>

</div>

</div>


<!-- FPGA MODULES -->

<div class="col-lg-4">

<div class="daq-card">

<div class="daq-card-header">

<h3 class="daq-card-title">

<i class="fas fa-microchip"></i>

FPGA Modules

</h3>

<span class="config-value debug-only">
<?= count($fpga_modules) ?> modules (all shown)
</span>

</div>

<div class="daq-card-body">

<?php foreach ($fpga_modules as $m) { ?>

<div class="module-row<?= $m['primary'] ? '' : ' debug-only' ?>">

<div class="module-name">

<div class="module-icon">
<i class="fas <?= $m['icon'] ?>"></i>
</div>

<?= $m['name'] ?>

</div>

<span class="module-status status-<?= $m['state'] ?>">
<?= $m['label'] ?>
</span>

<div class="module-debug-line">
BASE <b><?= $m['base'] ?></b> &nbsp;|&nbsp; IRQ <b><?= $m['irq'] ?></b> &nbsp;|&nbsp; STATUS <b><?= $m['status'] ?></b> &nbsp;|&nbsp; RESET <b><?= $m['reset'] ?></b>
</div>

</div>

<?php } ?>

</div>

</div>

</div>

</div>


<!-- =====================================================
     DEBUG CONSOLE (all modules, debug mode only)
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
     CONFIGURATION
     ===================================================== -->

<div class="row">


<div class="col-lg-6">

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

<option value="1">
x1
</option>

<option value="2">
x2
</option>

<option value="4">
x4
</option>

<option value="8" selected>
x8
</option>

<option value="16">
x16
</option>

<option value="32">
x32
</option>

</select>

</div>


<div class="config-row">

<span class="config-label">
ADC Channels
</span>

<select
    class="daq-select"
    id="channelCount">

<option value="4">
4 Channels
</option>

<option value="8">
8 Channels
</option>

<option value="16" selected>
16 Channels
</option>

</select>

</div>


<div class="config-row">

<span class="config-label">
ADC Resolution
</span>

<select
    class="daq-select"
    id="adcResolution">

<option>
12-bit
</option>

<option selected>
16-bit
</option>

<option>
18-bit
</option>

</select>

</div>


<div class="config-row">

<span class="config-label">
Buffer Size
</span>

<select
    class="daq-select"
    id="bufferSize">

<option>
1024 Samples
</option>

<option selected>
4096 Samples
</option>

<option>
8192 Samples
</option>

<option>
16384 Samples
</option>

</select>

</div>


<div class="config-row">

<span class="config-label">
Trigger

</span>

<select
    class="daq-select"
    id="triggerMode">

<option selected>
Disabled
</option>

<option>
Rising Edge
</option>

<option>
Falling Edge
</option>

</select>

</div>


</div>

</div>

</div>


<!-- SPI -->

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


<div class="module-row">

<div class="module-name">

<div class="module-icon">
<i class="fas fa-microchip"></i>
</div>

SPI-1

</div>

<span class="module-status status-running">
Active
</span>

</div>


<div class="config-row">

<span class="config-label">
Clock
</span>

<select class="daq-select">

<option>
1 MHz
</option>

<option selected>
5 MHz
</option>

<option>
10 MHz
</option>

<option>
20 MHz
</option>

</select>

</div>


<div class="config-row">

<span class="config-label">
Mode
</span>

<select class="daq-select">

<option selected>
Mode 0
</option>

<option>
Mode 1
</option>

<option>
Mode 2
</option>

<option>
Mode 3
</option>

</select>

</div>


<div class="module-row">

<div class="module-name">

<div class="module-icon">
<i class="fas fa-microchip"></i>
</div>

SPI-2

</div>

<span class="module-status status-running">
Active
</span>

</div>


<div class="config-row">

<span class="config-label">
Clock
</span>

<span class="config-value">
10 MHz
</span>

</div>


<div class="config-row">

<span class="config-label">
Mode
</span>

<span class="config-value">
Mode 0
</span>

</div>


<div class="module-row">

<div class="module-name">

<div class="module-icon">
<i class="fas fa-microchip"></i>
</div>

SPI-3

</div>

<span class="module-status status-idle">
Idle
</span>

</div>


<div class="config-row">

<span class="config-label">
Clock
</span>

<span class="config-value">
5 MHz
</span>

</div>


</div>

</div>

</div>

</div>


<!-- =====================================================
     CHANNELS
     ===================================================== -->

<div class="daq-card">

<div class="daq-card-header">

<h3 class="daq-card-title">

<i class="fas fa-grip-horizontal"></i>

Live ADC Channels

</h3>

<span class="config-value">
16 Channels
</span>

</div>

<div class="daq-card-body">

<div class="row">

<?php

for($i = 1; $i <= 16; $i++)
{

?>

<div class="col-xl-3 col-lg-3 col-md-4 col-6">

<div class="channel-card">

<div class="channel-top">

<span class="channel-name">

CH<?= $i ?>

</span>

<span class="channel-status"></span>

</div>

<div
    class="channel-value"
    id="ch<?= $i ?>">

0.000

</div>

<div class="channel-meta">

<span class="channel-unit">

mG

</span>

<span
    class="channel-decimation"
    id="chDec<?= $i ?>"
    title="Active decimation factor">

&divide;8

</span>

</div>

</div>

</div>

<?php

}

?>

</div>

</div>

</div>


<!-- =====================================================
     SYSTEM INFORMATION
     ===================================================== -->

<div class="row">


<div class="col-lg-4">

<div class="daq-card">

<div class="daq-card-header">

<h3 class="daq-card-title">

<i class="fas fa-heartbeat"></i>

Device Health

</h3>

<span class="module-status status-running" id="healthOverall">
Healthy
</span>

</div>

<div class="daq-card-body">


<div class="health-item">

<div class="health-top">
<span class="health-label"><i class="fas fa-microchip"></i>FPGA Temperature</span>
<span class="health-value" id="fpgaTempValue">42 &deg;C</span>
</div>

<div class="health-track">
<div class="health-fill health-ok" id="fpgaTempBar" style="width:47%"></div>
</div>

</div>


<div class="health-item">

<div class="health-top">
<span class="health-label"><i class="fas fa-tasks"></i>CPU Load</span>
<span class="health-value" id="cpuUsage">18%</span>
</div>

<div class="health-track">
<div class="health-fill health-ok" id="cpuBar" style="width:18%"></div>
</div>

</div>


<div class="health-item">

<div class="health-top">
<span class="health-label"><i class="fas fa-memory"></i>Memory Used</span>
<span class="health-value" id="memoryUsage">42%</span>
</div>

<div class="health-track">
<div class="health-fill health-ok" id="memBar" style="width:42%"></div>
</div>

</div>


<div class="health-item">

<div class="health-top">
<span class="health-label"><i class="fas fa-bolt"></i>Core Voltage (1.0V)</span>
<span class="health-value" id="coreVoltage">1.01 V</span>
</div>

<div class="health-track">
<div class="health-fill health-ok" style="width:98%"></div>
</div>

</div>


<div class="config-row">
<span class="config-label">FPGA</span>
<span class="config-value">Zynq-7020</span>
</div>

<div class="config-row">
<span class="config-label">Linux</span>
<span class="config-value">PYNQ</span>
</div>

<div class="config-row">
<span class="config-label">Uptime</span>
<span class="config-value" id="uptime">02d 14h 32m</span>
</div>


</div>

</div>

</div>


<div class="col-lg-4">

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


<div class="col-lg-4">

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
16 CH
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
     ACTIVE CONFIGURATION SNAPSHOT
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
     EVENT LOG
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

<th>
Time
</th>

<th>
Level
</th>

<th>
Module
</th>

<th>
Message
</th>

</tr>

</thead>

<tbody id="eventLog">

<tr>

<td>
10:52:01
</td>

<td>
<span class="module-status status-running">
INFO
</span>
</td>

<td>
ADC
</td>

<td>
Acquisition started
</td>

</tr>

<tr>

<td>
10:52:02
</td>

<td>
<span class="module-status status-running">
INFO
</span>
</td>

<td>
DMA
</td>

<td>
Buffer ready
</td>

</tr>

<tr>

<td>
10:52:04
</td>

<td>
<span class="module-status status-running">
INFO
</span>
</td>

<td>
Network
</td>

<td>
TLS connection established
</td>

</tr>

</tbody>

</table>

</div>

</div>


</div>

</div>


<!-- =====================================================
     PLOTLY
     ===================================================== -->

<script src="https://cdn.plot.ly/plotly-2.35.2.min.js"></script>

<script>

/* =========================================================
   REAL TIME SCOPE
   ========================================================= */

var scopeX = [];
var scopeY = [];

for(var i = 0; i < 500; i++)
{
    scopeX.push(i);
    scopeY.push(0);
}

function scopeColors()
{
    const dark =
        document.body.classList.contains('daq-dark');

    return {
        grid: dark ? '#263247' : '#dfe5ec',
        font: dark ? '#94a3b8' : '#718096'
    };
}

function scopeLayout()
{
    const c = scopeColors();

    return {

        margin: {
            l: 45,
            r: 15,
            t: 10,
            b: 35
        },

        xaxis: {
            title: 'Samples',
            gridcolor: c.grid,
            color: c.font
        },

        yaxis: {
            title: 'mG',
            gridcolor: c.grid,
            color: c.font
        },

        paper_bgcolor: 'transparent',

        plot_bgcolor: 'transparent',

        showlegend: false

    };
}

Plotly.newPlot(
    'scopeChart',
    [{
        x: scopeX,
        y: scopeY,
        mode: 'lines',
        line: {
            width: 1.5
        },
        name: 'CH1'
    }],
    scopeLayout(),
    {
        responsive: true,
        displaylogo: false
    }
);


/* =========================================================
   DARK / LIGHT MODE
   ========================================================= */

const themeButton =
    document.getElementById('themeToggle');

const savedTheme =
    localStorage.getItem('daqTheme');

if(savedTheme === 'dark')
{
    document.body.classList.add('daq-dark');

    themeButton.innerHTML =
        '<i class="fas fa-sun"></i>';
}

themeButton.addEventListener(
    'click',
    function()
    {

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
            dark
            ? '<i class="fas fa-sun"></i>'
            : '<i class="fas fa-moon"></i>';

        Plotly.relayout(
            'scopeChart',
            scopeLayout()
        );

    }
);


/* =========================================================
   DEBUG MODE
   Reveals the full module list, per-module register/status
   lines, and the Debug Console table. Persisted so a debug
   session survives a page reload while diagnosing a fault.
   ========================================================= */

const debugButton =
    document.getElementById('debugToggle');

const savedDebug =
    localStorage.getItem('daqDebug');

if(savedDebug === 'on')
{
    document.body.classList.add('debug-mode');
    debugButton.classList.add('is-active');
}

debugButton.addEventListener(
    'click',
    function()
    {

        document.body.classList.toggle(
            'debug-mode'
        );

        const debugOn =
            document.body.classList.contains(
                'debug-mode'
            );

        localStorage.setItem(
            'daqDebug',
            debugOn ? 'on' : 'off'
        );

        debugButton.classList.toggle(
            'is-active',
            debugOn
        );

    }
);


/* =========================================================
   CHANNEL DECIMATION DISPLAY
   Every channel card shows the decimation factor currently
   applied to its data path, kept in sync with the
   Acquisition Configuration panel.
   ========================================================= */

function applyDecimationToChannels(rawValue)
{
    const label =
        '\u00F7' + rawValue;

    for(let i = 1; i <= 16; i++)
    {
        const el =
            document.getElementById('chDec' + i);

        if(el)
        {
            el.innerText = label;
        }
    }

    const activeDec =
        document.getElementById('activeDecimation');

    if(activeDec)
    {
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
    function()
    {

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

        /*
         * Later replace with:
         *
         * fetch('/api/config',{
         *     method:'POST',
         *     headers:{
         *         'Content-Type':
         *             'application/json'
         *     },
         *     body:
         *         JSON.stringify(configuration)
         * });
         */

        // Reflect the new settings on every channel card
        applyDecimationToChannels(decimationSelect.value);

        // Reflect the new settings in the Active Configuration snapshot
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

        alert(
            'Configuration saved'
        );

    }
);


// Initialise channel decimation badges from the current
// Acquisition Configuration selection on page load.
applyDecimationToChannels(
    document.getElementById('decimation').value
);


/* =========================================================
   START / STOP
   ========================================================= */

document
.getElementById('startButton')
.addEventListener(
    'click',
    function()
    {

        document.getElementById(
            'adcStatus'
        ).innerText = 'Running';

        console.log(
            'ADC START'
        );

    }
);


document
.getElementById('stopButton')
.addEventListener(
    'click',
    function()
    {

        document.getElementById(
            'adcStatus'
        ).innerText = 'Stopped';

        console.log(
            'ADC STOP'
        );

    }
);


/* =========================================================
   CLEAR LOG
   ========================================================= */

document
.getElementById('clearLog')
.addEventListener(
    'click',
    function()
    {

        document.getElementById(
            'eventLog'
        ).innerHTML = '';

    }
);


/* =========================================================
   EXAMPLE REAL-TIME DATA
   Replace with WebSocket later
   ========================================================= */

setInterval(
    function()
    {

        for(
            let i = 1;
            i <= 16;
            i++
        )
        {

            const value =
                (
                    Math.random() * 20 - 10
                ).toFixed(3);

            const element =
                document.getElementById(
                    'ch' + i
                );

            if(element)
            {
                element.innerText =
                    value;
            }

        }

    },
    500
);


/* =========================================================
   EXAMPLE DEVICE HEALTH UPDATES
   Replace with real readings (sysfs, XADC, /proc, etc).
   Bars recolor green/orange/red as thresholds are crossed.
   ========================================================= */

function setHealthBar(barId, valueId, percent, unitSuffix, warnAt, critAt)
{
    const bar =
        document.getElementById(barId);

    const val =
        document.getElementById(valueId);

    if(!bar || !val)
    {
        return;
    }

    bar.style.width = percent + '%';

    bar.classList.remove(
        'health-ok', 'health-warn', 'health-crit'
    );

    let cssClass = 'health-ok';

    if(percent >= critAt)
    {
        cssClass = 'health-crit';
    }
    else if(percent >= warnAt)
    {
        cssClass = 'health-warn';
    }

    bar.classList.add(cssClass);

    val.innerText =
        percent.toFixed(0) + unitSuffix;

    return cssClass;
}

setInterval(
    function()
    {

        const cpu =
            18 + Math.random() * 15;

        const mem =
            42 + Math.random() * 10;

        const tempC =
            40 + Math.random() * 10;

        const tempPercent =
            (tempC / 90) * 100;

        setHealthBar('cpuBar', 'cpuUsage', cpu, '%', 70, 90);

        setHealthBar('memBar', 'memoryUsage', mem, '%', 75, 90);

        const tempClass =
            setHealthBar(
                'fpgaTempBar',
                'fpgaTempValue',
                tempPercent,
                '',
                (60 / 90) * 100,
                (75 / 90) * 100
            );

        document.getElementById('fpgaTempValue').innerText =
            tempC.toFixed(0) + ' \u00B0C';

        const overall =
            document.getElementById('healthOverall');

        if(overall)
        {
            overall.classList.remove(
                'status-running', 'status-idle', 'status-disabled'
            );

            if(tempClass === 'health-crit')
            {
                overall.classList.add('status-disabled');
                overall.innerText = 'Critical';
            }
            else if(tempClass === 'health-warn')
            {
                overall.classList.add('status-idle');
                overall.innerText = 'Warning';
            }
            else
            {
                overall.classList.add('status-running');
                overall.innerText = 'Healthy';
            }
        }

    },
    2000
);

</script>


<?php include 'footer.php'; ?>