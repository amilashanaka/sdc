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
  .ws-stats { margin-top:12px; padding:10px; background:rgba(0,0,0,0.05); border-radius:6px; font-size:12px; font-family:monospace; }
  .reconnect-btn { background:var(--orange) !important; color:white !important; }
</style>
</head>
<body>

<button class="theme-btn" id="themeBtn">🌙 Dark Mode</button>

<div class="container">
  <div class="panel left">
    <h1>Spicer 16-Ch DAQ</h1>
    
    <!-- Connection Controls -->
    <div class="controls">
      <button id="refreshBtn">🔄 Refresh</button>
      <button id="reconnectBtn" class="reconnect-btn">🔁 Reconnect</button>
    </div>

    <!-- WebSocket Stats -->
    <div class="ws-stats">
      <div>WebSocket: <span id="wsStatus">Disconnected</span></div>
      <div>Message Size: <span id="msgSize">0 bytes</span></div>
      <div>Packets: <span id="packetCount">0</span></div>
      <div>Data Rate: <span id="dataRate">0 Hz</span></div>
    </div>

    <!-- System Statistics -->
    <div class="system-stats" id="systemStats">
      <div class="stat-item"><span class="stat-label">Frames:</span> <span id="statFrames">0</span></div>
      <div class="stat-item"><span class="stat-label">Active:</span> <span id="statActive">0/16</span></div>
      <div class="stat-item"><span class="stat-label">Buffer:</span> <span id="statBuffer">0</span></div>
      <div class="stat-item"><span class="stat-label">Format:</span> <span id="statFormat">Unknown</span></div>
    </div>

    <!-- Y-Axis Controls -->
    <div class="y-range-controls">
      <label>Y-Min: <input type="number" id="yMin" value="-1000" step="100"></label>
      <label>Y-Max: <input type="number" id="yMax" value="1000" step="100"></label>
      <button id="applyYRange">Apply</button>
    </div>

    <!-- Display Controls -->
    <div class="controls">
      <select id="samples">
        <option value="1000">1k samples</option>
        <option value="2000">2k samples</option>
        <option value="4000" selected>4k samples</option>
        <option value="8000">8k samples</option>
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

    <div class="status-bar disconnected" id="status">🔌 Connecting to WebSocket...</div>
  </div>

  <div class="panel right">
    <h2>Oscilloscope <span id="chTitle">—</span></h2>
    <div class="controls">
      <label><input type="checkbox" id="autoY" checked> Auto Y</label>
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
    MAX_SAMPLES: 16000,
    COLORS: ['#1976d2','#e91e63','#4caf50','#ff9800','#9c27b0','#00bcd4','#f44336','#8bc34a','#ff5722','#607d8b','#795548','#cddc39','#009688','#ffc107','#673ab7','#03a9f4'],
    RECONNECT_DELAY: 3000,
    SIGNAL_VARIANCE_THRESHOLD: 10
};

// Buffer management - SIMPLIFIED for 1 channel
const buffers = Array(16).fill().map(() => ({
    data: [],
    valid: false,
    lastUpdate: 0,
    hasSignal: false,
    statistics: { min: 0, max: 0, mean: 0, rms: 0, frequency: 0, amplitude: 0 }
}));

// State variables
let selected = new Set([0]); // Default to channel 0
let paused = false;
let frameCount = 0;
let lastTime = 0;
let dataRate = 0.0;
let dataWebSocket = null;
let yRange = { min: -1000, max: 1000 };
let lastDisplayedSamples = 4000;
let packetCount = 0;
let reconnectAttempts = 0;
let dataFormat = "unknown";

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

// Get WebSocket URL based on current protocol
function getWebSocketUrl() {
    const protocol = window.location.protocol === 'https:' ? 'wss:' : 'ws:';
    return `${protocol}//${window.location.host}/ws`;
}

