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
                <div class="scope-container">
                    <!-- Left Panel: Controls -->
                    <div class="scope-panel scope-left">
                        <h1 class="scope-h1">
                            <i class="fas fa-chart-line"></i>
                            Spicer 16-Ch DAQ
                        </h1>

         

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

                                       <div class="scope-section">System Statistics</div>
                        <div class="scope-stats">
                            <div class="scope-stat">
                                <span class="scope-stat-label">Frames</span>
                                <div class="scope-stat-value" id="statFrames">0</div>
                            </div>
                            <div class="scope-stat">
                                <span class="scope-stat-label">Rate</span>
                                <div class="scope-stat-value" id="statRate">0 Hz</div>
                            </div>
                            <div class="scope-stat">
                                <span class="scope-stat-label">Active</span>
                                <div class="scope-stat-value" id="statActive">0/16</div>
                            </div>
                            <div class="scope-stat">
                                <span class="scope-stat-label">Samples</span>
                                <div class="scope-stat-value" id="statSamples">0</div>
                            </div>
                        </div>

                        <div class="scope-section">Y-Axis Range</div>
                        <div class="scope-slider-group">
                            <div class="scope-slider-label">
                                <span>Minimum</span>
                                <span class="scope-slider-value" id="yMinValue">-2000</span>
                            </div>
                            <input type="range" class="scope-slider" id="yMinSlider" min="-5000" max="0" value="-2000" step="100">
                            
                            <div class="scope-slider-label" style="margin-top: 15px;">
                                <span>Maximum</span>
                                <span class="scope-slider-value" id="yMaxValue">2000</span>
                            </div>
                            <input type="range" class="scope-slider" id="yMaxSlider" min="0" max="5000" value="2000" step="100">
                        </div>

                        <div class="scope-section">Time Base</div>
                        <div class="scope-slider-group">
                            <div class="scope-slider-label">
                                <span>Samples</span>
                                <span class="scope-slider-value" id="samplesValue">10000</span>
                            </div>
                            <input type="range" class="scope-slider" id="samplesSlider" min="2500" max="20000" value="10000" step="2500">
                        </div>

                        <div class="scope-section">Display Options</div>
                        <div class="scope-checkbox-group" style="padding: 10px 0;">
                            <label class="scope-checkbox-label">
                                <input type="checkbox" id="autoY">
                                Auto Y-Axis
                            </label>
                            <label class="scope-checkbox-label">
                                <input type="checkbox" id="grid" checked>
                                Grid
                            </label>
                        </div>

                        <div class="scope-section">Control</div>
                        <div class="scope-controls">
                            <button class="scope-btn" id="pause">
                                <i class="fas fa-pause"></i> Pause
                            </button>
                            <button class="scope-btn scope-btn-secondary" id="clear">
                                <i class="fas fa-eraser"></i> Clear
                            </button>
                            <button class="scope-btn scope-btn-secondary" id="deselect">
                                <i class="fas fa-times"></i> Deselect
                            </button>
                            <button class="scope-btn scope-btn-secondary" id="refreshBtn">
                                <i class="fas fa-sync-alt"></i> Refresh
                            </button>
                        </div>

                        <div class="scope-status scope-disconnected" id="status">
                            <i class="fas fa-plug"></i>
                            <span>Connecting...</span>
                        </div>
                    </div>

                    <!-- Right Panel: Oscilloscope Display -->
                    <div class="scope-panel scope-right">
                        <h2 class="scope-h2">
                            <i class="fas fa-wave-square"></i>
                            Oscilloscope <span id="chTitle">—</span>
                        </h2>
                        <div id="scopePlot"></div>
                        <div class="scope-info" id="info">
                            <i class="fas fa-info-circle"></i> Waiting for data...
                        </div>

                        
                    </div>
                </div>
            </div>

            <script src="https://cdn.plot.ly/plotly-2.35.2.min.js"></script>
            <script>
                // ===== Configuration =====
                const CONFIG = {
                    MAX_SAMPLES: 20000,
                    BLOCK_SIZE: 2500,
                    EFFECTIVE_SAMPLE_RATE: 5000,
                    COLORS: ['#007bff','#dc3545','#28a745','#ffc107','#17a2b8','#6610f2','#e83e8c','#fd7e14','#20c997','#6c757d','#343a40','#f8f9fa','#495057','#adb5bd','#ced4da','#dee2e6'],
                    RECONNECT_DELAY: 3000,
                    SIGNAL_VARIANCE_THRESHOLD: 10
                };

                // ===== State Management =====
                const state = {
                    buffers: Array(16).fill().map(() => ({
                        data: [],
                        valid: false,
                        lastUpdate: 0,
                        hasNonZero: false,
                        stats: { min: 0, max: 0, mean: 0, variance: 0, frequency: 0, amplitude: 0 }
                    })),
                    selected: new Set(),
                    paused: false,
                    frameCount: 0,
                    lastTime: 0,
                    dataRate: 0,
                    ws: null,
                    yRange: { min: -2000, max: 2000 },
                    samples: 10000
                };

                // ===== WebSocket Management =====
                function connectWS() {
                    if (state.ws?.readyState === WebSocket.OPEN) return;

                    try {
                        const proto = location.protocol === 'https:' ? 'wss:' : 'ws:';
                        state.ws = new WebSocket(`${proto}//${location.host}/ws`);
                        state.ws.binaryType = 'arraybuffer';

                        state.ws.onopen = () => updateStatus('Connected', 'scope-connected', 'check-circle');
                        state.ws.onmessage = handleMessage;
                        state.ws.onclose = () => {
                            updateStatus('Disconnected - Reconnecting...', 'scope-disconnected', 'exclamation-circle');
                            setTimeout(connectWS, CONFIG.RECONNECT_DELAY);
                        };
                        state.ws.onerror = () => updateStatus('Connection Error', 'scope-disconnected', 'exclamation-triangle');
                    } catch (err) {
                        updateStatus('Connection Failed', 'scope-disconnected', 'times-circle');
                        setTimeout(connectWS, CONFIG.RECONNECT_DELAY);
                    }
                }

                // ===== Data Processing =====
                function handleMessage(event) {
                    if (state.paused || !(event.data instanceof ArrayBuffer)) return;

                    const now = performance.now();
                    if (state.lastTime > 0) {
                        state.dataRate = 1000 / (now - state.lastTime);
                    }
                    state.lastTime = now;
                    state.frameCount++;

                    const packets = parsePacket(event.data);
                    if (!packets?.blocks?.length) return;

                    packets.blocks.forEach((block, ch) => {
                        if (ch < 16 && block?.length) {
                            updateBuffer(ch, block, now);
                            updateChannelUI(ch);
                        }
                    });

                    plot();
                    updateInfo();
                }

                function parsePacket(buf) {
                    try {
                        const view = new DataView(buf);
                        const HEADER_SIZE = 34;
                        
                        if (buf.byteLength < HEADER_SIZE) return { blocks: [] };

                        const headerOffset = buf.byteLength - HEADER_SIZE;
                        const numChannels = view.getInt16(headerOffset, false);

                        if (numChannels < 1 || numChannels > 16) {
                            return parseDefault(buf, view, headerOffset);
                        }

                        const blockSizes = Array.from({length: 16}, (_, i) => 
                            view.getInt16(headerOffset + 2 + (i * 2), false)
                        );

                        const samples = [];
                        let offset = 8;

                        for (let ch = 0; ch < numChannels; ch++) {
                            const size = blockSizes[ch];
                            const count = size / 2;

                            if (size < 0 || size > 10000 || offset + size > headerOffset) {
                                samples.push(new Int16Array(0));
                                continue;
                            }

                            const arr = new Int16Array(count);
                            for (let i = 0; i < count; i++) {
                                const val = view.getInt16(offset, true);
                                arr[i] = (i === 0 && ch === 0) ? fixFirstSample(val, view.getInt16(offset + 2, true)) : val;
                                offset += 2;
                            }
                            samples.push(arr);
                        }

                        return { blocks: samples };
                    } catch (err) {
                        return { blocks: [] };
                    }
                }

                function parseDefault(buf, view, headerOffset) {
                    const samples = [];
                    let offset = 8;
                    
                    for (let ch = 0; ch < 16; ch++) {
                        if (offset + 5000 > headerOffset) break;
                        
                        const arr = new Int16Array(2500);
                        for (let i = 0; i < 2500; i++) {
                            const val = view.getInt16(offset, true);
                            arr[i] = (i === 0 && ch === 0) ? fixFirstSample(val, view.getInt16(offset + 2, true)) : val;
                            offset += 2;
                        }
                        samples.push(arr);
                    }
                    
                    return { blocks: samples };
                }

                function fixFirstSample(val, nextVal) {
                    return Math.abs(val - nextVal) > 1000 ? nextVal : val;
                }

                // ===== Buffer Management =====
                function updateBuffer(ch, block, timestamp) {
                    const buf = state.buffers[ch];
                    
                    // Check for duplicate
                    if (isDuplicate(buf.data, block)) {
                        buf.lastUpdate = timestamp;
                        return;
                    }
                    
                    // Fix outliers
                    const fixed = Array.from(block);
                    if (fixed.length >= 2 && Math.abs(fixed[0] - fixed[1]) > 1000) {
                        fixed[0] = fixed[1];
                    }
                    
                    // Check signal validity
                    const allZeros = fixed.every(v => v === 0);
                    const allSame = fixed.every(v => v === fixed[0]);
                    
                    if (allZeros || allSame) {
                        buf.data = fixed;
                        buf.valid = true;
                        buf.lastUpdate = timestamp;
                        buf.hasNonZero = false;
                        buf.stats = { min: 0, max: 0, mean: 0, variance: 0, frequency: 0, amplitude: 0 };
                        return;
                    }
                    
                    // Detect discontinuity and insert gap
                    if (buf.data.length > 0 && hasDiscontinuity(buf.data, fixed)) {
                        buf.data.push(NaN);
                    }
                    
                    buf.data = [...buf.data, ...fixed].slice(-CONFIG.MAX_SAMPLES);
                    buf.valid = true;
                    buf.lastUpdate = timestamp;
                    calcStats(buf);
                }

                function isDuplicate(existing, block) {
                    if (existing.length < CONFIG.BLOCK_SIZE || block.length !== CONFIG.BLOCK_SIZE) return false;
                    const last = existing.slice(-CONFIG.BLOCK_SIZE);
                    return last.every((v, i) => v === block[i]);
                }

                function hasDiscontinuity(existing, block) {
                    const lastVal = existing[existing.length - 1];
                    const firstVal = block[0];
                    const delta = Math.abs(lastVal - firstVal);
                    const maxDelta = Math.max(...block.slice(1).map((v, i) => Math.abs(v - block[i])));
                    return maxDelta > 0 && delta > 5 * maxDelta;
                }

                // ===== Statistics =====
                function calcStats(buf) {
                    const recent = buf.data.slice(-5000).filter(v => !isNaN(v) && v >= -32768 && v <= 32767);
                    
                    if (!recent.length) {
                        buf.hasNonZero = false;
                        return;
                    }
                    
                    let min = Infinity, max = -Infinity, sum = 0, sumSq = 0;
                    recent.forEach(v => {
                        if (v < min) min = v;
                        if (v > max) max = v;
                        sum += v;
                        sumSq += v * v;
                    });
                    
                    const mean = sum / recent.length;
                    const variance = (sumSq / recent.length) - (mean * mean);
                    const { frequency, amplitude } = calcFrequency(recent, mean);
                    
                    buf.stats = { min, max, mean, variance, frequency, amplitude };
                    buf.hasNonZero = variance > CONFIG.SIGNAL_VARIANCE_THRESHOLD;
                }

                function calcFrequency(data, mean) {
                    if (data.length < 500) return { frequency: 0, amplitude: 0 };
                    
                    const amplitude = (Math.max(...data) - Math.min(...data)) / 2;
                    if (amplitude < 10) return { frequency: 0, amplitude };
                    
                    const detrended = data.map(v => v - mean);
                    const crossings = [];
                    
                    for (let i = 1; i < detrended.length; i++) {
                        if (detrended[i-1] <= 0 && detrended[i] > 0) {
                            const t = -detrended[i-1] / (detrended[i] - detrended[i-1]);
                            crossings.push(i - 1 + t);
                        }
                    }
                    
                    if (crossings.length < 2) return { frequency: 0, amplitude };
                    
                    const periods = crossings.slice(1).map((c, i) => c - crossings[i]);
                    const median = periods.sort((a, b) => a - b)[Math.floor(periods.length / 2)];
                    const filtered = periods.filter(p => p > median * 0.5 && p < median * 1.5);
                    
                    if (!filtered.length) return { frequency: 0, amplitude };
                    
                    const avgPeriod = filtered.reduce((a, b) => a + b, 0) / filtered.length;
                    const frequency = Math.min(CONFIG.EFFECTIVE_SAMPLE_RATE / avgPeriod, CONFIG.EFFECTIVE_SAMPLE_RATE / 2);
                    
                    return { frequency, amplitude };
                }

                // ===== UI Updates =====
                function updateChannelUI(ch) {
                    const buf = state.buffers[ch];
                    if (!buf.valid || !buf.data.length) return;
                    
                    const last = buf.data[buf.data.length - 1];
                    const s = buf.stats;
                    
                    const set = (id, val) => {
                        const el = document.getElementById(id);
                        if (el) el.textContent = val;
                    };
                    
                    set(`v${ch}`, isNaN(last) ? '—' : last.toFixed(0));
                    set(`n${ch}`, s.min.toFixed(0));
                    set(`x${ch}`, s.max.toFixed(0));
                    set(`f${ch}`, s.frequency > 0.1 ? s.frequency.toFixed(2) + ' Hz' : '—');
                    set(`a${ch}`, s.amplitude > 0 ? s.amplitude.toFixed(1) : '—');

                    const dot = document.getElementById(`d${ch}`);
                    if (dot) {
                        dot.className = buf.hasNonZero ? 'scope-dot scope-dot-green' : 
                                       buf.valid ? 'scope-dot scope-dot-orange' : 'scope-dot scope-dot-gray';
                    }
                }

                function plot() {
                    const container = document.getElementById('scopePlot');
                    
                    if (!state.selected.size) {
                        Plotly.react(container, [], getLayout('Select channels to display'), getConfig());
                        return;
                    }

                    const traces = [];
                    let maxLen = 0;

                    state.selected.forEach(ch => {
                        const len = state.buffers[ch].data.length;
                        if (len > maxLen) maxLen = len;
                    });

                    const displaySamples = Math.min(state.samples, maxLen);

                    state.selected.forEach((ch, idx) => {
                        const y = state.buffers[ch].data.slice(-displaySamples);
                        if (!y.length) return;
                        
                        traces.push({
                            x: Array.from({length: y.length}, (_, i) => i / CONFIG.EFFECTIVE_SAMPLE_RATE * 1000),
                            y: y,
                            name: `CH ${ch}${state.buffers[ch].hasNonZero ? '' : ' (flat)'}`,
                            mode: 'lines',
                            line: { color: CONFIG.COLORS[idx % CONFIG.COLORS.length], width: 1.5 },
                            connectgaps: false
                        });
                    });

                    Plotly.react(container, traces, getLayout(), getConfig());
                }

                function getLayout(title) {
                    const autoY = document.getElementById('autoY').checked;
                    const grid = document.getElementById('grid').checked;
                    const isDark = document.body.classList.contains('dark-mode');
                    
                    return {
                        title: title || `${state.selected.size} channel(s) • ${state.dataRate.toFixed(1)} Hz`,
                        xaxis: { 
                            title: 'Time (ms)', 
                            showgrid: grid,
                            gridcolor: isDark ? '#2a2a2a' : '#e0e0e0',
                            color: isDark ? '#00ff00' : '#00aa00',
                            zerolinecolor: isDark ? '#00ff00' : '#00aa00'
                        },
                        yaxis: { 
                            title: 'ADC Value', 
                            showgrid: grid,
                            gridcolor: isDark ? '#2a2a2a' : '#e0e0e0',
                            color: isDark ? '#00ff00' : '#00aa00',
                            zerolinecolor: isDark ? '#00ff00' : '#00aa00',
                            ...(autoY ? {} : { range: [state.yRange.min, state.yRange.max], autorange: false })
                        },
                        showlegend: false,
                        paper_bgcolor: 'rgba(0,0,0,0)',
                        plot_bgcolor: isDark ? '#0a0a0a' : '#1a1a1a',
                        font: { color: isDark ? '#00ff00' : '#00aa00', family: 'monospace' },
                        margin: { l: 60, r: 30, t: 40, b: 50 }
                    };
                }

                function getConfig() {
                    return { 
                        responsive: true, 
                        displayModeBar: false
                    };
                }

                function updateInfo() {
                    const active = state.buffers.filter(b => b.valid && b.hasNonZero).length;
                    document.getElementById('info').innerHTML = 
                        `<i class="fas fa-info-circle"></i> Frames: ${state.frameCount} • Rate: ${state.dataRate.toFixed(1)} Hz • Active: ${active}/16 • Samples: ${state.samples}`;
                    
                    document.getElementById('statFrames').textContent = state.frameCount;
                    document.getElementById('statRate').textContent = state.dataRate.toFixed(1) + ' Hz';
                    document.getElementById('statActive').textContent = `${active}/16`;
                    document.getElementById('statSamples').textContent = state.samples;
                }

                function updateStatus(msg, cls, icon) {
                    const el = document.getElementById('status');
                    if (el) {
                        el.innerHTML = `<i class="fas fa-${icon}"></i><span>${msg}</span>`;
                        el.className = `scope-status ${cls}`;
                    }
                }

                function updateSelection() {
                    document.querySelectorAll('#chTable tbody tr').forEach((r, i) => {
                        r.classList.toggle('scope-selected', state.selected.has(i));
                    });
                    
                    const list = Array.from(state.selected).sort((a, b) => a - b);
                    document.getElementById('chTitle').textContent = list.length ? 
                        `CH ${list.join(', ')}` : '—';
                }

                // ===== Initialization =====
                function buildTable() {
                    const tbody = document.querySelector('#chTable tbody');
                    tbody.innerHTML = Array.from({length: 16}, (_, i) => `
                        <tr data-ch="${i}">
                            <td><strong>CH${i}</strong></td>
                            <td id="v${i}">—</td>
                            <td id="n${i}">—</td>
                            <td id="x${i}">—</td>
                            <td id="f${i}">—</td>
                            <td id="a${i}">—</td>
                            <td><span class="scope-dot scope-dot-gray" id="d${i}"></span></td>
                        </tr>
                    `).join('');
                    
                    tbody.addEventListener('click', e => {
                        const tr = e.target.closest('tr');
                        if (!tr) return;
                        
                        const ch = parseInt(tr.dataset.ch);
                        if (e.ctrlKey || e.metaKey) {
                            state.selected.has(ch) ? state.selected.delete(ch) : state.selected.add(ch);
                        } else {
                            state.selected = new Set([ch]);
                        }
                        updateSelection();
                        plot();
                    });
                }

                function setupControls() {
                    // Sliders
                    const yMinSlider = document.getElementById('yMinSlider');
                    const yMaxSlider = document.getElementById('yMaxSlider');
                    const samplesSlider = document.getElementById('samplesSlider');
                    
                    yMinSlider.oninput = e => {
                        state.yRange.min = parseInt(e.target.value);
                        document.getElementById('yMinValue').textContent = state.yRange.min;
                        if (!document.getElementById('autoY').checked) plot();
                    };
                    
                    yMaxSlider.oninput = e => {
                        state.yRange.max = parseInt(e.target.value);
                        document.getElementById('yMaxValue').textContent = state.yRange.max;
                        if (!document.getElementById('autoY').checked) plot();
                    };
                    
                    samplesSlider.oninput = e => {
                        state.samples = parseInt(e.target.value);
                        document.getElementById('samplesValue').textContent = state.samples;
                        plot();
                    };
                    
                    // Checkboxes
                    document.getElementById('autoY').onchange = plot;
                    document.getElementById('grid').onchange = plot;
                    
                    // Buttons
                    document.getElementById('pause').onclick = function() {
                        state.paused = !state.paused;
                        this.innerHTML = state.paused ? '<i class="fas fa-play"></i> Resume' : '<i class="fas fa-pause"></i> Pause';
                        this.style.background = state.paused ? '#ffc107' : '';
                    };
                    
                    document.getElementById('clear').onclick = () => {
                        state.buffers.forEach(b => {
                            b.data = [];
                            b.valid = false;
                            b.hasNonZero = false;
                        });
                        state.frameCount = 0;
                        state.dataRate = 0;
                        plot();
                        updateInfo();
                    };
                    
                    document.getElementById('deselect').onclick = () => {
                        state.selected.clear();
                        updateSelection();
                        plot();
                    };
                    
                    document.getElementById('refreshBtn').onclick = () => location.reload();
                }

                function init() {
                    buildTable();
                    setupControls();
                    connectWS();
                    plot();
                    
                    // Theme observer
                    new MutationObserver(plot).observe(document.body, { 
                        attributes: true, 
                        attributeFilter: ['class'] 
                    });
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