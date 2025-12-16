<?php
include_once './header.php';
$form_config = ['heading' => 'ADC Test'];
$id = 1;
$row = ($id > 0 && isset($setting)) ? $setting->get_by_id($id)['data'] : null;
include_once './navbar.php';
include_once './sidebar.php';
?>

<div class="content-wrapper">
    <?php
    $heading = $form_config['heading'];
    $page_title = $id > 0 ? "Update $heading" : "New $heading";
    include_once './page_header.php';
    ?>

    <section class="content">
        <div class="container-fluid">
            <style>
                :root {
                    --bg: #f8f9fa;
                    --panel: #fff;
                    --text: #222;
                    --border: #dee2e6;
                    --accent: #1976d2;
                    --green: #28a745;
                    --red: #dc3545;
                    --orange: #fd7e14;
                }

                [data-theme="dark"] {
                    --bg: #0d1117;
                    --panel: #161b22;
                    --text: #e6edf3;
                    --border: #30363d;
                    --accent: #58a6ff;
                    --green: #3fb950;
                    --red: #f85149;
                    --orange: #d29922;
                }

                .scope-body {
                    font-family: 'Segoe UI', sans-serif;
                    background: var(--bg);
                    color: var(--text);
                    transition: 0.3s;
                }

                .scope-container {
                    display: flex;
                    gap: 20px;
                    max-width: 1920px;
                    margin: auto;
                    padding: 20px;
                }

                .scope-panel {
                    background: var(--panel);
                    border-radius: 12px;
                    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.2);
                    padding: 24px;
                    border: 1px solid var(--border);
                }

                .scope-left {
                    flex: 0 0 440px;
                    max-height: 90vh;
                    overflow-y: auto;
                }

                .scope-right {
                    flex: 1;
                    min-width: 0;
                }

                .scope-h1 {
                    margin: 0 0 20px;
                    color: var(--accent);
                    font-size: 26px;
                    font-weight: 600;
                }

                .scope-h2 {
                    margin: 0 0 16px;
                    color: var(--text);
                    font-size: 22px;
                    font-weight: 600;
                }

                .scope-controls {
                    display: flex;
                    flex-wrap: wrap;
                    gap: 10px;
                    margin-bottom: 20px;
                }

                .scope-btn,
                .scope-select,
                .scope-input {
                    padding: 10px 18px;
                    border: none;
                    border-radius: 8px;
                    font-size: 14px;
                    cursor: pointer;
                    background: var(--border);
                    color: var(--text);
                    font-weight: 500;
                    transition: 0.2s;
                }

                .scope-btn:hover {
                    background: var(--accent);
                    color: white;
                    transform: translateY(-1px);
                }

                .scope-status {
                    padding: 14px;
                    border-radius: 8px;
                    text-align: center;
                    font-weight: 600;
                    margin-top: 16px;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    gap: 8px;
                    transition: all 0.3s ease;
                }

                .scope-connected {
                    background: linear-gradient(135deg, #d4edda, #c3e6cb);
                    color: #155724;
                    box-shadow: 0 0 10px rgba(40, 167, 69, 0.3);
                }

                .scope-disconnected {
                    background: linear-gradient(135deg, #f8d7da, #f5c6cb);
                    color: #721c24;
                    box-shadow: 0 0 10px rgba(220, 53, 69, 0.3);
                }

                .scope-table {
                    width: 100%;
                    border-collapse: collapse;
                    font-size: 13px;
                    margin-top: 12px;
                }

                .scope-table th,
                .scope-table td {
                    padding: 10px 8px;
                    text-align: center;
                    border: 1px solid var(--border);
                    transition: background 0.2s;
                }

                .scope-table th {
                    background: var(--accent);
                    color: white;
                    font-weight: 600;
                    font-size: 11px;
                    text-transform: uppercase;
                }

                .scope-table tbody tr:hover {
                    background: rgba(25, 118, 210, 0.1);
                    cursor: pointer;
                }

                .scope-table tr.scope-selected {
                    background: rgba(25, 118, 210, 0.3);
                    font-weight: 600;
                }

                .scope-dot {
                    display: inline-block;
                    width: 12px;
                    height: 12px;
                    border-radius: 50%;
                    transition: all 0.3s ease;
                }

                .scope-dot-green {
                    background: var(--green);
                    box-shadow: 0 0 12px var(--green);
                    animation: glow 2s infinite;
                }

                .scope-dot-orange {
                    background: var(--orange);
                    box-shadow: 0 0 12px var(--orange);
                    animation: glow 2s infinite;
                }

                .scope-dot-gray {
                    background: #888;
                    opacity: 0.4;
                }

                @keyframes glow {

                    0%,
                    100% {
                        opacity: 0.7
                    }

                    50% {
                        opacity: 1
                    }
                }

                #scopePlot {
                    width: 100%;
                    height: 600px;
                    border-radius: 8px;
                    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
                }

                .scope-info {
                    margin-top: 16px;
                    padding: 14px;
                    background: var(--border);
                    border-radius: 8px;
                    font-family: monospace;
                    font-size: 13px;
                    box-shadow: inset 0 1px 3px rgba(0,0,0,0.1);
                }

                .scope-theme-btn {
                    position: fixed;
                    top: 80px;
                    right: 20px;
                    z-index: 999;
                    padding: 12px 20px;
                    border-radius: 50px;
                    background: var(--panel);
                    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
                    border: 1px solid var(--border);
                    cursor: pointer;
                    font-size: 16px;
                    transition: transform 0.2s;
                }

                .scope-theme-btn:hover {
                    transform: scale(1.05);
                }

                .scope-stats {
                    display: grid;
                    grid-template-columns: 1fr 1fr;
                    gap: 10px;
                    margin: 20px 0;
                }

                .scope-stat {
                    padding: 12px;
                    background: var(--border);
                    border-radius: 8px;
                    transition: box-shadow 0.2s;
                }

                .scope-stat:hover {
                    box-shadow: 0 2px 8px rgba(0,0,0,0.15);
                }

                .scope-stat-label {
                    font-weight: 600;
                    color: var(--accent);
                    font-size: 11px;
                    text-transform: uppercase;
                    display: block;
                    margin-bottom: 4px;
                }

                .scope-stat-value {
                    font-size: 18px;
                    font-weight: 700;
                    color: var(--text);
                }

                .scope-y-controls {
                    display: flex;
                    gap: 10px;
                    align-items: center;
                    margin: 16px 0;
                    padding: 12px;
                    background: var(--border);
                    border-radius: 8px;
                }

                .scope-y-controls input {
                    width: 90px;
                    padding: 8px;
                    border: 1px solid var(--border);
                    background: var(--panel);
                    color: var(--text);
                    border-radius: 6px;
                    transition: border-color 0.2s;
                }

                .scope-y-controls input:focus {
                    border-color: var(--accent);
                    outline: none;
                }

                .scope-section {
                    font-size: 14px;
                    font-weight: 600;
                    color: var(--accent);
                    margin: 20px 0 12px;
                    text-transform: uppercase;
                }
            </style>

            <div class="scope-body" id="scopeBody">
                <button class="scope-theme-btn" id="themeBtn">🌙</button>

                <div class="scope-container">
                    <div class="scope-panel scope-left">
                        <h1 class="scope-h1">📊 Spicer 16-Ch DAQ</h1>

                        <div class="scope-controls">
                            <button class="scope-btn" id="refreshBtn">🔄 Refresh</button>
                        </div>

                        <div class="scope-section">System Statistics</div>
                        <div class="scope-stats">
                            <div class="scope-stat"><span class="scope-stat-label">Frames</span>
                                <div class="scope-stat-value" id="statFrames">0</div>
                            </div>
                            <div class="scope-stat"><span class="scope-stat-label">Rate</span>
                                <div class="scope-stat-value" id="statRate">0 Hz</div>
                            </div>
                            <div class="scope-stat"><span class="scope-stat-label">Active</span>
                                <div class="scope-stat-value" id="statActive">0/16</div>
                            </div>
                            <div class="scope-stat"><span class="scope-stat-label">Read</span>
                                <div class="scope-stat-value" id="statRead">0 ms</div>
                            </div>
                        </div>

                        <div class="scope-section">Y-Axis Range</div>
                        <div class="scope-y-controls">
                            <label>Min: <input type="number" id="yMin" value="-2000" step="100"></label>
                            <label>Max: <input type="number" id="yMax" value="2000" step="100"></label>
                            <button class="scope-btn" id="applyY">Apply</button>
                        </div>

                        <div class="scope-section">Display Controls</div>
                        <div class="scope-controls">
                            <select class="scope-select" id="samples">
                                <option value="2500">1 Frame (2500)</option>
                                <option value="5000">2 Frames (5000)</option>
                                <option value="10000" selected>4 Frames (10000)</option>
                                <option value="20000">8 Frames (20000)</option>
                            </select>
                            <button class="scope-btn" id="clear">Clear</button>
                            <button class="scope-btn" id="pause">⏸ Pause</button>
                            <button class="scope-btn" id="deselect">Deselect</button>
                        </div>

                        <div class="scope-section">Channel Monitor</div>
                        <table class="scope-table" id="chTable">
                            <thead>
                                <tr>
                                    <th>Ch</th>
                                    <th>Value</th>
                                    <th>Min</th>
                                    <th>Max</th>
                                    <th>Freq</th>
                                    <th>Amp</th>
                                    <th>●</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>

                        <div class="scope-status scope-disconnected" id="status"><span>🔌</span><span>Connecting...</span></div>
                    </div>

                    <div class="scope-panel scope-right">
                        <h2 class="scope-h2">Oscilloscope <span id="chTitle">—</span></h2>
                        <div class="scope-controls">
                            <label><input type="checkbox" id="autoY"> Auto Y</label>
                            <label><input type="checkbox" id="grid" checked> Grid</label>
                            <label><input type="checkbox" id="legend" checked> Legend</label>
                        </div>
                        <div id="scopePlot"></div>
                        <div class="scope-info" id="info">⏳ Waiting for data...</div>
                    </div>
                </div>
            </div>

            <script src="https://cdn.plot.ly/plotly-2.35.2.min.js"></script>
            <script>
                // Configuration
                const CONFIG = {
                    MAX_SAMPLES: 20000,
                    BLOCK_SIZE: 2500,
                    BASE_SAMPLE_RATE: 10000,
                    DECIMATION_FACTOR: 20,
                    EFFECTIVE_SAMPLE_RATE: 500,
                    COLORS: ['#1976d2','#e91e63','#4caf50','#ff9800','#9c27b0','#00bcd4','#f44336','#8bc34a','#ff5722','#607d8b','#795548','#cddc39','#009688','#ffc107','#673ab7','#03a9f4'],
                    RECONNECT_DELAY: 3000,
                    SIGNAL_VARIANCE_THRESHOLD: 10
                };

                // Buffer management
                const buffers = Array(16).fill().map(() => ({
                    data: [],
                    valid: false,
                    lastUpdate: 0,
                    hasNonZero: false,
                    statistics: { min: 0, max: 0, mean: 0, rms: 0, variance: 0, frequency: 0, amplitude: 0 }
                }));

                // State variables
                let selected = new Set();
                let paused = false;
                let frameCount = 0;
                let lastTime = 0;
                let dataRate = 0.0;
                let dataWebSocket = null;
                let yRange = { min: -2000, max: 2000 };
                let lastDisplayedSamples = 10000;

                // Theme management
                const body = document.getElementById('scopeBody');
                const theme = localStorage.getItem('theme') || 'light';
                if (theme === 'dark') {
                    body.setAttribute('data-theme', 'dark');
                    document.getElementById('themeBtn').textContent = '☀️';
                }
                document.getElementById('themeBtn').onclick = () => {
                    const dark = body.getAttribute('data-theme') === 'dark';
                    body.setAttribute('data-theme', dark ? '' : 'dark');
                    document.getElementById('themeBtn').textContent = dark ? '🌙' : '☀️';
                    localStorage.setItem('theme', dark ? 'light' : 'dark');
                    plot();
                };

                // Build channel table
                function buildChannelTable() {
                    const tbody = document.querySelector('#chTable tbody');
                    tbody.innerHTML = '';
                    
                    for (let i = 0; i < 16; i++) {
                        const tr = document.createElement('tr');
                        tr.innerHTML = `
                            <td><strong>CH${i}</strong></td>
                            <td id="v${i}">—</td>
                            <td id="n${i}">—</td>
                            <td id="x${i}">—</td>
                            <td id="f${i}">—</td>
                            <td id="a${i}">—</td>
                            <td><span class="scope-dot scope-dot-gray" id="d${i}"></span></td>
                        `;
                        tr.onclick = (e) => {
                            if (e.ctrlKey || e.metaKey) {
                                selected.has(i) ? selected.delete(i) : selected.add(i);
                            } else {
                                selected = new Set([i]);
                            }
                            updateSelection();
                            plot();
                        };
                        tbody.appendChild(tr);
                    }
                }

                // WebSocket connection
                function connectDataWebSocket() {
                    if (dataWebSocket && dataWebSocket.readyState === WebSocket.OPEN) {
                        return;
                    }

                    try {
                        const wsProtocol = location.protocol === 'https:' ? 'wss:' : 'ws:';
                        dataWebSocket = new WebSocket(wsProtocol + '//' + location.host + '/ws');
                        dataWebSocket.binaryType = 'arraybuffer';

                        dataWebSocket.onopen = () => {
                            updateStatus('🟢 Connected', 'scope-connected');
                        };

                        dataWebSocket.onmessage = handleDataMessage;
                        
                        dataWebSocket.onclose = () => {
                            updateStatus('🔴 Disconnected - Reconnecting...', 'scope-disconnected');
                            setTimeout(connectDataWebSocket, CONFIG.RECONNECT_DELAY);
                        };

                        dataWebSocket.onerror = (error) => {
                            updateStatus('⚠️ Connection error', 'scope-disconnected');
                        };
                    } catch (error) {
                        updateStatus('⚠️ Connection failed', 'scope-disconnected');
                        setTimeout(connectDataWebSocket, CONFIG.RECONNECT_DELAY);
                    }
                }

                // Message handler
                function handleDataMessage(event) {
                    if (paused) return;

                    if (event.data instanceof ArrayBuffer) {
                        processBinaryData(event.data);
                    }
                }

                // Binary data processing
                function processBinaryData(buffer) {
                    const now = performance.now();
                    if (lastTime > 0) {
                        dataRate = 1000 / (now - lastTime);
                    }
                    lastTime = now;
                    frameCount++;

                    try {
                        const result = parseBinaryPacket(buffer);
                        
                        if (!result || !result.blocks || result.blocks.length === 0) {
                            return;
                        }

                        for (let ch = 0; ch < result.blocks.length && ch < 16; ch++) {
                            if (result.blocks[ch] && result.blocks[ch].length > 0) {
                                updateChannelBuffer(ch, result.blocks[ch], now);
                                updateChannelDisplay(ch);
                            }
                        }

                        plot();
                        updateInfoDisplay();

                    } catch (err) {
                        console.error('Error processing data:', err);
                    }
                }

                // Packet parser
                function parseBinaryPacket(buf) {
                    try {
                        const view = new DataView(buf);
                        const HEADER_SIZE = 34;
                        
                        if (buf.byteLength < HEADER_SIZE) {
                            return { blocks: [] };
                        }

                        const headerOffset = buf.byteLength - HEADER_SIZE;
                        const numChannels = view.getInt16(headerOffset, false);

                        if (numChannels < 1 || numChannels > 16) {
                            return parseWithDefaultChannels(buf, view, headerOffset);
                        }

                        const blockSizes = [];
                        for (let i = 0; i < 16; i++) {
                            const size = view.getInt16(headerOffset + 2 + (i * 2), false);
                            blockSizes.push(size);
                        }

                        const samples = [];
                        let dataOffset = 8;

                        for (let ch = 0; ch < numChannels; ch++) {
                            const blockSizeBytes = blockSizes[ch];
                            const numSamples = blockSizeBytes / 2;

                            if (blockSizeBytes < 0 || blockSizeBytes > 10000 || dataOffset + blockSizeBytes > headerOffset) {
                                samples.push(new Int16Array(0));
                                continue;
                            }

                            const arr = new Int16Array(numSamples);
                            
                            for (let i = 0; i < numSamples; i++) {
                                const value = view.getInt16(dataOffset, true);
                                // FIX: Handle first data point if it's corrupted
                                if (i === 0 && ch === 0) {
                                    // Check if first sample is outlier (debug)
                                    const nextVal = view.getInt16(dataOffset + 2, true);
                                    if (Math.abs(value - nextVal) > 1000) {
                                        arr[i] = nextVal; // Use second sample instead
                                    } else {
                                        arr[i] = (value < -32768 || value > 32767) ? 0 : value;
                                    }
                                } else {
                                    arr[i] = (value < -32768 || value > 32767) ? 0 : value;
                                }
                                dataOffset += 2;
                            }
                            
                            samples.push(arr);
                        }

                        return { blocks: samples };

                    } catch (error) {
                        return { blocks: [] };
                    }
                }

                // Fallback parser
                function parseWithDefaultChannels(buf, view, headerOffset) {
                    const samples = [];
                    let dataOffset = 8;
                    
                    for (let ch = 0; ch < 16; ch++) {
                        if (dataOffset + 5000 > headerOffset) break;
                        
                        const arr = new Int16Array(2500);
                        for (let i = 0; i < 2500; i++) {
                            const value = view.getInt16(dataOffset, true);
                            // FIX: Handle first data point
                            if (i === 0 && ch === 0) {
                                const nextVal = view.getInt16(dataOffset + 2, true);
                                if (Math.abs(value - nextVal) > 1000) {
                                    arr[i] = nextVal;
                                } else {
                                    arr[i] = value;
                                }
                            } else {
                                arr[i] = value;
                            }
                            dataOffset += 2;
                        }
                        samples.push(arr);
                    }
                    
                    return { blocks: samples };
                }

                // Buffer update
                function updateChannelBuffer(channel, block, timestamp) {
                    if (channel < 0 || channel >= 16 || !block || block.length === 0) return;
                    
                    const buf = buffers[channel];
                    
                    // Check for duplicate block
                    let isDuplicate = false;
                    if (buf.data.length >= CONFIG.BLOCK_SIZE && block.length === CONFIG.BLOCK_SIZE) {
                        const lastBlock = buf.data.slice(-CONFIG.BLOCK_SIZE);
                        isDuplicate = true;
                        for (let i = 0; i < CONFIG.BLOCK_SIZE; i++) {
                            if (lastBlock[i] !== block[i]) {
                                isDuplicate = false;
                                break;
                            }
                        }
                    }
                    
                    if (isDuplicate) {
                        buf.lastUpdate = timestamp;
                        return;
                    }
                    
                    // Fix first sample if it's an outlier
                    const fixedBlock = Array.from(block);
                    if (fixedBlock.length >= 2) {
                        // Check if first sample is outlier (jump > 1000 from second sample)
                        if (Math.abs(fixedBlock[0] - fixedBlock[1]) > 1000) {
                            fixedBlock[0] = fixedBlock[1];
                        }
                    }
                    
                    // Check if all zeros or flat signal
                    let allZeros = true;
                    let allSame = true;
                    const firstVal = fixedBlock[0];
                    for (let i = 0; i < fixedBlock.length; i++) {
                        if (fixedBlock[i] !== 0) allZeros = false;
                        if (fixedBlock[i] !== firstVal) allSame = false;
                        if (!allZeros && !allSame) break;
                    }
                    
                    if (allZeros || allSame) {
                        buf.data = fixedBlock;
                        buf.valid = true;
                        buf.lastUpdate = timestamp;
                        buf.hasNonZero = false;
                        buf.statistics = { min: 0, max: 0, mean: 0, rms: 0, variance: 0, frequency: 0, amplitude: 0 };
                        return;
                    }
                    
                    // Detect discontinuity
                    let insertGap = false;
                    if (buf.data.length > 0) {
                        const lastVal = buf.data[buf.data.length - 1];
                        const firstVal = fixedBlock[0];
                        const delta = Math.abs(lastVal - firstVal);
                        
                        let maxIncomingDelta = 0;
                        for (let i = 1; i < fixedBlock.length; i++) {
                            const d = Math.abs(fixedBlock[i] - fixedBlock[i - 1]);
                            if (d > maxIncomingDelta) maxIncomingDelta = d;
                        }
                        
                        if (maxIncomingDelta > 0 && delta > 5 * maxIncomingDelta) {
                            insertGap = true;
                        }
                    }
                    
                    const newData = fixedBlock;
                    
                    if (insertGap) {
                        buf.data.push(NaN);
                    }
                    
                    buf.data = [...buf.data, ...newData].slice(-CONFIG.MAX_SAMPLES);
                    buf.valid = true;
                    buf.lastUpdate = timestamp;

                    updateChannelStatistics(buf, fixedBlock);
                }

                // Improved frequency calculation
                function estimateFrequencyAndAmplitude(data) {
                    if (data.length < 500) return { frequency: 0, amplitude: 0 };
                    
                    // Filter out NaN values
                    const validData = data.filter(v => !isNaN(v));
                    if (validData.length < 500) return { frequency: 0, amplitude: 0 };
                    
                    // Calculate amplitude (peak-to-peak / 2)
                    let min = Infinity, max = -Infinity;
                    for (let i = 0; i < validData.length; i++) {
                        if (validData[i] < min) min = validData[i];
                        if (validData[i] > max) max = validData[i];
                    }
                    const amplitude = (max - min) / 2;
                    
                    // Check if signal is too flat to measure frequency
                    if (amplitude < 10) return { frequency: 0, amplitude };
                    
                    // Detrend the data (remove DC offset)
                    const mean = validData.reduce((a, b) => a + b, 0) / validData.length;
                    const detrended = validData.map(v => v - mean);
                    
                    // Find zero crossings with improved detection
                    const crossings = [];
                    let lastSign = Math.sign(detrended[0]);
                    
                    for (let i = 1; i < detrended.length; i++) {
                        const currentSign = Math.sign(detrended[i]);
                        
                        // Detect zero crossing (positive-going)
                        if (lastSign <= 0 && currentSign > 0) {
                            // Linear interpolation for more accurate crossing point
                            const t = -detrended[i-1] / (detrended[i] - detrended[i-1]);
                            const crossingIndex = (i - 1) + t;
                            crossings.push(crossingIndex);
                        }
                        lastSign = currentSign;
                    }
                    
                    if (crossings.length < 2) return { frequency: 0, amplitude };
                    
                    // Calculate periods between crossings
                    const periods = [];
                    for (let i = 1; i < crossings.length; i++) {
                        periods.push(crossings[i] - crossings[i-1]);
                    }
                    
                    // Remove outliers (periods that deviate by more than 50% from median)
                    const medianPeriod = periods.sort((a, b) => a - b)[Math.floor(periods.length / 2)];
                    const filteredPeriods = periods.filter(p => 
                        p > medianPeriod * 0.5 && p < medianPeriod * 1.5
                    );
                    
                    if (filteredPeriods.length === 0) return { frequency: 0, amplitude };
                    
                    // Calculate average period
                    const avgPeriodSamples = filteredPeriods.reduce((a, b) => a + b, 0) / filteredPeriods.length;
                    
                    // Calculate frequency: f = sample_rate / period_in_samples
                    const frequency = CONFIG.EFFECTIVE_SAMPLE_RATE / avgPeriodSamples;
                    
                    return { 
                        frequency: Math.min(frequency, CONFIG.EFFECTIVE_SAMPLE_RATE / 2), // Nyquist limit
                        amplitude 
                    };
                }

                // Statistics calculation
                function updateChannelStatistics(buf, block) {
                    if (!block || block.length === 0) return;
                    
                    let min = Infinity, max = -Infinity, sum = 0, sumSq = 0;
                    let validSamples = 0;
                    
                    for (let i = 0; i < block.length; i++) {
                        const val = block[i];
                        if (val >= -32768 && val <= 32767 && val !== -32768) {
                            if (val < min) min = val;
                            if (val > max) max = val;
                            sum += val;
                            sumSq += val * val;
                            validSamples++;
                        }
                    }
                    
                    if (validSamples === 0) {
                        buf.statistics = { min: 0, max: 0, mean: 0, rms: 0, variance: 0, frequency: 0, amplitude: 0 };
                        buf.hasNonZero = false;
                        return;
                    }
                    
                    const mean = sum / validSamples;
                    const variance = (sumSq / validSamples) - (mean * mean);
                    
                    // Estimate frequency and amplitude from all available data
                    const recentData = buf.data.slice(-5000); // Use 5 seconds of data for better accuracy
                    const { frequency, amplitude } = estimateFrequencyAndAmplitude(recentData);
                    
                    buf.statistics = {
                        min: min,
                        max: max,
                        mean: mean,
                        rms: Math.sqrt(sumSq / validSamples),
                        variance: variance,
                        frequency: frequency,
                        amplitude: amplitude
                    };
                    
                    buf.hasNonZero = variance > CONFIG.SIGNAL_VARIANCE_THRESHOLD;
                }

                // Channel display update
                function updateChannelDisplay(channel) {
                    if (channel < 0 || channel >= 16) return;
                    
                    const buf = buffers[channel];
                    const stats = buf.statistics;
                    
                    if (!buf.valid || buf.data.length === 0) return;
                    
                    const lastValue = buf.data[buf.data.length - 1];
                    
                    const vEl = document.getElementById(`v${channel}`);
                    const nEl = document.getElementById(`n${channel}`);
                    const xEl = document.getElementById(`x${channel}`);
                    const fEl = document.getElementById(`f${channel}`);
                    const aEl = document.getElementById(`a${channel}`);
                    
                    if (vEl) vEl.textContent = isNaN(lastValue) ? '—' : lastValue.toFixed(0);
                    if (nEl) nEl.textContent = stats.min.toFixed(0);
                    if (xEl) xEl.textContent = stats.max.toFixed(0);
                    if (fEl) fEl.textContent = stats.frequency > 0.1 ? stats.frequency.toFixed(2) + ' Hz' : '—';
                    if (aEl) aEl.textContent = stats.amplitude > 0 ? stats.amplitude.toFixed(1) : '—';

                    const dot = document.getElementById(`d${channel}`);
                    if (dot) {
                        if (!buf.valid) {
                            dot.className = 'scope-dot scope-dot-gray';
                            dot.title = 'No data';
                        } else if (buf.hasNonZero) {
                            dot.className = 'scope-dot scope-dot-green';
                            dot.title = `Active signal`;
                        } else {
                            dot.className = 'scope-dot scope-dot-orange';
                            dot.title = `Flat signal`;
                        }
                    }
                }

                // Get samples for display
                function getSamples(ch, n) {
                    if (ch < 0 || ch >= 16) return [];
                    
                    const buf = buffers[ch];
                    if (!buf.valid || buf.data.length === 0) return [];
                    
                    return buf.data.slice(-n);
                }

                // Plot function
                function plot() {
                    if (!selected.size) {
                        Plotly.react('scopePlot', [], { 
                            title: 'Select channels to display',
                            paper_bgcolor: 'rgba(0,0,0,0)',
                            plot_bgcolor: 'rgba(0,0,0,0)',
                            font: { color: getComputedStyle(body).getPropertyValue('--text') }
                        });
                        return;
                    }

                    const requestedSamples = parseInt(document.getElementById('samples').value);
                    const traces = [];
                    let traceIndex = 0;
                    let maxAvailableSamples = 0;

                    // Determine actual samples to display
                    for (const ch of selected) {
                        const availableSamples = buffers[ch].data.length;
                        if (availableSamples > maxAvailableSamples) {
                            maxAvailableSamples = availableSamples;
                        }
                    }

                    const samplesToDisplay = Math.min(requestedSamples, maxAvailableSamples);
                    lastDisplayedSamples = samplesToDisplay;

                    for (const ch of selected) {
                        const y = getSamples(ch, samplesToDisplay);
                        if (!y.length) continue;
                        
                        const hasRealData = buffers[ch].hasNonZero;

                        traces.push({
                            x: Array.from({length: y.length}, (_, i) => i),
                            y: y,
                            name: `CH ${ch}${hasRealData ? '' : ' (flat)'}`,
                            mode: 'lines',
                            line: { 
                                color: CONFIG.COLORS[traceIndex % CONFIG.COLORS.length], 
                                width: 2.5
                            },
                            connectgaps: false
                        });
                        traceIndex++;
                    }

                    const autoY = document.getElementById('autoY').checked;
                    
                    const title = `${traces.length} channel${traces.length > 1 ? 's' : ''} • ${dataRate.toFixed(1)} Hz • ${samplesToDisplay} samples`;

                    const yaxisConfig = {
                        title: 'ADC Value', 
                        showgrid: document.getElementById('grid').checked,
                        gridcolor: 'rgba(128,128,128,0.2)'
                    };

                    if (!autoY) {
                        yaxisConfig.range = [yRange.min, yRange.max];
                        yaxisConfig.autorange = false;
                    }

                    const layout = {
                        title: title,
                        xaxis: { 
                            title: 'Sample', 
                            showgrid: document.getElementById('grid').checked,
                            gridcolor: 'rgba(128,128,128,0.2)'
                        },
                        yaxis: yaxisConfig,
                        showlegend: document.getElementById('legend').checked,
                        paper_bgcolor: 'rgba(0,0,0,0)',
                        plot_bgcolor: 'rgba(0,0,0,0)',
                        font: { color: getComputedStyle(body).getPropertyValue('--text') },
                        margin: { l: 60, r: 30, t: 50, b: 50 }
                    };

                    Plotly.react('scopePlot', traces, layout, {responsive: true});
                }

                // UI update functions
                function updateSelection() {
                    document.querySelectorAll('#chTable tbody tr').forEach((r, i) => {
                        r.classList.toggle('scope-selected', selected.has(i));
                    });
                    
                    const list = Array.from(selected).sort((a, b) => a - b);
                    document.getElementById('chTitle').textContent = list.length ? 
                        `CH ${list.join(', ')}` : '—';
                }

                function updateInfoDisplay() {
                    const active = buffers.filter(b => b.valid && b.hasNonZero).length;
                    
                    const infoText = `Frames: ${frameCount} • Rate: ${dataRate.toFixed(1)} Hz • Active: ${active}/16 • Displaying: ${lastDisplayedSamples} samples`;
                    document.getElementById('info').textContent = infoText;

                    document.getElementById('statFrames').textContent = frameCount;
                    document.getElementById('statRate').textContent = dataRate.toFixed(1) + ' Hz';
                    document.getElementById('statActive').textContent = `${active}/16`;
                    document.getElementById('statRead').textContent = '0.0 ms'; // Update if API provides
                }

                function updateStatus(message, type) {
                    const status = document.getElementById('status');
                    if (status) {
                        status.innerHTML = `<span>${message.split(' ')[0]}</span><span>${message.substring(message.indexOf(' ') + 1)}</span>`;
                        status.className = `scope-status ${type}`;
                    }
                }

                // System statistics update (if API exists)
                async function updateSystemStats() {
                    try {
                        const response = await fetch('/api/stats');
                        const stats = await response.json();
                        
                        document.getElementById('statFrames').textContent = stats.frames || 0;
                        document.getElementById('statRate').textContent = (stats.frame_rate || 0).toFixed(1) + ' Hz';
                        document.getElementById('statActive').textContent = `${stats.active || 0}/16`;
                        document.getElementById('statRead').textContent = (stats.read_time_ms || 0).toFixed(1) + ' ms';
                        
                    } catch (error) {
                        // Stats not available
                    }
                }

                // Control event handlers
                document.getElementById('samples').onchange = plot;
                document.getElementById('autoY').onchange = plot;
                document.getElementById('grid').onchange = plot;
                document.getElementById('legend').onchange = plot;

                document.getElementById('applyY').onclick = () => {
                    const minVal = parseInt(document.getElementById('yMin').value);
                    const maxVal = parseInt(document.getElementById('yMax').value);
                    
                    if (isNaN(minVal) || isNaN(maxVal)) {
                        alert('Please enter valid numbers for Y-axis range');
                        return;
                    }
                    
                    if (minVal >= maxVal) {
                        alert('Y-Min must be less than Y-Max');
                        return;
                    }
                    
                    yRange.min = minVal;
                    yRange.max = maxVal;
                    plot();
                };

                document.getElementById('clear').onclick = () => {
                    buffers.forEach(buf => {
                        buf.data = [];
                        buf.valid = false;
                        buf.hasNonZero = false;
                    });
                    frameCount = 0;
                    dataRate = 0;
                    plot();
                    updateInfoDisplay();
                };

                document.getElementById('pause').onclick = function() { 
                    paused = !paused; 
                    this.textContent = paused ? '▶️ Resume' : '⏸ Pause'; 
                    this.style.background = paused ? 'var(--orange)' : '';
                    this.style.color = paused ? 'white' : '';
                };

                document.getElementById('deselect').onclick = () => { 
                    selected.clear(); 
                    updateSelection(); 
                    plot(); 
                };

                document.getElementById('refreshBtn').onclick = () => {
                    location.reload();
                };

                // Initialize
                function init() {
                    buildChannelTable();
                    connectDataWebSocket();
                    setInterval(updateSystemStats, 2000);
                    plot();
                }

                if (document.readyState === 'loading') {
                    document.addEventListener('DOMContentLoaded', init);
                } else {
                    init();
                }
            </script>
        </div>
    </section>
</div>

<?php include_once './footer.php'; ?>
</body>

</html>