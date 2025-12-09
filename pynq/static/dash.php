<?php
// Configuration parameters
$config = [
    'title' => 'PYNQ 16-Channel Real-Time Oscilloscope',
    'channels' => 16,
    'default_display_samples' => 2500,
    'max_display_samples' => 15000,
    'colors' => [
        '#1976d2', '#e91e63', '#4caf50', '#ff9800', '#9c27b0', 
        '#00bcd4', '#f44336', '#8bc34a', '#ff5722', '#607d8b',
        '#795548', '#cddc39', '#009688', '#ffc107', '#673ab7', '#03a9f4'
    ],
    'display_samples_options' => [2500, 2500, 5000],
    'websocket_url' => "ws://" . $_SERVER['HTTP_HOST'] . "/ws"
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title><?php echo htmlspecialchars($config['title']); ?></title>
<script src="https://cdn.jsdelivr.net/npm/echarts@5.4.3/dist/echarts.min.js"></script>
<style>
  :root {
    --bg: #f5f5f5; --panel: white; --text: #333; --border: #ddd;
    --accent: #1976d2; --grid: #e0e0e0; --dot-red: #F44336; --dot-green: #4CAF50;
  }
  [data-theme="dark"] {
    --bg: #121212; --panel: #1e1e1e; --text: #e0e0e0; --border: #333;
    --accent: #42a5f5; --grid: #333; --dot-red: #f44336; --dot-green: #4caf50;
  }
  body { 
    font-family: 'Segoe UI', Arial, sans-serif; 
    margin: 0; padding: 20px; 
    background: var(--bg); color: var(--text);
    transition: background 0.3s, color 0.3s;
  }
  .container { 
    display: flex; gap: 20px; max-width: 1700px; margin: 0 auto; 
  }
  .channels-panel {
    flex: 0 0 360px; background: var(--panel); padding: 18px; border-radius: 12px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.15); max-height: 95vh; overflow-y: auto;
  }
  .scope-panel { 
    flex: 1; background: var(--panel); padding: 18px; border-radius: 12px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.15);
  }
  h2 { color: var(--accent); margin: 0 0 15px 0; }
  .controls {
    background: rgba(0,0,0,0.05); padding: 14px; border-radius: 8px; margin-bottom: 15px;
    display: flex; flex-wrap: wrap; gap: 12px; align-items: center;
  }
  button, select, label {
    font-size: 14px; padding: 8px 14px; border-radius: 6px; border: none;
    background: rgba(0,0,0,0.1); color: var(--text);
  }
  button { cursor: pointer; }
  button:hover { background: var(--accent); color: white; }
  .status-bar {
    padding: 12px; border-radius: 8px; text-align: center; font-weight: bold;
    margin-top: 15px; background: #e8f5e8; color: #0b5e0b;
  }
  .channel-table {
    width: 100%; border-collapse: collapse; font-size: 13px; margin-top: 10px;
  }
  .channel-table th, .channel-table td {
    padding: 8px; text-align: center; border: 1px solid var(--border);
  }
  .channel-table th { background: rgba(0,0,0,0.08); font-weight: bold; }
  .channel-row {
    cursor: pointer; transition: all 0.2s;
  }
  .channel-row:hover { background: rgba(25,118,210,0.15); }
  .channel-row.selected {
    background: rgba(25,118,210,0.3); font-weight: bold;
  }
  .status-dot {
    display: inline-block; width: 13px; height: 13px; border-radius: 50%;
    box-shadow: 0 0 8px currentColor;
  }
  .dot-red { background: var(--dot-red); animation: pulse 2s infinite; }
  .dot-green { background: var(--dot-green); }
  @keyframes pulse { 0%,100% { opacity:1; } 50% { opacity:0.6; } }
  #scopeChart { width: 100%; height: 580px; }
  .info-panel {
    margin-top: 15px; padding: 14px; background: rgba(255,248,225,0.3);
    border-radius: 8px; font-family: monospace; font-size: 13px;
  }
  .theme-toggle {
    position: fixed; top: 15px; right: 15px; z-index: 1000;
    background: var(--panel); padding: 10px; border-radius: 50px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.2);
  }
</style>
</head>
<body>

<div class="theme-toggle">
  <button id="themeBtn">Dark Mode</button>
</div>

