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
    --daq-cyan: #06b6d4;

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

body.daq-dark .health-item {
    background: #111c31;
    border-color: var(--daq-border);
}

body.daq-dark .module-card {
    background: #111c31;
    border-color: var(--daq-border);
}

body.daq-dark .channel-card {
    background: #111c31;
}

body.daq-dark .channel-config-row {
    background: #111c31;
}

body.daq-dark .health-icon {
    box-shadow: 0 3px 12px rgba(0,0,0,.35);
}

body.daq-dark .health-pill-ok {
    color: #34d399;
    background: rgba(16,185,129,.16);
    border-color: rgba(16,185,129,.35);
}

body.daq-dark .health-pill-warn {
    color: #fbbf24;
    background: rgba(245,158,11,.16);
    border-color: rgba(245,158,11,.38);
}

body.daq-dark .health-pill-crit {
    color: #f87171;
    background: rgba(239,68,68,.16);
    border-color: rgba(239,68,68,.38);
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

/* DEBUG MODE - always active */

.debug-badge {

    display: inline-flex;

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

.debug-only {
    display: block;
}

body.debug-mode span.debug-only {
    display: inline;
}

/* =========================================================
   FPGA MODULE GRID
   ========================================================= */

.module-grid {

    display: grid;

    grid-template-columns:
        repeat(auto-fill, minmax(230px, 1fr));

    gap: 12px;
}

.module-card {

    position: relative;

    display: flex;

    flex-direction: column;

    gap: 10px;

    padding: 13px;

    background: var(--daq-bg);

    border: 1px solid var(--daq-border);

    border-radius: 12px;

    transition: transform .18s ease,
                border-color .18s ease,
                box-shadow .18s ease;

    overflow: hidden;
}

.module-card::before {

    content: '';

    position: absolute;

    top: 0;
    left: 0;
    right: 0;

    height: 2px;

    background: transparent;

    transition: background .18s ease;
}

.module-card:hover {

    transform: translateY(-2px);

    border-color: var(--daq-blue);

    box-shadow: 0 6px 18px rgba(37,99,235,.12);
}

.module-card:hover::before {

    background: linear-gradient(
        90deg,
        var(--daq-blue),
        var(--daq-purple)
    );
}

.module-card-top {

    display: flex;

    align-items: center;

    gap: 9px;
}

.module-card-name {

    flex: 1 1 auto;

    font-size: 13px;

    font-weight: 600;

    color: var(--daq-text);

    white-space: nowrap;

    overflow: hidden;

    text-overflow: ellipsis;
}

.module-icon {

    flex: 0 0 30px;

    width: 30px;
    height: 30px;

    border-radius: 8px;

    display: flex;

    align-items: center;
    justify-content: center;

    background: rgba(37,99,235,.1);

    color: var(--daq-blue);

    font-size: 13px;
}

.module-status {

    flex: 0 0 auto;

    font-size: 11px;

    font-weight: 600;

    padding: 4px 8px;

    border-radius: 20px;

    white-space: nowrap;
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

.module-debug-line {

    display: grid;

    grid-template-columns: 1fr 1fr;

    gap: 4px 10px;

    padding: 8px 10px;

    background: var(--daq-card);

    border: 1px dashed var(--daq-border);

    border-radius: 7px;

    font-family: 'SFMono-Regular', 'Courier New', monospace;

    font-size: 10.5px;

    line-height: 1.5;

    color: var(--daq-muted);
}

.module-debug-line span {

    white-space: nowrap;

    overflow: hidden;

    text-overflow: ellipsis;
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

/* =========================================================
   LIVE CHANNELS
   ========================================================= */

.channel-card {

    background: var(--daq-bg);

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

/* =========================================================
   CHANNEL CONFIGURATION LIST
   ========================================================= */

.channel-config-list {

    display: flex;

    flex-direction: column;

    gap: 8px;
}

.channel-config-row {

    display: grid;

    grid-template-columns:
        60px
        100px
        110px
        1fr
        90px
        90px;

    align-items: center;

    gap: 12px;

    padding: 10px 12px;

    background: var(--daq-bg);

    border: 1px solid var(--daq-border);

    border-radius: 10px;

    transition: border-color .15s ease,
                box-shadow .15s ease;
}

.channel-config-row:hover {

    border-color: var(--daq-blue);

    box-shadow: 0 4px 12px rgba(37,99,235,.10);
}

.channel-config-tag {

    font-size: 11px;

    font-weight: 800;

    color: #fff;

    background: linear-gradient(135deg, #2563eb, #7c3aed);

    padding: 4px 8px;

    border-radius: 6px;

    text-align: center;

    letter-spacing: .5px;
}

.channel-config-field {

    display: flex;

    flex-direction: column;

    gap: 2px;

    min-width: 0;
}

.channel-config-field-label {

    font-size: 9px;

    font-weight: 700;

    color: var(--daq-muted);

    text-transform: uppercase;

    letter-spacing: .6px;
}

.channel-config-field-value {

    font-size: 12px;

    font-weight: 600;

    color: var(--daq-text);

    font-family: 'SFMono-Regular', 'Courier New', monospace;

    white-space: nowrap;

    overflow: hidden;

    text-overflow: ellipsis;
}

.channel-config-select {

    width: 100%;

    height: 30px;

    padding: 4px 6px;

    border: 1px solid var(--daq-border);

    border-radius: 6px;

    background: var(--daq-card);

    color: var(--daq-text);

    font-size: 11.5px;

    outline: none;
}

.channel-config-select:focus {

    border-color: var(--daq-blue);

    box-shadow: 0 0 0 2px rgba(37,99,235,.1);
}

/* =========================================================
   BUS BLOCKS  —  SPI / I2C
   ========================================================= */

.bus-block {

    position: relative;

    padding: 15px;

    border-radius: 12px;

    border: 1px solid var(--daq-border);

    background: var(--daq-bg);
}

.bus-block + .bus-block {

    margin-top: 14px;
}

.bus-block-header {

    display: flex;

    align-items: center;

    gap: 10px;

    margin-bottom: 12px;
}

.bus-block-icon {

    width: 34px;
    height: 34px;

    border-radius: 9px;

    display: flex;

    align-items: center;
    justify-content: center;

    color: #fff;

    font-size: 15px;

    box-shadow: 0 4px 12px rgba(15,23,42,.18);
}

.bus-block-icon-spi {
    background: linear-gradient(135deg, #2563eb, #06b6d4);
}

.bus-block-icon-i2c {
    background: linear-gradient(135deg, #7c3aed, #ec4899);
}

.bus-block-title {

    margin: 0;

    font-size: 13.5px;

    font-weight: 700;

    color: var(--daq-text);
}

.bus-block-sub {

    margin-top: 2px;

    font-size: 10.5px;

    color: var(--daq-muted);
}

.bus-device {

    display: flex;

    justify-content: space-between;

    align-items: center;

    padding: 9px 10px;

    margin-bottom: 6px;

    background: var(--daq-card);

    border: 1px solid var(--daq-border);

    border-radius: 9px;

    transition: border-color .15s ease;
}

.bus-device:last-child {
    margin-bottom: 0;
}

.bus-device:hover {
    border-color: var(--daq-blue);
}

.bus-device-left {

    display: flex;

    align-items: center;

    gap: 9px;

    min-width: 0;
}

.bus-device-icon {

    width: 26px;
    height: 26px;

    border-radius: 7px;

    display: flex;

    align-items: center;
    justify-content: center;

    background: rgba(37,99,235,.1);

    color: var(--daq-blue);

    font-size: 11px;
}

.bus-device-name {

    font-size: 12px;

    font-weight: 600;

    color: var(--daq-text);

    white-space: nowrap;

    overflow: hidden;

    text-overflow: ellipsis;
}

.bus-device-meta {

    font-size: 10px;

    color: var(--daq-muted);

    font-family: 'SFMono-Regular', 'Courier New', monospace;
}

.bus-device-right {

    display: flex;

    align-items: center;

    gap: 8px;

    flex: 0 0 auto;
}

.bus-device-value {

    font-size: 11.5px;

    font-weight: 700;

    color: var(--daq-text);

    font-family: 'SFMono-Regular', 'Courier New', monospace;
}

/* =========================================================
   DEVICE HEALTH
   ========================================================= */

.health-card {

    position: relative;
}

.health-card::before {

    content: '';

    position: absolute;

    top: 0;
    left: 0;
    right: 0;

    height: 3px;

    background: linear-gradient(
        90deg,
        var(--daq-green),
        var(--daq-blue),
        var(--daq-purple)
    );
}

.health-header-title {

    display: flex;

    align-items: center;

    gap: 10px;
}

.health-header-icon {

    width: 32px;
    height: 32px;

    border-radius: 9px;

    display: flex;

    align-items: center;
    justify-content: center;

    color: #fff;

    font-size: 14px;

    background: linear-gradient(135deg, #ef4444, #f97316);

    box-shadow: 0 4px 12px rgba(239,68,68,.35);
}

.health-header-icon i {

    animation: health-beat 1.8s ease-in-out infinite;
}

@keyframes health-beat {

    0%, 100% { transform: scale(1); }
    15%      { transform: scale(1.22); }
    30%      { transform: scale(1); }
    45%      { transform: scale(1.14); }
    60%      { transform: scale(1); }
}

.health-overall {

    display: inline-flex;

    align-items: center;

    gap: 6px;

    font-size: 11px;

    font-weight: 700;

    padding: 5px 11px;

    border-radius: 20px;

    letter-spacing: .3px;
}

.health-overall.status-running i {
    animation: health-pulse 2s infinite;
}

@keyframes health-pulse {

    0%, 100% { opacity: 1; }
    50%      { opacity: .35; }
}

.health-item {

    display: flex;

    align-items: center;

    gap: 10px;

    padding: 11px;

    margin-bottom: 10px;

    border: 1px solid var(--daq-border);

    border-radius: 12px;

    background: var(--daq-bg);

    transition: transform .18s ease,
                border-color .18s ease,
                box-shadow .18s ease;
}

.health-item:last-of-type {
    margin-bottom: 0;
}

.health-item:hover {

    transform: translateX(3px);

    border-color: var(--daq-blue);

    box-shadow: 0 4px 14px rgba(37,99,235,.10);
}

.health-icon {

    flex: 0 0 38px;

    width: 38px;
    height: 38px;

    border-radius: 10px;

    display: flex;

    align-items: center;
    justify-content: center;

    color: #fff;

    font-size: 15px;

    box-shadow: 0 4px 12px rgba(15,23,42,.18);
}

.health-icon-temp {
    background: linear-gradient(135deg, #f97316, #ef4444);
}

.health-icon-cpu {
    background: linear-gradient(135deg, #3b82f6, #6366f1);
}

.health-icon-mem {
    background: linear-gradient(135deg, #8b5cf6, #a855f7);
}

.health-icon-volt {
    background: linear-gradient(135deg, #10b981, #059669);
}

.health-content {

    flex: 1 1 auto;

    min-width: 0;
}

.health-top {

    display: flex;

    justify-content: space-between;

    align-items: baseline;

    margin-bottom: 6px;

    gap: 6px;
}

.health-label {

    font-size: 11.5px;

    font-weight: 600;

    color: var(--daq-muted);

    white-space: nowrap;

    overflow: hidden;

    text-overflow: ellipsis;
}

.health-value {

    font-size: 13px;

    font-weight: 800;

    color: var(--daq-text);

    font-family: 'SFMono-Regular', 'Courier New', monospace;

    white-space: nowrap;
}

.health-track {

    height: 7px;

    background: var(--daq-border);

    border-radius: 5px;

    overflow: hidden;

    box-shadow: inset 0 1px 2px rgba(15,23,42,.08);
}

.health-fill {

    height: 100%;

    border-radius: 5px;

    transition: width .4s ease, background .3s ease;
}

.health-ok {
    background: linear-gradient(90deg, #10b981, #34d399);
}

.health-warn {
    background: linear-gradient(90deg, #f59e0b, #fbbf24);
}

.health-crit {
    background: linear-gradient(90deg, #ef4444, #f87171);
}

.health-pill {

    flex: 0 0 auto;

    font-size: 9px;

    font-weight: 800;

    letter-spacing: .6px;

    padding: 4px 8px;

    border-radius: 20px;

    text-transform: uppercase;

    border: 1px solid transparent;
}

.health-pill-ok {

    color: #059669;

    background: rgba(16,185,129,.13);

    border-color: rgba(16,185,129,.32);
}

.health-pill-warn {

    color: #d97706;

    background: rgba(245,158,11,.13);

    border-color: rgba(245,158,11,.35);
}

.health-pill-crit {

    color: #dc2626;

    background: rgba(239,68,68,.13);

    border-color: rgba(239,68,68,.35);
}

.health-footer {

    margin-top: 14px;

    padding-top: 4px;

    border-top: 1px dashed var(--daq-border);
}

.health-footer .config-row {

    padding: 8px 0;
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

@media(max-width:992px) {

    .channel-config-row {

        grid-template-columns:
            60px 1fr 1fr;

        grid-auto-rows: auto;

        row-gap: 8px;
    }

}

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

    .module-grid {

        grid-template-columns:
            repeat(auto-fill, minmax(180px, 1fr));
    }

}

</style>


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
    <?php echo  $settings->app_name ?? 'Device Dashboard'?>
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


<div class="health-item">

<div class="health-icon health-icon-temp">
<i class="fas fa-thermometer-half"></i>
</div>

<div class="health-content">

<div class="health-top">

<span class="health-label">
FPGA Temperature
</span>

<span class="health-value" id="fpgaTempValue">
42 &deg;C
</span>

</div>

<div class="health-track">
<div class="health-fill health-ok" id="fpgaTempBar" style="width:47%"></div>
</div>

</div>

<span class="health-pill health-pill-ok" id="fpgaTempPill">
OK
</span>

</div>


<div class="health-item">

<div class="health-icon health-icon-cpu">
<i class="fas fa-microchip"></i>
</div>

<div class="health-content">

<div class="health-top">

<span class="health-label">
CPU Load
</span>

<span class="health-value" id="cpuUsage">
18%
</span>

</div>

<div class="health-track">
<div class="health-fill health-ok" id="cpuBar" style="width:18%"></div>
</div>

</div>

<span class="health-pill health-pill-ok" id="cpuPill">
OK
</span>

</div>


<div class="health-item">

<div class="health-icon health-icon-mem">
<i class="fas fa-memory"></i>
</div>

<div class="health-content">

<div class="health-top">

<span class="health-label">
Memory Used
</span>

<span class="health-value" id="memoryUsage">
42%
</span>

</div>

<div class="health-track">
<div class="health-fill health-ok" id="memBar" style="width:42%"></div>
</div>

</div>

<span class="health-pill health-pill-ok" id="memPill">
OK
</span>

</div>


<div class="health-item">

<div class="health-icon health-icon-volt">
<i class="fas fa-bolt"></i>
</div>

<div class="health-content">

<div class="health-top">

<span class="health-label">
Core Voltage (1.0V)
</span>

<span class="health-value" id="coreVoltage">
1.01 V
</span>

</div>

<div class="health-track">
<div class="health-fill health-ok" style="width:98%"></div>
</div>

</div>

<span class="health-pill health-pill-ok">
OK
</span>

</div>


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

<?php

for($i = 1; $i <= 16; $i++)
{

?>

<div class="channel-config-row">

<span class="channel-config-tag">
CH<?= $i ?>
</span>

<div class="channel-config-field">

<span class="channel-config-field-label">
Status
</span>

<span class="module-status status-running" style="display:inline-block;padding:2px 7px;font-size:10px;">
Enabled
</span>

</div>

<div class="channel-config-field">

<span class="channel-config-field-label">
Decimation
</span>

<span
    class="channel-config-field-value ch-config-dec"
    id="chCfgDec<?= $i ?>">

&divide;8

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

<?php

}

?>

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
<option value="16" selected>16 Channels</option>

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

    }
);


/* =========================================================
   CHANNEL DECIMATION DISPLAY
   Keeps the live channel badges AND the per-channel
   configuration list in sync with the Acquisition panel.
   ========================================================= */

function applyDecimationToChannels(rawValue)
{
    const label =
        '\u00F7' + rawValue;

    for(let i = 1; i <= 16; i++)
    {
        const live =
            document.getElementById('chDec' + i);

        if(live)
        {
            live.innerText = label;
        }

        const cfg =
            document.getElementById('chCfgDec' + i);

        if(cfg)
        {
            cfg.innerText = label;
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
    function()
    {

        document.getElementById(
            'eventLog'
        ).innerHTML = '';

    }
);


/* =========================================================
   EXAMPLE REAL-TIME CHANNEL DATA
   ========================================================= */

setInterval(
    function()
    {

        for(let i = 1; i <= 16; i++)
        {

            const value =
                (
                    Math.random() * 20 - 10
                ).toFixed(3);

            const element =
                document.getElementById('ch' + i);

            if(element)
            {
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
)
{
    const bar =
        document.getElementById(barId);

    const val =
        document.getElementById(valueId);

    const pill =
        document.getElementById(pillId);

    if(!bar)
    {
        return 'health-ok';
    }

    bar.style.width =
        Math.min(percent, 100).toFixed(1) + '%';

    bar.classList.remove(
        'health-ok', 'health-warn', 'health-crit'
    );

    let level = 'ok';

    if(percent >= critAt)
    {
        level = 'crit';
    }
    else if(percent >= warnAt)
    {
        level = 'warn';
    }

    bar.classList.add('health-' + level);

    if(val && displayText !== undefined && displayText !== null)
    {
        val.innerText = displayText;
    }

    if(pill)
    {
        pill.classList.remove(
            'health-pill-ok',
            'health-pill-warn',
            'health-pill-crit'
        );

        pill.classList.add('health-pill-' + level);

        pill.innerText =
            level === 'crit'
            ? 'CRIT'
            : (level === 'warn' ? 'WARN' : 'OK');
    }

    return 'health-' + level;
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

        if(kpiTemp)
        {
            kpiTemp.innerText =
                tempC.toFixed(0) + ' \u00B0C';
        }

        const overall =
            document.getElementById('healthOverall');

        const overallText =
            document.getElementById('healthOverallText');

        const overallIcon =
            document.getElementById('healthOverallIcon');

        if(overall && overallText && overallIcon)
        {
            overall.classList.remove(
                'status-running',
                'status-idle',
                'status-disabled'
            );

            let level = 'ok';

            if(
                tempClass === 'health-crit' ||
                cpuClass === 'health-crit' ||
                memClass === 'health-crit'
            )
            {
                level = 'crit';
            }
            else if(
                tempClass === 'health-warn' ||
                cpuClass === 'health-warn' ||
                memClass === 'health-warn'
            )
            {
                level = 'warn';
            }

            if(level === 'crit')
            {
                overall.classList.add('status-disabled');
                overallIcon.className = 'fas fa-circle-exclamation';
                overallText.innerText = 'Critical';
            }
            else if(level === 'warn')
            {
                overall.classList.add('status-idle');
                overallIcon.className = 'fas fa-triangle-exclamation';
                overallText.innerText = 'Warning';
            }
            else
            {
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