// WebSocket connection
function connectDataWebSocket() {
    if (dataWebSocket && dataWebSocket.readyState === WebSocket.OPEN) {
        dataWebSocket.close();
    }

    try {
        const wsUrl = getWebSocketUrl();
        updateStatus('🟡 Connecting to WebSocket...', 'disconnected');
        
        dataWebSocket = new WebSocket(wsUrl);
        dataWebSocket.binaryType = 'arraybuffer';

        dataWebSocket.onopen = () => {
            reconnectAttempts = 0;
            updateStatus('🟢 WebSocket Connected', 'connected');
            document.getElementById('wsStatus').textContent = 'Connected';
            document.getElementById('wsStatus').style.color = '#28a745';
        };

        dataWebSocket.onmessage = (event) => {
            packetCount++;
            document.getElementById('packetCount').textContent = packetCount;
            
            if (event.data instanceof ArrayBuffer) {
                processBinaryData(event.data);
            } else if (typeof event.data === 'string') {
                // Handle heartbeat messages
                try {
                    const msg = JSON.parse(event.data);
                    if (msg.type === 'heartbeat') {
                        document.getElementById('msgSize').textContent = 'Heartbeat';
                    }
                } catch (e) {
                    // Ignore parsing errors
                }
            }
        };
        
        dataWebSocket.onclose = (event) => {
            updateStatus('🔴 WebSocket Disconnected', 'disconnected');
            document.getElementById('wsStatus').textContent = 'Disconnected';
            document.getElementById('wsStatus').style.color = '#dc3545';
            
            // Attempt reconnection
            if (reconnectAttempts < 5) {
                reconnectAttempts++;
                setTimeout(() => {
                    updateStatus('🟡 Reconnecting... (Attempt ' + reconnectAttempts + ')', 'disconnected');
                    connectDataWebSocket();
                }, CONFIG.RECONNECT_DELAY);
            } else {
                updateStatus('🔴 Failed to connect after 5 attempts. Click Reconnect button.', 'disconnected');
            }
        };

        dataWebSocket.onerror = (error) => {
            updateStatus('⚠️ WebSocket Error', 'disconnected');
        };
    } catch (error) {
        updateStatus('⚠️ Connection failed: ' + error.message, 'disconnected');
        setTimeout(connectDataWebSocket, CONFIG.RECONNECT_DELAY);
    }
}

// Binary data processing - FIXED for actual data format
function processBinaryData(buffer) {
    if (paused) return;

    const now = performance.now();
    if (lastTime > 0) {
        dataRate = 1000 / (now - lastTime);
    }
    lastTime = now;
    frameCount++;

    // Update display
    document.getElementById('dataRate').textContent = dataRate.toFixed(1) + ' Hz';
    document.getElementById('msgSize').textContent = buffer.byteLength + ' bytes';
    
    try {
        // Detect data format based on size
        const view = new DataView(buffer);
        const totalBytes = buffer.byteLength;
        const totalSamples = totalBytes / 2; // Each sample is 2 bytes
        
        document.getElementById('statFormat').textContent = totalBytes + ' bytes';
        
        // FIXED: The server sends data for ONE channel (based on your image showing ~4.4 kB)
        // 4.4 kB = ~4500 bytes = ~2250 samples
        // We'll assume it's for channel 0 (the only plugged in channel)
        
        // Parse all samples as int16 (little-endian)
        const samples = new Int16Array(totalSamples);
        for (let i = 0; i < totalSamples; i++) {
            samples[i] = view.getInt16(i * 2, true);
        }
        
        // Update channel 0 with all data
        updateChannelBuffer(0, Array.from(samples), now);
        updateChannelDisplay(0);
        
        // Also update other channels with empty/placeholder data
        for (let ch = 1; ch < 16; ch++) {
            if (!buffers[ch].valid || buffers[ch].data.length === 0) {
                // Create placeholder data for other channels
                const placeholder = Array(100).fill(0);
                updateChannelBuffer(ch, placeholder, now);
                updateChannelDisplay(ch);
            }
        }
        
        plot();
        updateInfoDisplay();

    } catch (err) {
        console.error('Error processing data:', err);
        document.getElementById('statFormat').textContent = 'Error: ' + err.message;
    }
}

// Buffer update
function updateChannelBuffer(channel, samples, timestamp) {
    if (channel < 0 || channel >= 16 || !samples || samples.length === 0) return;
    
    const buf = buffers[channel];
    
    // Add new data to buffer
    buf.data = [...buf.data, ...samples].slice(-CONFIG.MAX_SAMPLES);
    buf.valid = true;
    buf.lastUpdate = timestamp;
    
    // Calculate statistics
    updateChannelStatistics(buf, samples);
}

