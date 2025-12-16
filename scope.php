<?php
include_once './header.php';
$form_config = ['heading' => 'ADC Test'];
$id = 1;
$row = ($id > 0 && isset($setting)) ? $setting->get_by_id($id)['data'] : null;
include_once './navbar.php';
include_once './sidebar.php';
?>

<link rel="stylesheet" href="assets/css/scope.css">
<div class="content-wrapper">
    <?php
    $heading = $form_config['heading'];
    $page_title = $id > 0 ? "Update $heading" : "New $heading";
    include_once './page_header.php';
    ?>

    <section class="content">
        <div class="container-fluid">
            <div class="scope-body" id="scopeBody">
                <!-- Header -->
                <div class="scope-header">
                    <div class="header-left">
                        <h1>📊 Spicer DAQ Oscilloscope</h1>
                        <p class="subtitle">16-Channel Real-Time Data Acquisition</p>
                    </div>
                    <div class="header-right">
                        <div class="status-indicator" id="statusIndicator">
                            <span class="status-dot"></span>
                            <span class="status-text" id="statusText">Connecting...</span>
                        </div>
                        <button class="btn btn-primary" id="refreshBtn">
                            <i class="fas fa-redo"></i> Refresh
                        </button>
                    </div>
                </div>

                <!-- Main Content -->
                <div class="scope-main">
                    <!-- Left Panel: Controls & Channels -->
                    <div class="left-panel">
                        <!-- System Stats -->
                        <div class="stats-card">
                            <h3><i class="fas fa-chart-line"></i> System Statistics</h3>
                            <div class="stats-grid">
                                <div class="stat-item">
                                    <span class="stat-label">Frames</span>
                                    <span class="stat-value" id="statFrames">0</span>
                                </div>
                                <div class="stat-item">
                                    <span class="stat-label">Rate</span>
                                    <span class="stat-value" id="statRate">0 Hz</span>
                                </div>
                                <div class="stat-item">
                                    <span class="stat-label">Active</span>
                                    <span class="stat-value" id="statActive">0/16</span>
                                </div>
                                <div class="stat-item">
                                    <span class="stat-label">Read</span>
                                    <span class="stat-value" id="statRead">0 ms</span>
                                </div>
                            </div>
                        </div>

                        <!-- Display Controls -->
                        <div class="controls-card">
                            <h3><i class="fas fa-sliders-h"></i> Display Controls</h3>
                            <div class="control-group">
                                <label for="samples">Samples to Display</label>
                                <select class="form-control" id="samples">
                                    <option value="2500">1 Frame (2500)</option>
                                    <option value="5000">2 Frames (5000)</option>
                                    <option value="10000" selected>4 Frames (10000)</option>
                                    <option value="20000">8 Frames (20000)</option>
                                </select>
                            </div>
                            
                            <div class="control-row">
                                <button class="btn btn-secondary" id="clear">
                                    <i class="fas fa-trash"></i> Clear
                                </button>
                                <button class="btn btn-secondary" id="pause">
                                    <i class="fas fa-pause"></i> Pause
                                </button>
                                <button class="btn btn-secondary" id="deselect">
                                    <i class="fas fa-times"></i> Deselect
                                </button>
                            </div>
                            
                            <div class="control-group">
                                <label>Y-Axis Range</label>
                                <div class="range-control">
                                    <div class="range-input">
                                        <span class="range-label">Min:</span>
                                        <input type="number" id="yMin" value="-2000" step="100">
                                    </div>
                                    <div class="range-input">
                                        <span class="range-label">Max:</span>
                                        <input type="number" id="yMax" value="2000" step="100">
                                    </div>
                                    <button class="btn btn-sm btn-primary" id="applyY">
                                        <i class="fas fa-check"></i> Apply
                                    </button>
                                </div>
                            </div>
                            
                            <div class="control-group">
                                <div class="checkbox-group">
                                    <label class="checkbox-label">
                                        <input type="checkbox" id="autoY" checked>
                                        <span class="checkmark"></span>
                                        Auto Y-Axis
                                    </label>
                                    <label class="checkbox-label">
                                        <input type="checkbox" id="grid" checked>
                                        <span class="checkmark"></span>
                                        Show Grid
                                    </label>
                                    <label class="checkbox-label">
                                        <input type="checkbox" id="legend" checked>
                                        <span class="checkmark"></span>
                                        Show Legend
                                    </label>
                                </div>
                            </div>
                        </div>

                        <!-- Channel Monitor -->
                        <div class="channels-card">
                            <div class="card-header">
                                <h3><i class="fas fa-wave-square"></i> Channel Monitor</h3>
                                <span class="badge" id="activeBadge">0/16 active</span>
                            </div>
                            <div class="table-container">
                                <table class="channel-table" id="chTable">
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
                                    <tbody>
                                        <!-- Rows will be populated by JavaScript -->
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Right Panel: Visualization -->
                    <div class="right-panel">
                        <div class="viz-header">
                            <h2><i class="fas fa-oscilloscope"></i> Oscilloscope View</h2>
                            <div class="viz-info">
                                <span id="chTitle">No channels selected</span>
                                <span class="viz-stats" id="vizStats">0 samples @ 0 Hz</span>
                            </div>
                        </div>
                        
                        <div class="canvas-container">
                            <canvas id="scopeCanvas"></canvas>
                            <div class="canvas-overlay" id="canvasOverlay">
                                <div class="overlay-content">
                                    <i class="fas fa-mouse-pointer"></i>
                                    <p>Select channels from the table to display waveforms</p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="canvas-controls">
                            <div class="canvas-info">
                                <span id="info">⏳ Waiting for data...</span>
                            </div>
                            <div class="canvas-zoom">
                                <button class="btn btn-sm" id="zoomIn">
                                    <i class="fas fa-search-plus"></i>
                                </button>
                                <button class="btn btn-sm" id="zoomOut">
                                    <i class="fas fa-search-minus"></i>
                                </button>
                                <button class="btn btn-sm" id="zoomReset">
                                    <i class="fas fa-expand"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Footer -->
                <div class="scope-footer">
                    <div class="footer-left">
                        <span class="connection-info">
                            <i class="fas fa-plug"></i>
                            <span id="serverAddress">ws://<?php echo $_SERVER['HTTP_HOST']; ?>/ws</span>
                        </span>
                    </div>
                    <div class="footer-right">
                        <span class="timestamp" id="timestamp">--:--:--</span>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<script>
