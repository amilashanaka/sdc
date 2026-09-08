<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Router Web Management - System Mode</title>
    <!-- Bootstrap CSS v5.0.0 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <style>
        /* Embedded System Industrial Palette */
        body {
            background-color: #0c1a12; /* Deep terminal/router housing green */
            background-image: radial-gradient(#162e20 1px, transparent 1px);
            background-size: 24px 24px; /* Subtle hardware grid texture */
            min-height: 100vh;
            color: #e0e6e3;
        }

        /* Professional Network Appliance Button Styling */
        .btn-router-action {
            background-color: #1a1d20; /* Sleek matte dark grey */
            color: #39ff14; /* Crisp "Neon Green" telemetry text */
            font-family: 'SFMono-Regular', Consolas, 'Liberation Mono', Menlo, monospace;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.85rem;
            letter-spacing: 1.5px;
            padding: 14px 28px;
            border: 1px solid #2f563d; /* Grounded tech border */
            border-radius: 4px; /* Standardized industrial rounding */
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.5), inset 0 1px 0 rgba(255, 255, 255, 0.05);
            transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
        }

        /* Hover: Sleek lighting shift and text intensity boost */
        .btn-router-action:hover {
            color: #ffffff;
            background-color: #212529;
            border-color: #39ff14;
            box-shadow: 0 0 15px rgba(57, 255, 20, 0.3), 0 4px 20px rgba(0, 0, 0, 0.6);
        }

        /* Active click state simulating hardware actuation */
        .btn-router-action:active {
            transform: scale(0.98);
            background-color: #111416;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.8);
        }

        /* LED Pulse Animation representing an active system state */
        .led-pulse {
            display: inline-block;
            width: 8px;
            height: 8px;
            background-color: #39ff14;
            border-radius: 50%;
            margin-right: 10px;
            vertical-align: middle;
            animation: pulse-glow 2s infinite ease-in-out;
        }

        @keyframes pulse-glow {
            0% { transform: scale(0.9); opacity: 0.6; box-shadow: 0 0 0 0 rgba(57, 255, 20, 0.7); }
            50% { transform: scale(1.1); opacity: 1; box-shadow: 0 0 8px 3px rgba(57, 255, 20, 0.4); }
            100% { transform: scale(0.9); opacity: 0.6; box-shadow: 0 0 0 0 rgba(57, 255, 20, 0); }
        }
    </style>
</head>
<body class="d-flex align-items-center justify-content-center">

    <div class="container text-center">
        <!-- Subdued system status info often found in router portals -->
        <p class="text-uppercase text-muted small mb-4 font-monospace" style="letter-spacing: 2px;">
            System Status: Nominal // Environment: Production
        </p>
        
        <!-- Hardware Style Action Button -->
        <button type="button" class="btn btn-router-action">
            <span class="led-pulse"></span>Switch to Debug
        </button>
    </div>

    <!-- Bootstrap Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>