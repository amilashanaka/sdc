<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Spicer 16-Ch DAQ • Real-Time Oscilloscope</title>
<script src="https://cdn.plot.ly/plotly-2.35.2.min.js"></script>
<style>
  :root { --bg:#f8f9fa; --panel:#fff; --text:#222; --border:#dee2e6; --accent:#1976d2; --green:#28a745; --red:#dc3545; --orange:#fd7e14; --purple:#6f42c1; }
  [data-theme="dark"] { --bg:#121212; --panel:#1e1e1e; --text:#e0e0e0; --border:#333; --accent:#42a5f5; --green:#4caf50; --red:#f44336; --orange:#ff9800; --purple:#9c27b0; }
  body { margin:0; padding:20px; font-family:'Segoe UI',sans-serif; background:var(--bg); color:var(--text); transition:0.3s; }
  .container { display:flex; gap:20px; max-width:1920px; margin:auto; }
  .panel { background:var(--panel); border-radius:12px; box-shadow:0 6px 25px rgba(0,0,0,0.15); padding:20px; }
  .left { flex:0 0 420px; max-height:95vh; overflow-y:auto; }
  .right { flex:1; min-width:0; }
  h1 { margin:0 0 16px; color:var(--accent); font-size:24px; }
  h2 { margin:0 0 12px; color:var(--text); font-size:20px; }
  .controls { display:flex; flex-wrap:wrap; gap:10px; margin-bottom:16px; align-items:center; }
  button, select, input { padding:9px 16px; border:none; border-radius:8px; font-size:14px; cursor:pointer; background:rgba(0,0,0,0.08); color:var(--text); }
  button:hover { background:var(--accent); color:white; }
  button:disabled { opacity:0.6; cursor:not-allowed; }
  .status-bar { padding:12px; border-radius:8px; text-align:center; font-weight:bold; margin-top:12px; }
  .connected { background:#d4edda; color:#155724; }
  .disconnected { background:#f8d7da; color:#721c24; }
  table { width:100%; border-collapse:collapse; font-size:13px; margin-top:8px; }
  th, td { padding:8px 6px; text-align:center; border:1px solid var(--border); }
  th { background:rgba(0,0,0,0.07); }
  tr:hover { background:rgba(25,118,210,0.1); }
  tr.selected { background:rgba(25,118,210,0.25); font-weight:bold; }
  .dot { display:inline-block; width:12px; height:12px; border-radius:50%; }
  .dot-green { background:var(--green); box-shadow:0 0 10px var(--green); animation:glow 2s infinite; }
  .dot-red { background:var(--red); box-shadow:0 0 10px var(--red); }
  .dot-orange { background:var(--orange); box-shadow:0 0 10px var(--orange); animation:glow 2s infinite; }
  .dot-gray { background:#888; opacity:0.4; }
  @keyframes glow { 0%,100% { opacity:0.8 } 50% { opacity:1 } }
  #plot { width:100%; height:600px; border-radius:8px; }
  .info { margin-top:12px; padding:12px; background:rgba(0,0,0,0.05); border-radius:8px; font-family:monospace; font-size:13px; }
  .theme-btn { position:fixed; top:16px; right:16px; z-index:999; padding:10px 16px; border-radius:50px; background:var(--panel); box-shadow:0 4px 15px rgba(0,0,0,0.2); }
  .system-stats { display:grid; grid-template-columns:1fr 1fr; gap:8px; margin-top:12px; font-size:12px; }
  .stat-item { padding:6px; background:rgba(0,0,0,0.05); border-radius:6px; }
  .stat-label { font-weight:bold; color:var(--accent); }
  .y-range-controls { display:flex; gap:8px; align-items:center; margin:8px 0; }
  .y-range-controls input { width:80px; padding:6px; }
</style>
</head>
<body>

<button class="theme-btn" id="themeBtn">🌙 Dark Mode</button>

<div class="container">
  <div class="panel left">
    <h1>Spicer 16-Ch DAQ</h1>
    
    <!-- System Controls -->
    <div class="controls">
      <button id="refreshBtn">🔄 Refresh</button>
    </div>

    <!-- System Statistics -->
    <div class="system-stats" id="systemStats">
      <div class="stat-item"><span class="stat-label">Frames:</span> <span id="statFrames">0</span></div>
      <div class="stat-item"><span class="stat-label">Rate:</span> <span id="statRate">0 Hz</span></div>
      <div class="stat-item"><span class="stat-label">Active:</span> <span id="statActive">0/16</span></div>
      <div class="stat-item"><span class="stat-label">Read:</span> <span id="statRead">0 ms</span></div>
    </div>

    <!-- Y-Axis Controls -->
    <div class="y-range-controls">
      <label>Y-Min: <input type="number" id="yMin" value="-2000" step="100"></label>
      <label>Y-Max: <input type="number" id="yMax" value="2000" step="100"></label>
      <button id="applyYRange">Apply</button>
    </div>

    <!-- Display Controls -->
    <div class="controls">
      <select id="samples">
        <option value="2500">1 Frame (2500)</option>
        <option value="5000">2 Frames (5000)</option>
        <option value="10000" selected>4 Frames (10000)</option>
        <option value="20000">8 Frames (20000)</option>
      </select>
      <button id="clear">Clear</button>
      <button id="pause">⏸ Pause</button>
      <button id="deselect">Deselect</button>
    </div>

    <!-- Channel Table -->
    <table id="chTable">
      <thead><tr><th>Ch</th><th>Value</th><th>Min</th><th>Max</th><th>Freq</th><th>Amp</th><th>Status</th></tr></thead>
      <tbody></tbody>
    </table>

    <div class="status-bar connected" id="status">🔌 Connecting...</div>
  </div>

  <div class="panel right">
    <h2>Oscilloscope <span id="chTitle">—</span></h2>
    <div class="controls">
      <label><input type="checkbox" id="autoY"> Auto Y</label>
      <label><input type="checkbox" id="grid" checked> Grid</label>
      <label><input type="checkbox" id="legend" checked> Legend</label>
    </div>
    <div id="plot"></div>
    <div class="info" id="info">⏳ Waiting for data...</div>
  </div>
</div>

<script>
// Configuration
const CONFIG = {
    MAX_SAMPLES: 20000,
    BLOCK_SIZE: 2500,
    SAMPLE_RATE: 10000, // 10 kHz sampling rate
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
let lastDisplayedSamples = 10000; // Track what we're actually displaying

// Theme management
document.getElementById('themeBtn').onclick = () => {
    const dark = document.body.getAttribute('data-theme') === 'dark';
    document.body.setAttribute('data-theme', dark ? '' : 'dark');
    document.getElementById('themeBtn').textContent = dark ? '🌙 Dark Mode' : '☀️ Light Mode';
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
            <td><span class="dot dot-gray" id="d${i}"></span></td>
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
        dataWebSocket = new WebSocket('ws://' + location.host + '/ws');
        dataWebSocket.binaryType = 'arraybuffer';

        dataWebSocket.onopen = () => {
            updateStatus('🟢 Connected', 'connected');
        };

        dataWebSocket.onmessage = handleDataMessage;
        
        dataWebSocket.onclose = () => {
            updateStatus('🔴 Disconnected - Reconnecting...', 'disconnected');
            setTimeout(connectDataWebSocket, CONFIG.RECONNECT_DELAY);
        };

        dataWebSocket.onerror = (error) => {
            updateStatus('⚠️ Connection error', 'disconnected');
        };
    } catch (error) {
        updateStatus('⚠️ Connection failed', 'disconnected');
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
                arr[i] = (value < -32768 || value > 32767) ? 0 : value;
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
            arr[i] = view.getInt16(dataOffset, true);
            dataOffset += 2;
        }
        samples.push(arr);
    }
    
    return { blocks: samples };
}

// Buffer update - keeps data persistent until explicitly cleared
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
        // Mark channel as stale if receiving duplicates
        buf.lastUpdate = timestamp;
        return;
    }
    
    // Check if all zeros or flat signal - might indicate channel is off
    let allZeros = true;
    let allSame = true;
    const firstVal = block[0];
    for (let i = 0; i < block.length; i++) {
        if (block[i] !== 0) allZeros = false;
        if (block[i] !== firstVal) allSame = false;
        if (!allZeros && !allSame) break;
    }
    
    // If channel appears off (all zeros or all same value), clear old data
    if (allZeros || allSame) {
        buf.data = Array.from(block);
        buf.valid = true;
        buf.lastUpdate = timestamp;
        buf.hasNonZero = false;
        buf.statistics = { min: 0, max: 0, mean: 0, rms: 0, variance: 0, frequency: 0, amplitude: 0 };
        return;
    }
    
    // Detect discontinuity and insert NaN gap if needed
    let insertGap = false;
    if (buf.data.length > 0) {
        const lastVal = buf.data[buf.data.length - 1];
        const firstVal = block[0];
        const delta = Math.abs(lastVal - firstVal);
        
        let maxIncomingDelta = 0;
        for (let i = 1; i < block.length; i++) {
            const d = Math.abs(block[i] - block[i - 1]);
            if (d > maxIncomingDelta) maxIncomingDelta = d;
        }
        
        if (maxIncomingDelta > 0 && delta > 5 * maxIncomingDelta) {
            insertGap = true;
        }
    }
    
    const newData = Array.from(block);
    
    if (insertGap) {
        buf.data.push(NaN);
    }
    
    buf.data = [...buf.data, ...newData].slice(-CONFIG.MAX_SAMPLES);
    buf.valid = true;
    buf.lastUpdate = timestamp;

    updateChannelStatistics(buf, block);
}

// Calculate frequency and amplitude
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
    
    // Zero-crossing frequency estimation with sub-sample interpolation
    const mean = validData.reduce((a, b) => a + b, 0) / validData.length;
    const crossings = [];
    
    for (let i = 1; i < validData.length; i++) {
        const prev = validData[i-1] - mean;
        const curr = validData[i] - mean;
        
        // Detect positive-going zero crossing with interpolation
        if (prev < 0 && curr >= 0) {
            // Linear interpolation to find exact crossing point
            const fraction = -prev / (curr - prev);
            const crossingIndex = (i - 1) + fraction;
            crossings.push(crossingIndex);
        }
    }
    
    if (crossings.length < 2) return { frequency: 0, amplitude };
    
    // Calculate average period from all crossing intervals
    let totalPeriod = 0;
    for (let i = 1; i < crossings.length; i++) {
        totalPeriod += crossings[i] - crossings[i-1];
    }
    const avgPeriodSamples = totalPeriod / (crossings.length - 1);
    
    // Convert period in samples to frequency in Hz
    const frequency = CONFIG.SAMPLE_RATE / avgPeriodSamples;
    
    return { frequency, amplitude };
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
    
    // Estimate frequency and amplitude from all available data for better accuracy
    const recentData = buf.data.slice(-10000); // Use up to 1 second of data for 0.1 Hz resolution
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
    if (fEl) fEl.textContent = stats.frequency > 0 ? stats.frequency.toFixed(2) + ' Hz' : '—';
    if (aEl) aEl.textContent = stats.amplitude > 0 ? stats.amplitude.toFixed(1) : '—';

    const dot = document.getElementById(`d${channel}`);
    if (dot) {
        if (!buf.valid) {
            dot.className = 'dot dot-gray';
            dot.title = 'No data';
        } else if (buf.hasNonZero) {
            dot.className = 'dot dot-green';
            dot.title = `Active signal`;
        } else {
            dot.className = 'dot dot-orange';
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
        Plotly.react('plot', [], { 
            title: 'Select channels to display',
            paper_bgcolor: 'rgba(0,0,0,0)',
            plot_bgcolor: 'rgba(0,0,0,0)',
            font: { color: getComputedStyle(document.body).getPropertyValue('--text') }
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
        font: { color: getComputedStyle(document.body).getPropertyValue('--text') },
        margin: { l: 60, r: 30, t: 50, b: 50 }
    };

    Plotly.react('plot', traces, layout, {responsive: true});
}

// UI update functions
function updateSelection() {
    document.querySelectorAll('#chTable tbody tr').forEach((r, i) => {
        r.classList.toggle('selected', selected.has(i));
    });
    
    const list = Array.from(selected).sort((a, b) => a - b);
    document.getElementById('chTitle').textContent = list.length ? 
        `CH ${list.join(', ')}` : '—';
}

function updateInfoDisplay() {
    const active = buffers.filter(b => b.valid && b.hasNonZero).length;
    
    const infoText = `Frames: ${frameCount} • Rate: ${dataRate.toFixed(1)} Hz • Active: ${active}/16 • Displaying: ${lastDisplayedSamples} samples`;
    document.getElementById('info').textContent = infoText;
}

function updateStatus(message, type) {
    const status = document.getElementById('status');
    if (status) {
        status.textContent = message;
        status.className = `status-bar ${type}`;
    }
}

// System statistics update
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

document.getElementById('applyYRange').onclick = () => {
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
</body>
</html>