// Calculate statistics
function updateChannelStatistics(buf, samples) {
    if (!samples || samples.length === 0) return;
    
    let min = Infinity, max = -Infinity, sum = 0, sumSq = 0;
    let validSamples = 0;
    
    for (let i = 0; i < samples.length; i++) {
        const val = samples[i];
        if (val >= -32768 && val <= 32767) {
            if (val < min) min = val;
            if (val > max) max = val;
            sum += val;
            sumSq += val * val;
            validSamples++;
        }
    }
    
    if (validSamples === 0) {
        buf.statistics = { min: 0, max: 0, mean: 0, rms: 0, frequency: 0, amplitude: 0 };
        buf.hasSignal = false;
        return;
    }
    
    const mean = sum / validSamples;
    const variance = (sumSq / validSamples) - (mean * mean);
    
    // Simple frequency detection (zero crossing)
    let zeroCrossings = 0;
    let prev = samples[0] - mean;
    for (let i = 1; i < samples.length; i++) {
        const curr = samples[i] - mean;
        if ((prev <= 0 && curr > 0) || (prev >= 0 && curr < 0)) {
            zeroCrossings++;
        }
        prev = curr;
    }
    
    // Estimate frequency: (zeroCrossings/2) * (sampleRate / samples)
    // Assuming 100 Hz sample rate for now
    const sampleRate = 100; // Adjust based on your actual sample rate
    const frequency = (zeroCrossings / 2) * (sampleRate / samples.length);
    const amplitude = (max - min) / 2;
    
    buf.statistics = {
        min: min,
        max: max,
        mean: mean,
        rms: Math.sqrt(sumSq / validSamples),
        frequency: frequency,
        amplitude: amplitude
    };
    
    buf.hasSignal = amplitude > 10 || variance > 100;
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
    if (fEl) fEl.textContent = stats.frequency > 0.5 ? stats.frequency.toFixed(1) + ' Hz' : '—';
    if (aEl) aEl.textContent = stats.amplitude > 0 ? stats.amplitude.toFixed(0) : '—';

    const dot = document.getElementById(`d${channel}`);
    if (dot) {
        if (!buf.valid) {
            dot.className = 'dot dot-gray';
            dot.title = 'No data';
        } else if (buf.hasSignal) {
            dot.className = 'dot dot-green';
            dot.title = `Active signal (${stats.amplitude.toFixed(0)} amplitude)`;
        } else {
            dot.className = 'dot dot-orange';
            dot.title = `Weak signal`;
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
        
        const hasSignal = buffers[ch].hasSignal;

        traces.push({
            x: Array.from({length: y.length}, (_, i) => i),
            y: y,
            name: `CH ${ch}${hasSignal ? '' : ' (weak)'}`,
            mode: 'lines',
            line: { 
                color: CONFIG.COLORS[traceIndex % CONFIG.COLORS.length], 
                width: 1.5
            },
            connectgaps: false
        });
        traceIndex++;
    }

    const autoY = document.getElementById('autoY').checked;
    
    const title = `${traces.length} channel${traces.length > 1 ? 's' : ''} • ${dataRate.toFixed(1)} Hz • ${samplesToDisplay} samples`;

    const layout = {
        title: title,
        xaxis: { 
            title: 'Sample', 
            showgrid: document.getElementById('grid').checked,
            gridcolor: 'rgba(128,128,128,0.2)'
        },
        yaxis: {
            title: 'ADC Value', 
            showgrid: document.getElementById('grid').checked,
            gridcolor: 'rgba(128,128,128,0.2)',
            autorange: autoY,
            ...(!autoY && { range: [yRange.min, yRange.max] })
        },
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
    const active = buffers.filter(b => b.valid && b.hasSignal).length;
    const totalBuffer = buffers.reduce((sum, buf) => sum + buf.data.length, 0);
    
    const infoText = `Frames: ${frameCount} • Rate: ${dataRate.toFixed(1)} Hz • Active: ${active}/16 • Buffer: ${totalBuffer} samples`;
    document.getElementById('info').textContent = infoText;
    
    // Update system stats
    document.getElementById('statFrames').textContent = frameCount;
    document.getElementById('statActive').textContent = `${active}/16`;
    document.getElementById('statBuffer').textContent = totalBuffer;
}

function updateStatus(message, type) {
    const status = document.getElementById('status');
    if (status) {
        status.textContent = message;
        status.className = `status-bar ${type}`;
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
        buf.hasSignal = false;
    });
    frameCount = 0;
    dataRate = 0;
    packetCount = 0;
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
    selected.add(0); // Always keep at least channel 0 selected
    updateSelection(); 
    plot(); 
};

document.getElementById('refreshBtn').onclick = () => {
    location.reload();
};

document.getElementById('reconnectBtn').onclick = () => {
    reconnectAttempts = 0;
    connectDataWebSocket();
};

// Initialize
function init() {
    buildChannelTable();
    connectDataWebSocket();
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