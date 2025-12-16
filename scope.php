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
                    <!-- Left Panel (Controls & Channel Info) -->
                    <div class="scope-panel scope-left">
                        <h1 class="scope-h1">📊 Spicer DAQ</h1>
                        
                        <!-- Quick Controls -->
                        <div class="scope-controls">
                            <button class="scope-btn" id="pauseBtn">⏸ Pause</button>
                            <button class="scope-btn" id="clearBtn">Clear</button>
                            <select class="scope-select" id="samplesSelect">
                                <option value="2500">1 Frame</option>
                                <option value="5000">2 Frames</option>
                                <option value="10000" selected>4 Frames</option>
                                <option value="20000">8 Frames</option>
                            </select>
                        </div>

                        <!-- System Stats -->
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
                                <span class="scope-stat-label">Buffer</span>
                                <div class="scope-stat-value" id="statBuffer">0</div>
                            </div>
                        </div>

                        <!-- Channel Monitor Table -->
                        <div class="scope-section">Channel Monitor</div>
                        <div class="channel-table-container">
                            <table class="channel-table" id="channelTable">
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
                                <tbody id="channelTableBody">
                                    <!-- Rows will be populated by JavaScript -->
                                </tbody>
                            </table>
                        </div>

                        <!-- Y-Axis Controls -->
                        <div class="scope-section">Y-Axis Range</div>
                        <div class="scope-y-controls">
                            <div class="y-range-inputs">
                                <input type="number" id="yMin" value="-2000" step="100" min="-32768" max="32767">
                                <span>to</span>
                                <input type="number" id="yMax" value="2000" step="100" min="-32768" max="32767">
                                <button class="scope-btn" id="applyY">Apply</button>
                            </div>
                        </div>

                        <!-- Connection Status -->
                        <div class="scope-status" id="connectionStatus">
                            <span id="statusIcon">🔌</span>
                            <span id="statusText">Connecting...</span>
                        </div>
                    </div>

                    <!-- Right Panel (Visualization) -->
                    <div class="scope-panel scope-right">
                        <!-- Plot Header -->
                        <div class="plot-header">
                            <h2 class="scope-h2">Oscilloscope <span id="chTitle">—</span></h2>
                            <div class="plot-controls">
                                <label class="plot-checkbox">
                                    <input type="checkbox" id="autoYCheck" checked>
                                    <span>Auto Y</span>
                                </label>
                                <label class="plot-checkbox">
                                    <input type="checkbox" id="gridCheck" checked>
                                    <span>Grid</span>
                                </label>
                                <label class="plot-checkbox">
                                    <input type="checkbox" id="legendCheck" checked>
                                    <span>Legend</span>
                                </label>
                            </div>
                        </div>
                        
                        <!-- Canvas Container -->
                        <div class="canvas-container">
                            <canvas id="scopeCanvas"></canvas>
                        </div>
                        
                        <!-- Plot Info -->
                        <div class="plot-info">
                            <div id="plotStatus">⏳ Waiting for data...</div>
                            <div id="plotStats">0 samples | 0 Hz | 0 ms</div>
                        </div>
                    </div>
                </div>
            </div>

            <script>
                // Configuration
                const CONFIG = {
                    CHANNELS: 16,
                    SAMPLES_PER_CHANNEL: 2500,
                    MAX_SAMPLES: 20000,
                    UPDATE_RATE: 30, // 30 FPS
                    SAMPLE_RATE: 10000,
                    DECIMATION: 20,
                    EFFECTIVE_RATE: 500,
                    COLORS: [
                        '#1976d2', '#e91e63', '#4caf50', '#ff9800',
                        '#9c27b0', '#00bcd4', '#f44336', '#8bc34a',
                        '#ff5722', '#607d8b', '#795548', '#cddc39',
                        '#009688', '#ffc107', '#673ab7', '#03a9f4'
                    ]
                };

                // State Management
                const State = {
                    paused: false,
                    buffers: Array(CONFIG.CHANNELS).fill().map(() => []),
                    channelStats: Array(CONFIG.CHANNELS).fill().map(() => ({
                        current: 0,
                        min: 0,
                        max: 0,
                        freq: 0,
                        amp: 0,
                        active: false,
                        lastUpdate: 0
                    })),
                    selectedChannels: new Set([0]),
                    yRange: { min: -2000, max: 2000 },
                    frameCount: 0,
                    dataRate: 0,
                    lastUpdateTime: 0,
                    activeCount: 0,
                    ws: null,
                    canvas: null,
                    ctx: null,
                    rafId: null
                };

                // DOM Elements Cache
                const DOM = {
                    canvas: () => document.getElementById('scopeCanvas'),
                    channelTableBody: () => document.getElementById('channelTableBody'),
                    connectionStatus: () => document.getElementById('connectionStatus'),
                    statusIcon: () => document.getElementById('statusIcon'),
                    statusText: () => document.getElementById('statusText'),
                    chTitle: () => document.getElementById('chTitle'),
                    statFrames: () => document.getElementById('statFrames'),
                    statRate: () => document.getElementById('statRate'),
                    statActive: () => document.getElementById('statActive'),
                    statBuffer: () => document.getElementById('statBuffer'),
                    plotStatus: () => document.getElementById('plotStatus'),
                    plotStats: () => document.getElementById('plotStats')
                };

                // Initialize Application
                class ScopeApp {
                    constructor() {
                        this.initCanvas();
                        this.buildChannelTable();
                        this.setupEventListeners();
                        this.connectWebSocket();
                        this.startAnimation();
                    }

                    // Canvas Setup
                    initCanvas() {
                        State.canvas = DOM.canvas();
                        State.ctx = State.canvas.getContext('2d');
                        this.resizeCanvas();
                        window.addEventListener('resize', () => this.resizeCanvas());
                    }

                    resizeCanvas() {
                        const container = State.canvas.parentElement;
                        State.canvas.width = container.clientWidth;
                        State.canvas.height = container.clientHeight;
                        this.draw();
                    }

                    // Channel Table
                    buildChannelTable() {
                        const tbody = DOM.channelTableBody();
                        tbody.innerHTML = '';
                        
                        for (let i = 0; i < CONFIG.CHANNELS; i++) {
                            const row = document.createElement('tr');
                            row.dataset.channel = i;
                            row.innerHTML = `
                                <td><strong>CH${i}</strong></td>
                                <td class="channel-value" id="val${i}">—</td>
                                <td class="channel-min" id="min${i}">—</td>
                                <td class="channel-max" id="max${i}">—</td>
                                <td class="channel-freq" id="freq${i}">—</td>
                                <td class="channel-amp" id="amp${i}">—</td>
                                <td>
                                    <span class="channel-dot" id="dot${i}" title="No data">●</span>
                                </td>
                            `;
                            
                            // Click handler for channel selection
                            row.addEventListener('click', (e) => {
                                if (e.target.tagName === 'INPUT') return;
                                
                                const channel = parseInt(row.dataset.channel);
                                const ctrl = e.ctrlKey || e.metaKey;
                                const shift = e.shiftKey;
                                
                                if (shift) {
                                    // Select range
                                    const lastSelected = Math.max(...State.selectedChannels);
                                    const start = Math.min(lastSelected, channel);
                                    const end = Math.max(lastSelected, channel);
                                    for (let ch = start; ch <= end; ch++) {
                                        State.selectedChannels.add(ch);
                                    }
                                } else if (ctrl) {
                                    // Toggle selection
                                    if (State.selectedChannels.has(channel)) {
                                        State.selectedChannels.delete(channel);
                                    } else {
                                        State.selectedChannels.add(channel);
                                    }
                                } else {
                                    // Single selection
                                    State.selectedChannels.clear();
                                    State.selectedChannels.add(channel);
                                }
                                
                                this.updateSelectionDisplay();
                                this.draw();
                            });
                            
                            tbody.appendChild(row);
                        }
                    }

                    // WebSocket Connection
                    connectWebSocket() {
                        if (State.ws?.readyState === WebSocket.OPEN) return;
                        
                        try {
                            const protocol = location.protocol === 'https:' ? 'wss:' : 'ws:';
                            State.ws = new WebSocket(`${protocol}//${location.host}/ws`);
                            State.ws.binaryType = 'arraybuffer';
                            
                            State.ws.onopen = () => this.updateStatus('🟢 Connected', 'connected');
                            State.ws.onmessage = (e) => this.processData(e.data);
                            State.ws.onclose = () => {
                                this.updateStatus('🔴 Disconnected', 'disconnected');
                                setTimeout(() => this.connectWebSocket(), 3000);
                            };
                            State.ws.onerror = () => this.updateStatus('⚠️ Error', 'error');
                        } catch (err) {
                            console.error('WebSocket error:', err);
                            this.updateStatus('🔴 Failed', 'error');
                            setTimeout(() => this.connectWebSocket(), 3000);
                        }
                    }

                    // Data Processing
                    processData(data) {
                        if (State.paused || !(data instanceof ArrayBuffer)) return;
                        
                        const now = performance.now();
                        const dt = now - State.lastUpdateTime;
                        State.lastUpdateTime = now;
                        State.frameCount++;
                        
                        // Calculate data rate (Hz)
                        if (dt > 0) {
                            State.dataRate = 1000 / dt;
                        }
                        
                        try {
                            const view = new DataView(data);
                            const samplesPerChannel = data.byteLength / (CONFIG.CHANNELS * 2);
                            
                            State.activeCount = 0;
                            
                            for (let ch = 0; ch < CONFIG.CHANNELS; ch++) {
                                const channelData = [];
                                
                                // Read channel data
                                for (let i = 0; i < samplesPerChannel; i++) {
                                    const offset = (ch * samplesPerChannel + i) * 2;
                                    const value = view.getInt16(offset, true);
                                    channelData.push(value);
                                }
                                
                                // Update buffer
                                this.updateChannelBuffer(ch, channelData);
                                
                                // Calculate statistics
                                this.updateChannelStats(ch);
                                
                                // Update table display
                                this.updateChannelDisplay(ch);
                            }
                            
                            // Update UI
                            this.updateStatsDisplay();
                            this.updatePlotInfo();
                            
                        } catch (err) {
                            console.error('Data processing error:', err);
                        }
                    }

                    // Channel Buffer Management
                    updateChannelBuffer(channel, newData) {
                        if (!newData || newData.length === 0) return;
                        
                        const buffer = State.buffers[channel];
                        
                        // Add new data
                        buffer.push(...newData);
                        
                        // Trim buffer to max size
                        if (buffer.length > CONFIG.MAX_SAMPLES) {
                            State.buffers[channel] = buffer.slice(-CONFIG.MAX_SAMPLES);
                        }
                    }

                    // Channel Statistics
                    updateChannelStats(channel) {
                        const buffer = State.buffers[channel];
                        if (buffer.length < 100) return;
                        
                        const stats = State.channelStats[channel];
                        const recentData = buffer.slice(-1000); // Use last 1000 samples
                        
                        // Basic stats
                        let min = Infinity, max = -Infinity, sum = 0;
                        for (const val of recentData) {
                            if (val < min) min = val;
                            if (val > max) max = val;
                            sum += val;
                        }
                        
                        stats.current = recentData[recentData.length - 1] || 0;
                        stats.min = min;
                        stats.max = max;
                        stats.amp = (max - min) / 2;
                        
                        // Frequency calculation
                        const freq = this.calculateFrequency(recentData);
                        stats.freq = freq;
                        
                        // Activity detection
                        stats.active = (max - min) > 10 && Math.abs(stats.amp) > 5;
                        if (stats.active) State.activeCount++;
                        
                        stats.lastUpdate = Date.now();
                    }

                    // Frequency Calculation
                    calculateFrequency(data) {
                        if (data.length < 100) return 0;
                        
                        // Detrend data
                        const mean = data.reduce((a, b) => a + b, 0) / data.length;
                        const detrended = data.map(v => v - mean);
                        
                        // Find zero crossings
                        const crossings = [];
                        let lastSign = Math.sign(detrended[0]);
                        
                        for (let i = 1; i < detrended.length; i++) {
                            const currentSign = Math.sign(detrended[i]);
                            
                            if (lastSign <= 0 && currentSign > 0) {
                                // Linear interpolation for better accuracy
                                const t = -detrended[i-1] / (detrended[i] - detrended[i-1]);
                                crossings.push(i - 1 + t);
                            }
                            lastSign = currentSign;
                        }
                        
                        if (crossings.length < 2) return 0;
                        
                        // Calculate average period
                        const periods = [];
                        for (let i = 1; i < crossings.length; i++) {
                            periods.push(crossings[i] - crossings[i-1]);
                        }
                        
                        const avgPeriod = periods.reduce((a, b) => a + b, 0) / periods.length;
                        const frequency = CONFIG.EFFECTIVE_RATE / avgPeriod;
                        
                        return frequency > 0.1 ? frequency : 0;
                    }

                    // Update Channel Display
                    updateChannelDisplay(channel) {
                        const stats = State.channelStats[channel];
                        
                        // Update table cells
                        document.getElementById(`val${channel}`).textContent = 
                            stats.current.toFixed(0);
                        document.getElementById(`min${channel}`).textContent = 
                            stats.min.toFixed(0);
                        document.getElementById(`max${channel}`).textContent = 
                            stats.max.toFixed(0);
                        document.getElementById(`freq${channel}`).textContent = 
                            stats.freq > 0 ? stats.freq.toFixed(2) + ' Hz' : '—';
                        document.getElementById(`amp${channel}`).textContent = 
                            stats.amp > 0 ? stats.amp.toFixed(1) : '—';
                        
                        // Update status dot
                        const dot = document.getElementById(`dot${channel}`);
                        if (dot) {
                            if (stats.active) {
                                dot.className = 'channel-dot active';
                                dot.title = 'Active signal';
                            } else if (stats.lastUpdate > Date.now() - 5000) {
                                dot.className = 'channel-dot inactive';
                                dot.title = 'Flat signal';
                            } else {
                                dot.className = 'channel-dot';
                                dot.title = 'No data';
                            }
                        }
                        
                        // Update row selection
                        const row = document.querySelector(`tr[data-channel="${channel}"]`);
                        if (row) {
                            row.classList.toggle('selected', State.selectedChannels.has(channel));
                        }
                    }

                    // Drawing Functions
                    draw() {
                        if (!State.ctx || State.canvas.width === 0) return;
                        
                        const ctx = State.ctx;
                        const width = State.canvas.width;
                        const height = State.canvas.height;
                        
                        // Clear canvas
                        ctx.clearRect(0, 0, width, height);
                        
                        // Draw grid if enabled
                        if (document.getElementById('gridCheck').checked) {
                            this.drawGrid(width, height);
                        }
                        
                        // Draw signals
                        if (State.selectedChannels.size > 0) {
                            this.drawSignals(width, height);
                        } else {
                            this.drawNoSignalMessage(width, height);
                        }
                        
                        // Draw axes
                        this.drawAxes(width, height);
                    }

                    drawGrid(width, height) {
                        const ctx = State.ctx;
                        ctx.strokeStyle = 'rgba(128, 128, 128, 0.15)';
                        ctx.lineWidth = 1;
                        
                        // Vertical lines
                        for (let x = 0; x < width; x += width / 20) {
                            ctx.beginPath();
                            ctx.moveTo(x, 0);
                            ctx.lineTo(x, height);
                            ctx.stroke();
                        }
                        
                        // Horizontal lines
                        for (let y = 0; y < height; y += height / 10) {
                            ctx.beginPath();
                            ctx.moveTo(0, y);
                            ctx.lineTo(width, y);
                            ctx.stroke();
                        }
                    }

                    drawAxes(width, height) {
                        const ctx = State.ctx;
                        ctx.strokeStyle = 'rgba(255, 255, 255, 0.5)';
                        ctx.lineWidth = 2;
                        
                        // X-axis
                        ctx.beginPath();
                        ctx.moveTo(50, height / 2);
                        ctx.lineTo(width - 20, height / 2);
                        ctx.stroke();
                        
                        // Y-axis
                        ctx.beginPath();
                        ctx.moveTo(50, 20);
                        ctx.lineTo(50, height - 20);
                        ctx.stroke();
                    }

                    drawSignals(width, height) {
                        const ctx = State.ctx;
                        const samplesToShow = parseInt(document.getElementById('samplesSelect').value);
                        const autoY = document.getElementById('autoYCheck').checked;
                        
                        // Calculate Y range
                        let yMin = State.yRange.min;
                        let yMax = State.yRange.max;
                        
                        if (autoY) {
                            yMin = Infinity;
                            yMax = -Infinity;
                            
                            for (const ch of State.selectedChannels) {
                                const buffer = State.buffers[ch]?.slice(-samplesToShow) || [];
                                for (const val of buffer) {
                                    if (val < yMin) yMin = val;
                                    if (val > yMax) yMax = val;
                                }
                            }
                            
                            // Add padding
                            const range = Math.max(yMax - yMin, 1);
                            yMin -= range * 0.1;
                            yMax += range * 0.1;
                        }
                        
                        const yRange = yMax - yMin || 1;
                        
                        // Draw each selected channel
                        let colorIndex = 0;
                        for (const ch of State.selectedChannels) {
                            const buffer = State.buffers[ch]?.slice(-samplesToShow) || [];
                            if (buffer.length === 0) continue;
                            
                            ctx.strokeStyle = CONFIG.COLORS[colorIndex % CONFIG.COLORS.length];
                            ctx.lineWidth = 2;
                            ctx.beginPath();
                            
                            for (let i = 0; i < buffer.length; i++) {
                                const x = 50 + (i / buffer.length) * (width - 70);
                                const y = 20 + ((yMax - buffer[i]) / yRange) * (height - 40);
                                
                                if (i === 0 || isNaN(buffer[i]) || isNaN(buffer[i-1])) {
                                    ctx.moveTo(x, y);
                                } else {
                                    ctx.lineTo(x, y);
                                }
                            }
                            
                            ctx.stroke();
                            colorIndex++;
                        }
                        
                        // Draw legend if enabled
                        if (document.getElementById('legendCheck').checked && State.selectedChannels.size > 0) {
                            this.drawLegend();
                        }
                    }

                    drawLegend() {
                        const ctx = State.ctx;
                        const legendX = State.canvas.width - 150;
                        let legendY = 30;
                        
                        ctx.font = '12px monospace';
                        ctx.textAlign = 'left';
                        ctx.fillStyle = 'rgba(255, 255, 255, 0.8)';
                        
                        let index = 0;
                        for (const ch of State.selectedChannels) {
                            const stats = State.channelStats[ch];
                            const color = CONFIG.COLORS[index % CONFIG.COLORS.length];
                            
                            // Color box
                            ctx.fillStyle = color;
                            ctx.fillRect(legendX, legendY - 8, 12, 12);
                            
                            // Text
                            ctx.fillStyle = 'rgba(255, 255, 255, 0.8)';
                            ctx.fillText(`CH${ch}: ${stats.current} (${stats.freq.toFixed(1)}Hz)`, 
                                        legendX + 18, legendY);
                            
                            legendY += 20;
                            index++;
                        }
                    }

                    drawNoSignalMessage(width, height) {
                        const ctx = State.ctx;
                        ctx.font = '16px Arial';
                        ctx.fillStyle = 'rgba(255, 255, 255, 0.5)';
                        ctx.textAlign = 'center';
                        ctx.textBaseline = 'middle';
                        ctx.fillText('Select channels to display', width / 2, height / 2);
                    }

                    // UI Updates
                    updateSelectionDisplay() {
                        const channels = Array.from(State.selectedChannels).sort((a, b) => a - b);
                        DOM.chTitle().textContent = channels.length ? `CH ${channels.join(', ')}` : '—';
                    }

                    updateStatsDisplay() {
                        DOM.statFrames().textContent = State.frameCount;
                        DOM.statRate().textContent = State.dataRate.toFixed(1) + ' Hz';
                        DOM.statActive().textContent = `${State.activeCount}/${CONFIG.CHANNELS}`;
                        DOM.statBuffer().textContent = State.buffers[0]?.length || 0;
                    }

                    updatePlotInfo() {
                        const samples = parseInt(document.getElementById('samplesSelect').value);
                        const channels = State.selectedChannels.size;
                        
                        if (channels > 0) {
                            DOM.plotStatus().textContent = 
                                `Displaying ${channels} channel${channels > 1 ? 's' : ''}`;
                            DOM.plotStats().textContent = 
                                `${samples} samples | ${State.dataRate.toFixed(1)} Hz | ${State.activeCount} active`;
                        }
                    }

                    updateStatus(text, type) {
                        DOM.statusIcon().textContent = text.substring(0, 2);
                        DOM.statusText().textContent = text.substring(3);
                        DOM.connectionStatus().className = `scope-status ${type}`;
                    }

                    // Event Listeners
                    setupEventListeners() {
                        // Pause button
                        document.getElementById('pauseBtn').addEventListener('click', () => {
                            State.paused = !State.paused;
                            const btn = document.getElementById('pauseBtn');
                            btn.textContent = State.paused ? '▶ Resume' : '⏸ Pause';
                            btn.classList.toggle('paused', State.paused);
                        });
                        
                        // Clear button
                        document.getElementById('clearBtn').addEventListener('click', () => {
                            State.buffers = Array(CONFIG.CHANNELS).fill().map(() => []);
                            State.frameCount = 0;
                            State.dataRate = 0;
                            State.activeCount = 0;
                            this.updateStatsDisplay();
                        });
                        
                        // Y-axis apply
                        document.getElementById('applyY').addEventListener('click', () => {
                            const yMin = parseInt(document.getElementById('yMin').value);
                            const yMax = parseInt(document.getElementById('yMax').value);
                            
                            if (!isNaN(yMin) && !isNaN(yMax) && yMin < yMax) {
                                State.yRange.min = yMin;
                                State.yRange.max = yMax;
                                this.draw();
                            }
                        });
                        
                        // Sample count change
                        document.getElementById('samplesSelect').addEventListener('change', () => {
                            this.draw();
                        });
                        
                        // Display controls
                        ['autoYCheck', 'gridCheck', 'legendCheck'].forEach(id => {
                            document.getElementById(id).addEventListener('change', () => {
                                this.draw();
                            });
                        });
                        
                        // Deselect all channels
                        document.addEventListener('keydown', (e) => {
                            if (e.key === 'Escape') {
                                State.selectedChannels.clear();
                                this.updateSelectionDisplay();
                                this.draw();
                            }
                        });
                    }

                    // Animation Loop
                    startAnimation() {
                        const animate = () => {
                            if (!State.paused) {
                                this.draw();
                            }
                            State.rafId = requestAnimationFrame(animate);
                        };
                        State.rafId = requestAnimationFrame(animate);
                    }

                    // Cleanup
                    cleanup() {
                        if (State.ws) State.ws.close();
                        if (State.rafId) cancelAnimationFrame(State.rafId);
                    }
                }

                // Initialize when DOM is ready
                let app;
                document.addEventListener('DOMContentLoaded', () => {
                    app = new ScopeApp();
                });
                
                window.addEventListener('beforeunload', () => {
                    if (app) app.cleanup();
                });
            </script>
        </div>
    </section>
</div>

<?php include_once './footer.php'; ?>
</body>
</html>