// Configuration
const CONFIG = {
    MAX_SAMPLES: 20000,
    BLOCK_SIZE: 2500,
    BASE_SAMPLE_RATE: 10000,
    DECIMATION_FACTOR: 20,
    EFFECTIVE_SAMPLE_RATE: 5000,
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
let canvas, ctx;
let zoomLevel = 1;
let panOffset = 0;

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
            <td><span class="channel-dot" id="d${i}" title="No data"></span></td>
        `;
        tr.onclick = (e) => {
            if (e.ctrlKey || e.metaKey) {
                selected.has(i) ? selected.delete(i) : selected.add(i);
            } else {
                selected = new Set([i]);
            }
            updateSelection();
            drawWaveforms();
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
            updateStatus('connected', 'Connected');
        };

        dataWebSocket.onmessage = handleDataMessage;
        
        dataWebSocket.onclose = () => {
            updateStatus('disconnected', 'Disconnected - Reconnecting...');
            setTimeout(connectDataWebSocket, CONFIG.RECONNECT_DELAY);
        };

        dataWebSocket.onerror = (error) => {
            updateStatus('error', 'Connection error');
        };
    } catch (error) {
        updateStatus('error', 'Connection failed');
        setTimeout(connectDataWebSocket, CONFIG.RECONNECT_DELAY);
    }
}

// Message handler
function handleDataMessage(event) {
    if (paused) return;

    if (event.data instanceof ArrayBuffer) {
        processBinaryData(event.data);
    } else if (typeof event.data === 'string') {
        try {
            const msg = JSON.parse(event.data);
            if (msg.type === 'heartbeat') {
                // Keep connection alive
                updateStatus('connected', 'Connected');
            }
        } catch (e) {
            // Not JSON
        }
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
        const view = new DataView(buffer);
        const bytesPerChannel = buffer.byteLength / 16;
        
        for (let ch = 0; ch < 16; ch++) {
            const channelData = [];
            for (let i = 0; i < 2500; i++) {
                const offset = (ch * 2500 + i) * 2;
                const value = view.getInt16(offset, true);
                channelData.push(value);
            }
            
            updateChannelBuffer(ch, channelData, now);
            updateChannelDisplay(ch);
        }

        drawWaveforms();
        updateInfoDisplay();

    } catch (err) {
        console.error('Error processing data:', err);
    }
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
    
    // Check if all zeros or flat signal
    let allZeros = true;
    let allSame = true;
    const firstVal = block[0];
    for (let i = 0; i < block.length; i++) {
        if (block[i] !== 0) allZeros = false;
        if (block[i] !== firstVal) allSame = false;
        if (!allZeros && !allSame) break;
    }
    
    if (allZeros || allSame) {
        buf.data = block;
        buf.valid = true;
        buf.lastUpdate = timestamp;
        buf.hasNonZero = false;
        buf.statistics = { min: 0, max: 0, mean: 0, rms: 0, variance: 0, frequency: 0, amplitude: 0 };
        return;
    }
    
    const newData = block;
    
    buf.data = [...buf.data, ...newData].slice(-CONFIG.MAX_SAMPLES);
    buf.valid = true;
    buf.lastUpdate = timestamp;

    updateChannelStatistics(buf, block);
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
    if (fEl) fEl.textContent = stats.frequency > 0.1 ? stats.frequency.toFixed(2) : '—';
    if (aEl) aEl.textContent = stats.amplitude > 0 ? stats.amplitude.toFixed(1) : '—';

    const dot = document.getElementById(`d${channel}`);
    if (dot) {
        if (!buf.valid) {
            dot.className = 'channel-dot gray';
            dot.title = 'No data';
        } else if (buf.hasNonZero) {
            dot.className = 'channel-dot green';
            dot.title = `Active signal (${stats.frequency.toFixed(1)}Hz)`;
        } else {
            dot.className = 'channel-dot orange';
            dot.title = `Flat signal`;
        }
    }
}

// Canvas drawing functions
function initCanvas() {
    canvas = document.getElementById('scopeCanvas');
    ctx = canvas.getContext('2d');
    resizeCanvas();
    window.addEventListener('resize', resizeCanvas);
}

function resizeCanvas() {
    const container = canvas.parentElement;
    canvas.width = container.clientWidth;
    canvas.height = container.clientHeight;
    drawWaveforms();
}

function drawWaveforms() {
    if (!ctx || !canvas.width) return;
    
    // Clear canvas
    ctx.clearRect(0, 0, canvas.width, canvas.height);
    
    if (selected.size === 0) {
        drawNoSignalMessage();
        return;
    }
    
    // Draw grid if enabled
    if (document.getElementById('grid').checked) {
        drawGrid();
    }
    
    // Draw axes
    drawAxes();
    
    // Draw waveforms
    const samplesToShow = parseInt(document.getElementById('samples').value);
    const autoY = document.getElementById('autoY').checked;
    
    // Calculate Y range
    let yMin = yRange.min;
    let yMax = yRange.max;
    
    if (autoY && selected.size > 0) {
        yMin = Infinity;
        yMax = -Infinity;
        
        for (const ch of selected) {
            const buffer = buffers[ch].data.slice(-samplesToShow);
            for (const val of buffer) {
                if (val < yMin) yMin = val;
                if (val > yMax) yMax = val;
            }
        }
        
        // Add padding
        const range = yMax - yMin;
        yMin -= range * 0.1;
        yMax += range * 0.1;
    }
    
    const yRangeTotal = Math.max(yMax - yMin, 1);
    
    // Draw each selected channel
    let colorIndex = 0;
    for (const ch of selected) {
        const buffer = buffers[ch].data.slice(-samplesToShow);
        if (buffer.length === 0) continue;
        
        ctx.strokeStyle = CONFIG.COLORS[colorIndex % CONFIG.COLORS.length];
        ctx.lineWidth = 2;
        ctx.beginPath();
        
        for (let i = 0; i < buffer.length; i++) {
            const x = 60 + (i / buffer.length) * (canvas.width - 80);
            const y = canvas.height - 30 - ((buffer[i] - yMin) / yRangeTotal) * (canvas.height - 60);
            
            if (i === 0) {
                ctx.moveTo(x, y);
            } else {
                ctx.lineTo(x, y);
            }
        }
        
        ctx.stroke();
        
        // Draw legend if enabled
        if (document.getElementById('legend').checked) {
            drawLegend(ch, colorIndex, buffer[buffer.length - 1]);
        }
        
        colorIndex++;
    }
}

function drawGrid() {
    ctx.strokeStyle = 'rgba(128, 128, 128, 0.2)';
    ctx.lineWidth = 1;
    
    // Vertical lines
    const verticalSpacing = canvas.width / 20;
    for (let x = 60; x < canvas.width - 20; x += verticalSpacing) {
        ctx.beginPath();
        ctx.moveTo(x, 20);
        ctx.lineTo(x, canvas.height - 20);
        ctx.stroke();
    }
    
    // Horizontal lines
    const horizontalSpacing = (canvas.height - 40) / 10;
    for (let y = 20; y < canvas.height - 20; y += horizontalSpacing) {
        ctx.beginPath();
        ctx.moveTo(60, y);
        ctx.lineTo(canvas.width - 20, y);
        ctx.stroke();
    }
}

function drawAxes() {
    ctx.strokeStyle = 'rgba(255, 255, 255, 0.5)';
    ctx.lineWidth = 2;
    
    // X-axis
    ctx.beginPath();
    ctx.moveTo(60, canvas.height / 2);
    ctx.lineTo(canvas.width - 20, canvas.height / 2);
    ctx.stroke();
    
    // Y-axis
    ctx.beginPath();
    ctx.moveTo(60, 20);
    ctx.lineTo(60, canvas.height - 20);
    ctx.stroke();
}

function drawLegend(channel, colorIndex, lastValue) {
    const x = canvas.width - 150;
    const y = 40 + (colorIndex * 25);
    
    // Color indicator
    ctx.fillStyle = CONFIG.COLORS[colorIndex % CONFIG.COLORS.length];
    ctx.fillRect(x, y - 8, 12, 12);
    
    // Text
    ctx.fillStyle = '#ffffff';
    ctx.font = '12px monospace';
    ctx.textAlign = 'left';
    const stats = buffers[channel].statistics;
    const text = `CH${channel}: ${lastValue} (${stats.frequency.toFixed(1)}Hz)`;
    ctx.fillText(text, x + 18, y);
}

function drawNoSignalMessage() {
    ctx.fillStyle = 'rgba(255, 255, 255, 0.5)';
    ctx.font = '16px Arial';
    ctx.textAlign = 'center';
    ctx.textBaseline = 'middle';
    ctx.fillText('Select channels to display', canvas.width / 2, canvas.height / 2);
}

// UI update functions
function updateSelection() {
    document.querySelectorAll('#chTable tbody tr').forEach((r, i) => {
        r.classList.toggle('selected', selected.has(i));
    });
    
    const list = Array.from(selected).sort((a, b) => a - b);
    document.getElementById('chTitle').textContent = list.length ? 
        `CH ${list.join(', ')}` : 'No channels selected';
    
    // Show/hide canvas overlay
    const overlay = document.getElementById('canvasOverlay');
    if (selected.size > 0) {
        overlay.style.display = 'none';
    } else {
        overlay.style.display = 'flex';
    }
}

function updateInfoDisplay() {
    const active = buffers.filter(b => b.valid && b.hasNonZero).length;
    
    const infoText = `Frames: ${frameCount} • Rate: ${dataRate.toFixed(1)} Hz • Active: ${active}/16 • Displaying: ${lastDisplayedSamples} samples`;
    document.getElementById('info').textContent = infoText;

    document.getElementById('statFrames').textContent = frameCount;
    document.getElementById('statRate').textContent = dataRate.toFixed(1) + ' Hz';
    document.getElementById('statActive').textContent = `${active}/16`;
    document.getElementById('activeBadge').textContent = `${active}/16 active`;
    
    document.getElementById('vizStats').textContent = `${lastDisplayedSamples} samples @ ${dataRate.toFixed(1)} Hz`;
    document.getElementById('timestamp').textContent = new Date().toLocaleTimeString();
}

function updateStatus(status, message) {
    const indicator = document.getElementById('statusIndicator');
    const dot = indicator.querySelector('.status-dot');
    const text = document.getElementById('statusText');
    
    indicator.className = `status-indicator ${status}`;
    dot.className = `status-dot ${status}`;
    text.textContent = message;
}

// Initialize
function init() {
    buildChannelTable();
    initCanvas();
    connectDataWebSocket();
    
    // Set up event listeners
    document.getElementById('samples').onchange = () => {
        lastDisplayedSamples = parseInt(document.getElementById('samples').value);
        drawWaveforms();
        updateInfoDisplay();
    };
    
    document.getElementById('autoY').onchange = drawWaveforms;
    document.getElementById('grid').onchange = drawWaveforms;
    document.getElementById('legend').onchange = drawWaveforms;
    
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
        drawWaveforms();
    };
    
    document.getElementById('clear').onclick = () => {
        buffers.forEach(buf => {
            buf.data = [];
            buf.valid = false;
            buf.hasNonZero = false;
        });
        frameCount = 0;
        dataRate = 0;
        drawWaveforms();
        updateInfoDisplay();
    };
    
    document.getElementById('pause').onclick = function() { 
        paused = !paused; 
        this.innerHTML = paused ? '<i class="fas fa-play"></i> Resume' : '<i class="fas fa-pause"></i> Pause';
        this.classList.toggle('btn-paused', paused);
    };
    
    document.getElementById('deselect').onclick = () => { 
        selected.clear(); 
        updateSelection(); 
        drawWaveforms(); 
    };
    
    document.getElementById('refreshBtn').onclick = () => {
        location.reload();
    };
    
    // Zoom controls
    document.getElementById('zoomIn').onclick = () => {
        zoomLevel *= 1.2;
        drawWaveforms();
    };
    
    document.getElementById('zoomOut').onclick = () => {
        zoomLevel /= 1.2;
        drawWaveforms();
    };
    
    document.getElementById('zoomReset').onclick = () => {
        zoomLevel = 1;
        panOffset = 0;
        drawWaveforms();
    };
    
    // Initial draw
    drawWaveforms();
}

// Start when ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
} else {
    init();
}
</script>

<?php include_once './footer.php'; ?>
</body>
</html>