<!doctype html>
<html lang="en" data-bs-theme="light">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Spicer Consulting - Admin Dashboard</title>

  <!-- Bootstrap 5.3 -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
  <!-- Font Awesome -->
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
  <!-- Google Fonts: Inter -->
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

  <style>
    :root {
      --primary-color: #0d6efd;
      --sidebar-width: 250px;
      --header-height: 60px;
      --text-muted: #6c757d;
      --bg-light: #f8f9fa;
      --border-color: #dee2e6;
    }

    [data-bs-theme="dark"] {
      --text-muted: #adb5bd;
      --bg-light: #212529;
      --border-color: #495057;
    }

    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    body {
      font-family: 'Inter', system-ui, -apple-system, sans-serif;
      overflow-x: hidden;
    }

    /* Header */
    .main-header {
      height: var(--header-height);
      background: #fff;
      border-bottom: 1px solid var(--border-color);
      position: fixed;
      top: 0;
      right: 0;
      left: var(--sidebar-width);
      z-index: 1000;
      display: flex;
      align-items: center;
      padding: 0 1.5rem;
      transition: left 0.3s ease;
    }

    [data-bs-theme="dark"] .main-header {
      background: #1a1d21;
    }

    .sidebar-toggle {
      background: none;
      border: none;
      font-size: 1.25rem;
      color: var(--text-muted);
      cursor: pointer;
      margin-right: 1rem;
    }

    /* Sidebar */
    .main-sidebar {
      position: fixed;
      top: 0;
      left: 0;
      bottom: 0;
      width: var(--sidebar-width);
      background: #fff;
      border-right: 1px solid var(--border-color);
      overflow-y: auto;
      transition: left 0.3s ease;
      z-index: 1001;
    }

    [data-bs-theme="dark"] .main-sidebar {
      background: #1a1d21;
    }

    .brand-link {
      display: flex;
      align-items: center;
      gap: 1rem;
      padding: 1rem 1.5rem;
      border-bottom: 1px solid var(--border-color);
      text-decoration: none;
      color: inherit;
    }

    .brand-logo {
      width: 40px;
      height: 40px;
 
      display: flex;
      align-items: center;
      justify-content: center;
      flex-shrink: 0;
    }

    .brand-logo img {
      max-width: 28px;
      max-height: 28px;
    }

    .brand-text {
      font-weight: 700;
      font-size: 1.125rem;
    }

    .sidebar-menu {
      list-style: none;
      padding: 1rem 0;
    }

    .nav-item {
      margin: 0.25rem 0.75rem;
    }

    .nav-link {
      display: flex;
      align-items: center;
      gap: 0.75rem;
      padding: 0.75rem 1rem;
      color: #495057;
      text-decoration: none;
      border-radius: 0.5rem;
      transition: all 0.2s ease;
      font-weight: 500;
      font-size: 0.9375rem;
    }

    [data-bs-theme="dark"] .nav-link {
      color: #adb5bd;
    }

    .nav-link:hover {
      background: rgba(13, 110, 253, 0.08);
      color: var(--primary-color);
    }

    .nav-link.active {
      background: var(--primary-color);
      color: #fff;
    }

    .nav-link i {
      width: 20px;
      text-align: center;
      font-size: 1rem;
    }

    .nav-link .right {
      margin-left: auto;
      transition: transform 0.3s ease;
    }

    .nav-item.menu-open > .nav-link .right {
      transform: rotate(90deg);
    }

    .nav-header {
      padding: 1rem 1.75rem 0.5rem;
      font-size: 0.75rem;
      font-weight: 700;
      text-transform: uppercase;
      color: var(--text-muted);
      letter-spacing: 0.5px;
    }

    .nav-treeview {
      list-style: none;
      padding: 0;
      display: none;
      padding-left: 1rem;
    }

    .nav-item.menu-open > .nav-treeview {
      display: block;
    }

    .nav-treeview .nav-link {
      padding: 0.6rem 1rem;
      padding-left: 2.5rem;
      font-size: 0.875rem;
    }

    .nav-treeview .nav-link i {
      font-size: 0.75rem;
    }

    /* Content */
    .content-wrapper {
      margin-left: var(--sidebar-width);
      margin-top: var(--header-height);
      padding: 1.5rem;
      min-height: calc(100vh - var(--header-height));
      background: var(--bg-light);
      transition: margin-left 0.3s ease;
    }

    .content-header {
      margin-bottom: 1.5rem;
    }

    .content-header h1 {
      font-size: 1.75rem;
      font-weight: 700;
      margin-bottom: 0.5rem;
    }

    .breadcrumb {
      background: none;
      padding: 0;
      margin: 0;
      font-size: 0.875rem;
    }

    /* Info Boxes */
    .info-box {
      display: flex;
      align-items: center;
      background: #fff;
      border-radius: 0.5rem;
      padding: 1.25rem;
      box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
      height: 100%;
    }

    [data-bs-theme="dark"] .info-box {
      background: #212529;
    }

    .info-box-icon {
      width: 60px;
      height: 60px;
      display: flex;
      align-items: center;
      justify-content: center;
      border-radius: 0.5rem;
      font-size: 1.5rem;
      margin-right: 1rem;
    }

    .info-box-content {
      flex: 1;
    }

    .info-box-text {
      font-size: 0.875rem;
      color: var(--text-muted);
      margin-bottom: 0.25rem;
      font-weight: 500;
    }

    .info-box-number {
      font-size: 1.5rem;
      font-weight: 700;
      margin-bottom: 0.25rem;
    }

    .info-box-footer {
      font-size: 0.8125rem;
      color: var(--text-muted);
    }

    /* Cards */
    .card {
      border: none;
      border-radius: 0.5rem;
      box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
      margin-bottom: 1.5rem;
    }

    [data-bs-theme="dark"] .card {
      background: #212529;
    }

    .card-header {
      background: transparent;
      border-bottom: 1px solid var(--border-color);
      padding: 1rem 1.25rem;
      font-weight: 600;
    }

    .card-body {
      padding: 1.25rem;
    }

    .card-title {
      font-size: 1.125rem;
      font-weight: 600;
      margin-bottom: 0;
    }

    /* Tables */
    .table {
      font-size: 0.9375rem;
    }

    .table th {
      font-weight: 600;
      color: var(--text-muted);
      text-transform: uppercase;
      font-size: 0.8125rem;
      letter-spacing: 0.3px;
      border-bottom: 2px solid var(--border-color);
    }

    /* Progress bars */
    .progress {
      height: 0.5rem;
      border-radius: 1rem;
    }

    /* Badges */
    .badge {
      padding: 0.35rem 0.65rem;
      font-weight: 600;
      font-size: 0.75rem;
    }

    /* Responsive */
    body.sidebar-collapse .main-sidebar {
      left: calc(var(--sidebar-width) * -1);
    }

    body.sidebar-collapse .main-header,
    body.sidebar-collapse .content-wrapper {
      margin-left: 0;
      left: 0;
    }

    @media (max-width: 991px) {
      .main-sidebar {
        left: calc(var(--sidebar-width) * -1);
      }

      .main-header,
      .content-wrapper {
        margin-left: 0;
        left: 0;
      }

      body.sidebar-open .main-sidebar {
        left: 0;
      }
    }

    /* User Panel */
    .user-panel {
      display: flex;
      align-items: center;
      gap: 0.75rem;
      padding: 1rem 1.5rem;
      border-bottom: 1px solid var(--border-color);
    }

    .user-panel-image {
      width: 40px;
      height: 40px;
      border-radius: 50%;
      background: linear-gradient(135deg, #0d6efd 0%, #0a58ca 100%);
      display: flex;
      align-items: center;
      justify-content: center;
      color: white;
      font-weight: 600;
    }

    .user-panel-info {
      flex: 1;
    }

    .user-panel-info .name {
      font-weight: 600;
      font-size: 0.9375rem;
    }

    .user-panel-info .status {
      font-size: 0.8125rem;
      color: var(--text-muted);
    }

    .status-dot {
      width: 8px;
      height: 8px;
      border-radius: 50%;
      display: inline-block;
      margin-right: 0.25rem;
    }

    .status-dot.online {
      background: #28a745;
    }
  </style>
</head>
<body>
  <!-- Sidebar -->
  <aside class="main-sidebar">
    <a href="#" class="brand-link">
      <div class="brand-logo">
        <img src="./assets/img/logo.png" alt="Logo">
      </div>
      <span class="brand-text">Spicer</span>
    </a>

 

    <nav class="mt-2">
      <ul class="sidebar-menu">
        <li class="nav-item">
          <a href="#" class="nav-link active">
            <i class="fas fa-tachometer-alt"></i>
            <span>Dashboard</span>
          </a>
        </li>
        <li class="nav-item">
          <a href="#" class="nav-link">
            <i class="fas fa-microchip"></i>
            <span>Device Status</span>
          </a>
        </li>
        <li class="nav-item">
          <a href="#" class="nav-link">
            <i class="fas fa-chart-line"></i>
            <span>ADC Channels</span>
          </a>
        </li>
        <li class="nav-item">
          <a href="#" class="nav-link">
            <i class="fas fa-plug"></i>
            <span>Modules</span>
          </a>
        </li>

        <li class="nav-header">System</li>
        <li class="nav-item has-treeview">
          <a href="#" class="nav-link">
            <i class="fas fa-cog"></i>
            <span>Settings</span>
            <i class="fas fa-angle-right right"></i>
          </a>
          <ul class="nav-treeview">
            <li class="nav-item">
              <a href="#" class="nav-link">
                <i class="fas fa-circle"></i>
                <span>General</span>
              </a>
            </li>
            <li class="nav-item">
              <a href="#" class="nav-link active">
                <i class="fas fa-circle"></i>
                <span>Device</span>
              </a>
            </li>
            <li class="nav-item">
              <a href="#" class="nav-link">
                <i class="fas fa-circle"></i>
                <span>Security</span>
              </a>
            </li>
            <li class="nav-item">
              <a href="#" class="nav-link">
                <i class="fas fa-circle"></i>
                <span>Notifications</span>
              </a>
            </li>
          </ul>
        </li>
        <li class="nav-item">
          <a href="#" class="nav-link">
            <i class="fas fa-users"></i>
            <span>User Management</span>
          </a>
        </li>
        <li class="nav-item">
          <a href="#" class="nav-link">
            <i class="fas fa-network-wired"></i>
            <span>Network</span>
          </a>
        </li>
        <li class="nav-item">
          <a href="#" class="nav-link">
            <i class="fas fa-file-alt"></i>
            <span>Logs</span>
          </a>
        </li>

        <li class="nav-header">Support</li>
        <li class="nav-item">
          <a href="#" class="nav-link">
            <i class="fas fa-book"></i>
            <span>Documentation</span>
          </a>
        </li>
        <li class="nav-item">
          <a href="#" class="nav-link">
            <i class="fas fa-life-ring"></i>
            <span>Support</span>
          </a>
        </li>
      </ul>
    </nav>
  </aside>

  <!-- Header -->
  <nav class="main-header">
    <button class="sidebar-toggle" id="sidebarToggle">
      <i class="fas fa-bars"></i>
    </button>

    <div class="ms-auto d-flex align-items-center gap-3">
      <div class="form-check form-switch mb-0">
        <input class="form-check-input" type="checkbox" id="themeToggle">
        <label class="form-check-label" for="themeToggle">
          <i class="fas fa-moon"></i>
        </label>
      </div>

      <div class="dropdown">
        <button class="btn btn-link text-decoration-none dropdown-toggle" type="button" data-bs-toggle="dropdown">
          <i class="fas fa-bell"></i>
          <span class="badge bg-danger position-absolute top-0 start-100 translate-middle rounded-pill">3</span>
        </button>
        <ul class="dropdown-menu dropdown-menu-end">
          <li><h6 class="dropdown-header">Notifications</h6></li>
          <li><a class="dropdown-item" href="#"><i class="fas fa-exclamation-circle text-warning me-2"></i>High temperature alert</a></li>
          <li><a class="dropdown-item" href="#"><i class="fas fa-check-circle text-success me-2"></i>Module connected</a></li>
          <li><a class="dropdown-item" href="#"><i class="fas fa-info-circle text-info me-2"></i>Firmware update available</a></li>
        </ul>
      </div>

      <div class="dropdown">
        <button class="btn btn-link text-decoration-none dropdown-toggle" type="button" data-bs-toggle="dropdown">
          <i class="fas fa-user-circle fa-lg"></i>
        </button>
        <ul class="dropdown-menu dropdown-menu-end">
          <li><h6 class="dropdown-header">Admin User</h6></li>
          <li><a class="dropdown-item" href="#"><i class="fas fa-user me-2"></i>Profile</a></li>
          <li><a class="dropdown-item" href="#"><i class="fas fa-cog me-2"></i>Settings</a></li>
          <li><hr class="dropdown-divider"></li>
          <li><a class="dropdown-item" href="#"><i class="fas fa-sign-out-alt me-2"></i>Logout</a></li>
        </ul>
      </div>
    </div>
  </nav>

  <!-- Content -->
  <div class="content-wrapper">
    <div class="content-header">
      <div class="container-fluid">
        <div class="row mb-2">
          <div class="col-sm-6">
            <h1>Dashboard</h1>
          </div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-end">
              <li class="breadcrumb-item"><a href="#">Home</a></li>
              <li class="breadcrumb-item active">Dashboard</li>
            </ol>
          </div>
        </div>
      </div>
    </div>

    <div class="container-fluid">
      <!-- Info boxes -->
      <div class="row">
        <div class="col-lg-3 col-md-6 mb-3">
          <div class="info-box">
            <div class="info-box-icon bg-primary bg-opacity-10 text-primary">
              <i class="fas fa-microchip"></i>
            </div>
            <div class="info-box-content">
              <div class="info-box-text">Device Status</div>
              <div class="info-box-number">Online</div>
              <div class="info-box-footer">
                <i class="fas fa-clock me-1"></i>Uptime: 24d 3h
              </div>
            </div>
          </div>
        </div>

        <div class="col-lg-3 col-md-6 mb-3">
          <div class="info-box">
            <div class="info-box-icon bg-success bg-opacity-10 text-success">
              <i class="fas fa-chart-line"></i>
            </div>
            <div class="info-box-content">
              <div class="info-box-text">Active Channels</div>
              <div class="info-box-number">12/16</div>
              <div class="info-box-footer">
                <i class="fas fa-arrow-up me-1"></i>75% utilization
              </div>
            </div>
          </div>
        </div>

        <div class="col-lg-3 col-md-6 mb-3">
          <div class="info-box">
            <div class="info-box-icon bg-warning bg-opacity-10 text-warning">
              <i class="fas fa-thermometer-half"></i>
            </div>
            <div class="info-box-content">
              <div class="info-box-text">Temperature</div>
              <div class="info-box-number">42°C</div>
              <div class="info-box-footer">
                <i class="fas fa-check-circle me-1"></i>Normal range
              </div>
            </div>
          </div>
        </div>

        <div class="col-lg-3 col-md-6 mb-3">
          <div class="info-box">
            <div class="info-box-icon bg-info bg-opacity-10 text-info">
              <i class="fas fa-plug"></i>
            </div>
            <div class="info-box-content">
              <div class="info-box-text">Modules</div>
              <div class="info-box-number">8</div>
              <div class="info-box-footer">
                <i class="fas fa-link me-1"></i>All connected
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Charts and tables -->
      <div class="row">
        <div class="col-lg-8">
          <div class="card">
            <div class="card-header">
              <h3 class="card-title">
                <i class="fas fa-chart-bar me-2"></i>
                ADC Channel Activity
              </h3>
            </div>
            <div class="card-body">
              <canvas id="channelChart" style="height: 300px;"></canvas>
            </div>
          </div>

          <div class="card">
            <div class="card-header">
              <h3 class="card-title">
                <i class="fas fa-list me-2"></i>
                Recent Activity
              </h3>
            </div>
            <div class="card-body p-0">
              <table class="table table-hover mb-0">
                <thead>
                  <tr>
                    <th>Time</th>
                    <th>Event</th>
                    <th>Channel</th>
                    <th>Status</th>
                  </tr>
                </thead>
                <tbody>
                  <tr>
                    <td>14:32:45</td>
                    <td>Data acquisition started</td>
                    <td>CH-03</td>
                    <td><span class="badge bg-success">Success</span></td>
                  </tr>
                  <tr>
                    <td>14:30:12</td>
                    <td>Module connected</td>
                    <td>MOD-05</td>
                    <td><span class="badge bg-info">Info</span></td>
                  </tr>
                  <tr>
                    <td>14:28:03</td>
                    <td>Threshold exceeded</td>
                    <td>CH-08</td>
                    <td><span class="badge bg-warning">Warning</span></td>
                  </tr>
                  <tr>
                    <td>14:25:19</td>
                    <td>Calibration completed</td>
                    <td>CH-12</td>
                    <td><span class="badge bg-success">Success</span></td>
                  </tr>
                  <tr>
                    <td>14:22:54</td>
                    <td>System health check</td>
                    <td>ALL</td>
                    <td><span class="badge bg-success">Passed</span></td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>

        <div class="col-lg-4">
          <div class="card">
            <div class="card-header">
              <h3 class="card-title">
                <i class="fas fa-info-circle me-2"></i>
                System Information
              </h3>
            </div>
            <div class="card-body">
              <dl class="row mb-0">
                <dt class="col-sm-6">Serial Number:</dt>
                <dd class="col-sm-6">SC-24123</dd>

                <dt class="col-sm-6">Firmware:</dt>
                <dd class="col-sm-6">v1.2.0</dd>

                <dt class="col-sm-6">IP Address:</dt>
                <dd class="col-sm-6">192.168.0.1</dd>

                <dt class="col-sm-6">MAC Address:</dt>
                <dd class="col-sm-6">00:1B:44:11:3A:B7</dd>

                <dt class="col-sm-6">Last Boot:</dt>
                <dd class="col-sm-6">Nov 11, 2025</dd>

                <dt class="col-sm-6">Memory Usage:</dt>
                <dd class="col-sm-6">
                  <div class="progress">
                    <div class="progress-bar bg-primary" style="width: 62%"></div>
                  </div>
                  <small class="text-muted">62% (248MB / 400MB)</small>
                </dd>

                <dt class="col-sm-6 mt-2">CPU Usage:</dt>
                <dd class="col-sm-6 mt-2">
                  <div class="progress">
                    <div class="progress-bar bg-success" style="width: 38%"></div>
                  </div>
                  <small class="text-muted">38%</small>
                </dd>

                <dt class="col-sm-6 mt-2">Storage:</dt>
                <dd class="col-sm-6 mt-2">
                  <div class="progress">
                    <div class="progress-bar bg-warning" style="width: 71%"></div>
                  </div>
                  <small class="text-muted">71% (14.2GB / 20GB)</small>
                </dd>
              </dl>
            </div>
          </div>

          <div class="card">
            <div class="card-header">
              <h3 class="card-title">
                <i class="fas fa-tasks me-2"></i>
                Quick Actions
              </h3>
            </div>
            <div class="card-body">
              <div class="d-grid gap-2">
                <button class="btn btn-primary">
                  <i class="fas fa-sync-alt me-2"></i>Refresh Data
                </button>
                <button class="btn btn-outline-secondary">
                  <i class="fas fa-download me-2"></i>Export Logs
                </button>
                <button class="btn btn-outline-secondary">
                  <i class="fas fa-power-off me-2"></i>Restart Device
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Bootstrap JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
  <!-- Chart.js -->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.0/chart.umd.min.js"></script>

  <script>
    // Sidebar toggle
    const sidebarToggle = document.getElementById('sidebarToggle');
    sidebarToggle.addEventListener('click', () => {
      if (window.innerWidth > 991) {
        document.body.classList.toggle('sidebar-collapse');
      } else {
        document.body.classList.toggle('sidebar-open');
      }
    });

    // Theme toggle
    const themeToggle = document.getElementById('themeToggle');
    themeToggle.addEventListener('change', (e) => {
      document.documentElement.setAttribute('data-bs-theme', e.target.checked ? 'dark' : 'light');
    });

    // Submenu handling
    document.querySelectorAll('.has-treeview > .nav-link').forEach(link => {
      link.addEventListener('click', (e) => {
        e.preventDefault();
        const parent = link.parentElement;
        const isOpen = parent.classList.contains('menu-open');
        
        // Close all other submenus
        document.querySelectorAll('.nav-item.menu-open').forEach(item => {
          if (item !== parent) {
            item.classList.remove('menu-open');
          }
        });
        
        // Toggle current submenu
        parent.classList.toggle('menu-open');
      });
    });

    // Auto-open Settings submenu on page load (since Device is active)
    document.addEventListener('DOMContentLoaded', () => {
      const activeSubmenuItem = document.querySelector('.nav-treeview .nav-link.active');
      if (activeSubmenuItem) {
        const parentTreeview = activeSubmenuItem.closest('.has-treeview');
        if (parentTreeview) {
          parentTreeview.classList.add('menu-open');
        }
      }
    });

    // Chart
    const ctx = document.getElementById('channelChart').getContext('2d');
    new Chart(ctx, {
      type: 'line',
      data: {
        labels: ['00:00', '04:00', '08:00', '12:00', '16:00', '20:00', '24:00'],
        datasets: [{
          label: 'Channel Activity',
          data: [45, 52, 48, 65, 72, 68, 58],
          borderColor: '#0d6efd',
          backgroundColor: 'rgba(13, 110, 253, 0.1)',
          tension: 0.4,
          fill: true
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: {
            display: false
          }
        },
        scales: {
          y: {
            beginAtZero: true,
            max: 100
          }
        }
      }
    });
  </script>
</body>
</html>