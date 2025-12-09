<!DOCTYPE html>
<html>
<head>
  <title>PYNQ Real-Time DAQ</title>
  <script src="https://cdn.plot.ly/plotly-latest.min.js"></script>
  <style>
    body { font-family: Arial; margin: 20px; }
    .controls { margin: 15px 0; padding: 10px; background: #f5f5f5; border-radius: 5px; }
    .control-group { display: inline-block; margin-right: 20px; }
    label { margin-right: 5px; }
    select, input, button { padding: 5px; }
    .status { color: #666; font-size: 14px; margin-top: 10px; }
    .info { margin: 5px 0; font-size: 12px; color: #333; }
  </style>
</head>
<body>

<h2>FPGA Real-Time ADC Graph</h2>

<div class="controls">
  <div class="control-group">
    <label for="channelSelect">Channel:</label>
    <select id="channelSelect">
      <option value="0">Channel 0</option>
      <option value="1">Channel 1</option>
      <option value="2">Channel 2</option>
      <option value="3">Channel 3</option>
      <option value="4">Channel 4</option>
      <option value="5">Channel 5</option>
      <option value="6">Channel 6</option>
      <option value="7">Channel 7</option>
    </select>
  </div>
  
  <div class="control-group">
    <label for="displaySamples">Samples to Show:</label>
    <select id="displaySamples">
      <option value="2500">1 Buffer (2500)</option>
      <option value="2500">2 Buffers (2500)</option>
      <option value="5000">4 Buffers (5000)</option>
    </select>
  </div>
  
  <div class="control-group">
    <label for="autoScale">Auto Scale:</label>
    <input type="checkbox" id="autoScale" checked>
  </div>
  
  <div class="control-group">
    <button id="clearBtn">Clear & Restart</button>
  </div>
</div>

<div id="chart" style="width:100%;height:500px;"></div>
<div id="info" class="info"></div>
<div id="status" class="status"></div>

<script>
let ws = new WebSocket("ws://" + window.location.host + "/ws");
ws.binaryType = "arraybuffer";

// DAQ Data Structure Constants
const TEMPRH_BYTES = 8;
const SAMPLES_PER_CHANNEL = 2500;
const BYTES_PER_SAMPLE = 2;
const CHANNEL_DATA_SIZE = SAMPLES_PER_CHANNEL * BYTES_PER_SAMPLE;
const NUM_CHANNELS = 8;

let yData = [];
let selectedChannel = 0;
let displaySamples = 2500;
let autoScale = true;
let isFirstData = true;
let bufferCount = 0;

// Initialize plot with proper layout
let layout = {
    title: `Channel ${selectedChannel} - Real-time Waveform`,
    xaxis: { 
        title: 'Sample Number',
        range: [0, displaySamples],
        fixedrange: true
    },
    yaxis: { 
        title: 'Amplitude (ADC Counts)',
        fixedrange: false
    },
    margin: { t: 50, r: 30, b: 50, l: 60 },
    showlegend: false
};

let config = {
    responsive: true,
    displayModeBar: true,
    modeBarButtonsToRemove: ['pan2d', 'lasso2d', 'select2d'],
    displaylogo: false
};

Plotly.newPlot('chart', [{
    x: Array.from({length: displaySamples}, (_, i) => i),
    y: new Array(displaySamples).fill(0),
    mode: "lines",
    line: { width: 1, color: '#0066CC' },
    name: `ADC Data`
}], layout, config);

function updateInfo() {
    const min = Math.min(...yData);
    const max = Math.max(...yData);
    const amplitude = max - min;
    const avg = yData.reduce((a, b) => a + b, 0) / yData.length;
    
    // Simple frequency estimation (zero crossings)
    let zeroCrossings = 0;
    for (let i = 1; i < yData.length; i++) {
        if ((yData[i-1] >= 0 && yData[i] < 0) || (yData[i-1] < 0 && yData[i] >= 0)) {
            zeroCrossings++;
        }
    }
    const estimatedFreq = (zeroCrossings / 2) * (5000 / yData.length); // Assuming 5kHz sample rate
    
    document.getElementById('info').innerHTML = 
        `Amplitude: ${amplitude.toFixed(0)} | Min: ${min.toFixed(0)} | Max: ${max.toFixed(0)} | ` +
        `Avg: ${avg.toFixed(1)} | Zero Crossings: ${zeroCrossings} | Est. Freq: ${estimatedFreq.toFixed(1)} Hz`;
}

function updateStatus(message) {
    document.getElementById('status').textContent = message;
}

function parseDAQData(buffer, channel) {
    const dataView = new DataView(buffer);
    const channelOffset = TEMPRH_BYTES + (channel * CHANNEL_DATA_SIZE);
    const samples = [];
    
    for (let i = 0; i < SAMPLES_PER_CHANNEL; i++) {
        const byteOffset = channelOffset + (i * BYTES_PER_SAMPLE);
        const sample = dataView.getInt16(byteOffset, true);
        samples.push(sample);
    }
    
    return samples;
}

function updatePlot() {
    // Ensure we only show the desired number of samples
    const displayData = yData.slice(-displaySamples);
    const xData = Array.from({length: displayData.length}, (_, i) => i);
    
    Plotly.update('chart', {
        x: [xData],
        y: [displayData]
    }, {
        'xaxis.range': [0, displaySamples]
    });
    
    updateInfo();
}

ws.onopen = function() {
    console.log("WebSocket connected successfully");
    updateStatus("Connected - waiting for data...");
    isFirstData = true;
};

ws.onmessage = function(event) {
    if (event.data.byteLength !== 20144) {
        console.warn(`Unexpected data length: ${event.data.byteLength}, expected 20144`);
        return;
    }
    
    bufferCount++;
    
    try {
        const channelSamples = parseDAQData(event.data, selectedChannel);
        
        if (isFirstData) {
            // On first data, initialize with current buffer
            yData = [...channelSamples];
            isFirstData = false;
            updateStatus(`Receiving data - Buffer ${bufferCount} - Channel ${selectedChannel}`);
        } else {
            // Append new samples, removing old ones if needed
            yData = [...yData, ...channelSamples];
            
            // Keep only the maximum we might need (5000)
            if (yData.length > 5000) {
                yData = yData.slice(-5000);
            }
        }
        
        updatePlot();
        
        // Update status every 10 buffers to avoid spam
        if (bufferCount % 10 === 0) {
            updateStatus(`Receiving data - Buffer ${bufferCount} - Channel ${selectedChannel}`);
        }
        
    } catch (error) {
        console.error("Error parsing DAQ data:", error);
        updateStatus("Error parsing data");
    }
};

ws.onerror = function(error) {
    console.error("WebSocket error:", error);
    updateStatus("Connection error");
};

ws.onclose = function() {
    console.log("WebSocket closed");
    updateStatus("Disconnected");
};

// Event handlers
document.getElementById('channelSelect').addEventListener('change', function(e) {
    selectedChannel = parseInt(e.target.value);
    yData = [];
    isFirstData = true;
    bufferCount = 0;
    updateStatus(`Switched to Channel ${selectedChannel} - waiting for data...`);
});

document.getElementById('displaySamples').addEventListener('change', function(e) {
    displaySamples = parseInt(e.target.value);
    updatePlot();
});

document.getElementById('autoScale').addEventListener('change', function(e) {
    autoScale = e.target.checked;
    Plotly.update('chart', {}, {
        'yaxis.fixedrange': !autoScale
    });
});

document.getElementById('clearBtn').addEventListener('click', function() {
    yData = [];
    isFirstData = true;
    bufferCount = 0;
    updatePlot();
    updateStatus("Cleared - waiting for new data...");
});
</script>
</body>
</html>