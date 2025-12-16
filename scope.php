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
                }

                .scope-connected {
                    background: linear-gradient(135deg, #d4edda, #c3e6cb);
                    color: #155724;
                }

                .scope-disconnected {
                    background: linear-gradient(135deg, #f8d7da, #f5c6cb);
                    color: #721c24;
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
                }

                .scope-info {
                    margin-top: 16px;
                    padding: 14px;
                    background: var(--border);
                    border-radius: 8px;
                    font-family: monospace;
                    font-size: 13px;
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
                                <option value="2500">1 Frame</option>
                                <option value="5000">2 Frames</option>
                                <option value="10000" selected>4 Frames</option>
                                <option value="20000">8 Frames</option>
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
                const CONFIG = {
                    MAX_SAMPLES: 20000,
                    BLOCK_SIZE: 2500,
                    EFFECTIVE_RATE: 500,
                    COLORS: ['#1976d2', '#e91e63', '#4caf50', '#ff9800', '#9c27b0', '#00bcd4', '#f44336', '#8bc34a'],
                    RECONNECT: 3000
                };
                const buffers = Array(16).fill().map(() => ({
                    data: [],
                    valid: false,
                    stats: {
                        min: 0,
                        max: 0,
                        freq: 0,
                        amp: 0
                    }
                }));
                let selected = new Set(),
                    paused = false,
                    frameCount = 0,
                    lastTime = 0,
                    rate = 0,
                    ws = null,
                    yRange = {
                        min: -2000,
                        max: 2000
                    };

                // Theme
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

                // Build table
                function buildTable() {
                    const tbody = document.querySelector('#chTable tbody');
                    tbody.innerHTML = '';
                    for (let i = 0; i < 16; i++) {
                        const tr = document.createElement('tr');
                        tr.innerHTML = `<td><strong>CH${i}</strong></td><td id="v${i}">—</td><td id="n${i}">—</td><td id="x${i}">—</td><td id="f${i}">—</td><td id="a${i}">—</td><td><span class="scope-dot scope-dot-gray" id="d${i}"></span></td>`;
                        tr.onclick = (e) => {
                            if (e.ctrlKey || e.metaKey) selected.has(i) ? selected.delete(i) : selected.add(i);
                            else selected = new Set([i]);
                            updateSel();
                            plot();
                        };
                        tbody.appendChild(tr);
                    }
                }

                // WebSocket
                function connect() {
                    if (ws && ws.readyState === WebSocket.OPEN) return;
                    const proto = location.protocol === 'https:' ? 'wss:' : 'ws:';
                    ws = new WebSocket(proto + '//' + location.host + '/ws');
                    ws.binaryType = 'arraybuffer';
                    ws.onopen = () => updateStatus('🟢 Connected', 'scope-connected');
                    ws.onmessage = (e) => {
                        if (!paused && e.data instanceof ArrayBuffer) processData(e.data);
                    };
                    ws.onclose = () => {
                        updateStatus('🔴 Reconnecting...', 'scope-disconnected');
                        setTimeout(connect, CONFIG.RECONNECT);
                    };
                }

                // Process data
                function processData(buf) {
                    const now = performance.now();
                    if (lastTime > 0) rate = 1000 / (now - lastTime);
                    lastTime = now;
                    frameCount++;

                    const result = parse(buf);
                    if (!result?.blocks) return;

                    for (let ch = 0; ch < result.blocks.length && ch < 16; ch++) {
                        if (result.blocks[ch]?.length > 0) {
                            updateBuf(ch, result.blocks[ch]);
                            updateDisplay(ch);
                        }
                    }
                    plot();
                    updateInfo();
                }

                // Parse packet
                function parse(buf) {
                    try {
                        const view = new DataView(buf);
                        const h = buf.byteLength - 34;
                        if (buf.byteLength < 34) return {
                            blocks: []
                        };
                        const nc = view.getInt16(h, false);
                        if (nc < 1 || nc > 16) return {
                            blocks: []
                        };
                        const samples = [];
                        let off = 8;
                        for (let ch = 0; ch < nc; ch++) {
                            const size = view.getInt16(h + 2 + ch * 2, false);
                            const n = size / 2;
                            if (size < 0 || off + size > h) {
                                samples.push(new Int16Array(0));
                                continue;
                            }
                            const arr = new Int16Array(n);
                            for (let i = 0; i < n; i++) {
                                arr[i] = view.getInt16(off, true);
                                off += 2;
                            }
                            samples.push(arr);
                        }
                        return {
                            blocks: samples
                        };
                    } catch {
                        return {
                            blocks: []
                        };
                    }
                }

                // Update buffer
                function updateBuf(ch, block) {
                    const b = buffers[ch];
                    const fixed = Array.from(block);
                    if (fixed.length >= 2 && Math.abs(fixed[0] - fixed[1]) > 1000) fixed[0] = fixed[1];

                    let allZero = true,
                        allSame = true;
                    for (let i = 0; i < fixed.length; i++) {
                        if (fixed[i] !== 0) allZero = false;
                        if (fixed[i] !== fixed[0]) allSame = false;
                    }

                    b.data = [...b.data, ...fixed].slice(-CONFIG.MAX_SAMPLES);
                    b.valid = true;

                    if (allZero || allSame) {
                        b.stats = {
                            min: 0,
                            max: 0,
                            freq: 0,
                            amp: 0
                        };
                        return;
                    }

                    let min = Infinity,
                        max = -Infinity;
                    for (let v of fixed) {
                        if (v < min) min = v;
                        if (v > max) max = v;
                    }
                    const amp = (max - min) / 2;
                    b.stats = {
                        min,
                        max,
                        freq: 0,
                        amp
                    };
                }

                // Update display
                function updateDisplay(ch) {
                    const b = buffers[ch];
                    if (!b.valid) return;
                    const last = b.data[b.data.length - 1];
                    const v = document.getElementById(`v${ch}`);
                    const n = document.getElementById(`n${ch}`);
                    const x = document.getElementById(`x${ch}`);
                    const f = document.getElementById(`f${ch}`);
                    const a = document.getElementById(`a${ch}`);
                    const d = document.getElementById(`d${ch}`);

                    if (v) v.textContent = last?.toFixed(0) || '—';
                    if (n) n.textContent = b.stats.min.toFixed(0);
                    if (x) x.textContent = b.stats.max.toFixed(0);
                    if (f) f.textContent = '—';
                    if (a) a.textContent = b.stats.amp > 0 ? b.stats.amp.toFixed(1) : '—';
                    if (d) d.className = b.stats.amp > 10 ? 'scope-dot scope-dot-green' : 'scope-dot scope-dot-orange';
                }

                // Plot
                function plot() {
                    if (!selected.size) {
                        Plotly.react('scopePlot', [], {
                            title: 'Select channels'
                        });
                        return;
                    }
                    const n = parseInt(document.getElementById('samples').value);
                    const traces = [];
                    let i = 0;
                    for (const ch of selected) {
                        const y = buffers[ch].data.slice(-n);
                        if (!y.length) continue;
                        traces.push({
                            x: Array.from({
                                length: y.length
                            }, (_, i) => i),
                            y,
                            name: `CH ${ch}`,
                            mode: 'lines',
                            line: {
                                color: CONFIG.COLORS[i % 8],
                                width: 2
                            }
                        });
                        i++;
                    }
                    const autoY = document.getElementById('autoY').checked;
                    const yaxis = {
                        title: 'ADC',
                        showgrid: document.getElementById('grid').checked
                    };
                    if (!autoY) {
                        yaxis.range = [yRange.min, yRange.max];
                        yaxis.autorange = false;
                    }
                    Plotly.react('scopePlot', traces, {
                        title: `${traces.length} ch • ${rate.toFixed(1)} Hz`,
                        xaxis: {
                            title: 'Sample'
                        },
                        yaxis,
                        showlegend: document.getElementById('legend').checked,
                        paper_bgcolor: 'rgba(0,0,0,0)',
                        plot_bgcolor: 'rgba(0,0,0,0)',
                        font: {
                            color: getComputedStyle(body).getPropertyValue('--text')
                        }
                    }, {
                        responsive: true
                    });
                }

                // UI updates
                function updateSel() {
                    document.querySelectorAll('#chTable tbody tr').forEach((r, i) => r.classList.toggle('scope-selected', selected.has(i)));
                    const list = Array.from(selected).sort((a, b) => a - b);
                    document.getElementById('chTitle').textContent = list.length ? `CH ${list.join(', ')}` : '—';
                }

                function updateInfo() {
                    const active = buffers.filter(b => b.valid && b.stats.amp > 10).length;
                    document.getElementById('info').textContent = `Frames: ${frameCount} • Rate: ${rate.toFixed(1)} Hz • Active: ${active}/16`;
                    document.getElementById('statFrames').textContent = frameCount;
                    document.getElementById('statRate').textContent = rate.toFixed(1) + ' Hz';
                    document.getElementById('statActive').textContent = `${active}/16`;
                    document.getElementById('statRead').textContent = '0.0 ms';
                }

                function updateStatus(msg, cls) {
                    const s = document.getElementById('status');
                    s.innerHTML = `<span>${msg.split(' ')[0]}</span><span>${msg.substring(msg.indexOf(' ')+1)}</span>`;
                    s.className = `scope-status ${cls}`;
                }

                // Controls
                document.getElementById('samples').onchange = plot;
                document.getElementById('autoY').onchange = plot;
                document.getElementById('grid').onchange = plot;
                document.getElementById('legend').onchange = plot;
                document.getElementById('applyY').onclick = () => {
                    yRange.min = parseInt(document.getElementById('yMin').value);
                    yRange.max = parseInt(document.getElementById('yMax').value);
                    plot();
                };
                document.getElementById('clear').onclick = () => {
                    buffers.forEach(b => {
                        b.data = [];
                        b.valid = false;
                    });
                    frameCount = 0;
                    rate = 0;
                    plot();
                    updateInfo();
                };
                document.getElementById('pause').onclick = function() {
                    paused = !paused;
                    this.textContent = paused ? '▶️ Resume' : '⏸ Pause';
                };
                document.getElementById('deselect').onclick = () => {
                    selected.clear();
                    updateSel();
                    plot();
                };
                document.getElementById('refreshBtn').onclick = () => location.reload();

                // Init
                buildTable();
                connect();
                plot();
            </script>
        </div>
    </section>
</div>

<?php include_once './footer.php'; ?>
</body>

</html>