<div class="container">
  <!-- Channels Panel -->
  <div class="channels-panel">
    <h2><?php echo $config['channels']; ?>-Channel DAQ</h2>
    <div class="controls">
      <select id="displaySamples">
        <?php foreach ($config['display_samples_options'] as $samples): ?>
          <option value="<?php echo $samples; ?>" <?php echo $samples == $config['default_display_samples'] ? 'selected' : ''; ?>>
            <?php echo $samples; ?>
          </option>
        <?php endforeach; ?>
      </select>
      <button id="clearBtn">Clear</button>
      <button id="pauseBtn">Pause</button>
      <button id="deselectBtn">Deselect All</button>
    </div>

    <table class="channel-table">
      <thead><tr>
        <th>Ch</th><th>Value</th><th>Min</th><th>Max</th><th>RMS</th><th>Status</th>
      </tr></thead>
      <tbody id="channelsTableBody"></tbody>
    </table>

    <div class="status-bar" id="globalStatus">Connecting...</div>
  </div>

  <!-- Oscilloscope Panel -->
  <div class="scope-panel">
    <h2>Oscilloscope <span id="titleChannels">—</span></h2>
    <div class="controls">
      <label><input type="checkbox" id="autoScale" checked> Auto Scale</label>
      <label><input type="checkbox" id="showGrid" checked> Grid</label>
    </div>
    <div id="scopeChart"></div>
    <div class="info-panel" id="scopeInfo">Waiting for data...</div>
  </div>
</div>

<script>
// === Configuration ===
const CONFIG = {
    channels: <?php echo $config['channels']; ?>,
    colors: <?php echo json_encode($config['colors']); ?>,
    maxDisplaySamples: <?php echo $config['max_display_samples']; ?>,
    websocketUrl: "<?php echo $config['websocket_url']; ?>"
};

// === Theme Toggle ===
document.getElementById('themeBtn').onclick = () => {
  const isDark = document.body.getAttribute('data-theme') === 'dark';
  document.body.setAttribute('data-theme', isDark ? '' : 'dark');
  document.getElementById('themeBtn').textContent = isDark ? 'Dark Mode' : 'Light Mode';
  // Update ECharts theme
  chart.dispose();
  chart = echarts.init(chartDom, isDark ? 'dark' : null);
  updatePlot();
};

// === State ===
let ws = new WebSocket(CONFIG.websocketUrl);
ws.binaryType = "arraybuffer";

let channelData = [];
let hasData = [];
let selectedChannels = new Set([0]);
let displaySamples = <?php echo $config['default_display_samples']; ?>;
let isPaused = false;
let detectedChannels = 0;
let samplesPerChannel = 2500;

// === ECharts Init ===
const chartDom = document.getElementById('scopeChart');
let chart = echarts.init(chartDom);

// === Channel Table ===
function initChannelTable(n) {
  detectedChannels = n;
  channelData = Array(n).fill().map(() => []);
  hasData = Array(n).fill(false);
  selectedChannels = new Set([0]);

  const tbody = document.getElementById('channelsTableBody');
  tbody.innerHTML = '';
  for (let i = 0; i < n; i++) {
    const row = document.createElement('tr');
    row.className = 'channel-row';
    row.onclick = (e) => {
      if (e.ctrlKey || e.metaKey) {
        if (selectedChannels.has(i)) selectedChannels.delete(i);
        else selectedChannels.add(i);
      } else {
        selectedChannels = new Set([i]);
      }
      updateSelection();
      updatePlot();
    };
    row.innerHTML = `
      <td><strong>CH${i}</strong></td>
      <td id="val${i}">--</td>
      <td id="min${i}">--</td>
      <td id="max${i}">--</td>
      <td id="rms${i}">--</td>
      <td><div class="status-dot dot-red" id="dot${i}"></div></td>
    `;
    tbody.appendChild(row);
  }
  updateSelection();
}

function updateSelection() {
  document.querySelectorAll('.channel-row').forEach((r, i) => {
    r.classList.toggle('selected', selectedChannels.has(i));
  });
  const list = Array.from(selectedChannels).sort((a,b)=>a-b);
  document.getElementById('titleChannels').textContent = 
    list.length === 0 ? '—' : 
    list.length === 1 ? `CH ${list[0]}` :
    `CH ${list.join(' + CH ')} (${list.length} channels)`;
}

// === Packet Parser ===
function parsePacket(buf) {
  const view = new DataView(buf);
  const headerOff = buf.byteLength - 34;
  const numCh = view.getInt16(headerOff, false);
  const blockBytes = view.getInt16(headerOff + 2, false);
  samplesPerChannel = blockBytes / 2;

  let off = 8;
  const samples = [];
  for (let ch = 0; ch < numCh; ch++) {
    const arr = [];
    for (let i = 0; i < samplesPerChannel; i++) {
      arr.push(view.getInt16(off + ch*blockBytes + i*2, true));
    }
    samples.push(arr);
  }
  return { numCh, samplesPerChannel, samples };
}

// === WebSocket ===
ws.onmessage = function(e) {
  if (isPaused) return;
  try {
    const { numCh, samplesPerChannel: sps, samples } = parsePacket(e.data);
    if (detectedChannels === 0) {
      initChannelTable(numCh);
      displaySamples = Math.min(displaySamples, sps);
      document.getElementById('displaySamples').value = sps;
    }

    for (let ch = 0; ch < numCh; ch++) {
      channelData[ch].push(...samples[ch]);
      if (channelData[ch].length > CONFIG.maxDisplaySamples) {
          channelData[ch] = channelData[ch].slice(-CONFIG.maxDisplaySamples);
      }
      hasData[ch] = true;
      document.getElementById(`dot${ch}`).className = "status-dot dot-green";
    }

    updateStats(samples);
    updatePlot();
  } catch(err) { console.error(err); }
};

function updateStats(latest) {
  for (let ch = 0; ch < detectedChannels; ch++) {
    const s = latest[ch];
    const val = s[s.length-1];
    const min = Math.min(...s);
    const max = Math.max(...s);
    const rms = Math.sqrt(s.reduce((a,v)=>a+v*v,0)/s.length).toFixed(0);
    document.getElementById(`val${ch}`).textContent = val;
    document.getElementById(`min${ch}`).textContent = min;
    document.getElementById(`max${ch}`).textContent = max;
    document.getElementById(`rms${ch}`).textContent = rms;
  }
}

function updatePlot() {
  if (selectedChannels.size === 0) {
    chart.setOption({
        title: { text: 'No channel selected' },
        series: []
    });
    return;
  }

  const series = [];
  const legendData = [];
  let i = 0;
  
  for (const ch of selectedChannels) {
    const data = channelData[ch];
    if (data.length === 0) continue;
    
    const yData = data.slice(-displaySamples);
    
    series.push({
        name: `CH ${ch}`,
        type: 'line',
        data: yData,
        symbol: 'none',
        line: {
            color: CONFIG.colors[i % CONFIG.colors.length],
            width: 2
        }
    });
    
    legendData.push(`CH ${ch}`);
    i++;
  }

  const xData = Array.from({length: Math.min(displaySamples, series[0]?.data?.length || 0)}, (_,k)=>k);
  
  chart.setOption({
      title: {
          text: `Overlay • ${series.length} channel${series.length>1?'s':''}`,
          textStyle: { color: 'var(--text)' }
      },
      legend: {
          data: legendData,
          textStyle: { color: 'var(--text)' }
      },
      tooltip: {
          trigger: 'axis'
      },
      grid: {
          left: '3%',
          right: '4%',
          bottom: '3%',
          containLabel: true
      },
      xAxis: {
          type: 'category',
          data: xData,
          name: 'Sample',
          nameTextStyle: { color: 'var(--text)' },
          axisLabel: { color: 'var(--text)' },
          splitLine: {
              show: document.getElementById('showGrid').checked,
              lineStyle: { color: 'var(--grid)' }
          }
      },
      yAxis: {
          type: 'value',
          name: 'ADC Value',
          nameTextStyle: { color: 'var(--text)' },
          axisLabel: { color: 'var(--text)' },
          splitLine: {
              show: document.getElementById('showGrid').checked,
              lineStyle: { color: 'var(--grid)' }
          },
          scale: !document.getElementById('autoScale').checked
      },
      series: series
  });
}

// === Controls ===
document.getElementById('displaySamples').onchange = e => { 
    displaySamples = +e.target.value; 
    updatePlot(); 
};

document.getElementById('autoScale').onchange = e => {
    chart.setOption({
        yAxis: {
            scale: !e.target.checked
        }
    });
};

document.getElementById('showGrid').onchange = e => {
    const showGrid = e.target.checked;
    chart.setOption({
        xAxis: {
            splitLine: { show: showGrid }
        },
        yAxis: {
            splitLine: { show: showGrid }
        }
    });
};

document.getElementById('clearBtn').onclick = () => {
  channelData = channelData.map(()=>[]);
  hasData.fill(false);
  document.querySelectorAll('[id^="dot"]').forEach(d=>d.className="status-dot dot-red");
  updatePlot();
};

document.getElementById('pauseBtn').onclick = function() {
  isPaused = !isPaused;
  this.textContent = isPaused ? "Resume" : "Pause";
};

document.getElementById('deselectBtn').onclick = () => {
  selectedChannels.clear();
  updateSelection();
  updatePlot();
};

// === Window Resize ===
window.addEventListener('resize', function() {
    chart.resize();
});

// === Connection ===
ws.onopen = () => {
  document.getElementById('globalStatus').textContent = "Connected — waiting for first frame...";
  document.getElementById('globalStatus').style.background = "#d4edda";
  document.getElementById('globalStatus').style.color = "#0b5e0b";
};
ws.onclose = ws.onerror = () => {
  document.getElementById('globalStatus').textContent = "Disconnected";
  document.getElementById('globalStatus').style.background = "#f8d7da";
  document.getElementById('globalStatus').style.color = "#721c24";
};

// Initialize with default channels
initChannelTable(CONFIG.channels);
</script>
</body>
</html>