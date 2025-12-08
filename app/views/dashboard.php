<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Spicer Digital Core Dashboard</title>
  
  <!-- Bootstrap 5 CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
  <!-- SweetAlert2 -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11.10.0/dist/sweetalert2.min.css">
  
  <style>
    :root {
      --sidebar-width: 250px;
      --sidebar-collapsed-width: 60px;
      --primary-color: #3498db;
      --secondary-color: #2c3e50;
      --success-color: #28a745;
      --danger-color: #dc3545;
      --warning-color: #ffc107;
      --info-color: #17a2b8;
      --bg-color: #f4f6f9;
      --card-bg: #ffffff;
      --text-color: #333333;
      --border-color: #e9ecef;
      --sidebar-bg: #2c3e50;
      --sidebar-text: rgba(255, 255, 255, 0.8);
      --navbar-bg: #ffffff;
      --navbar-text: #2c3e50;
    }

    [data-theme="dark"] {
      --bg-color: #1a1a1a;
      --card-bg: #2d2d2d;
      --text-color: #e0e0e0;
      --border-color: #404040;
      --sidebar-bg: #1a1a1a;
      --sidebar-text: rgba(255, 255, 255, 0.7);
      --navbar-bg: #2d2d2d;
      --navbar-text: #e0e0e0;
      --secondary-color: #e0e0e0;
    }

    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    body {
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      background-color: var(--bg-color);
      color: var(--text-color);
      overflow-x: hidden;
      transition: background-color 0.3s ease, color 0.3s ease;
    }

    /* Sidebar Styles */
    .sidebar {
      position: fixed;
      top: 0;
      left: 0;
      height: 100vh;
      width: var(--sidebar-width);
      background: var(--sidebar-bg);
      transition: all 0.3s ease;
      z-index: 1000;
      overflow-y: auto;
      box-shadow: 2px 0 5px rgba(0,0,0,0.1);
    }

    .sidebar.collapsed {
      width: var(--sidebar-collapsed-width);
    }

    .sidebar-brand {
      padding: 20px;
      text-align: center;
      color: white;
      font-size: 20px;
      font-weight: bold;
      border-bottom: 1px solid rgba(255, 255, 255, 0.1);
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 10px;
    }

    .sidebar.collapsed .sidebar-brand .brand-text {
      display: none;
    }

    /* User Panel */
    .user-panel {
      padding: 15px;
      display: flex;
      align-items: center;
      border-bottom: 1px solid rgba(255, 255, 255, 0.1);
      margin-bottom: 10px;
    }

    .user-panel img {
      width: 45px;
      height: 45px;
      border-radius: 50%;
      margin-right: 12px;
      border: 2px solid rgba(255, 255, 255, 0.3);
    }

    .user-panel .info {
      color: white;
      flex: 1;
    }

    .user-panel .info a {
      color: white;
      text-decoration: none;
      font-size: 14px;
      display: block;
    }

    .user-panel .info small {
      color: rgba(255, 255, 255, 0.6);
      font-size: 12px;
    }

    .sidebar.collapsed .user-panel .info {
      display: none;
    }

    .sidebar.collapsed .user-panel {
      justify-content: center;
    }

    .sidebar.collapsed .user-panel img {
      margin-right: 0;
    }

    /* Sidebar Menu */
    .sidebar-menu {
      list-style: none;
      padding: 10px 0;
    }

    .sidebar-menu > li {
      position: relative;
    }

    .sidebar-menu .nav-header {
      padding: 10px 20px;
      color: rgba(255, 255, 255, 0.5);
      font-size: 11px;
      text-transform: uppercase;
      letter-spacing: 1px;
      font-weight: 600;
    }

    .sidebar.collapsed .nav-header {
      display: none;
    }

    .sidebar-menu a {
      display: flex;
      align-items: center;
      padding: 12px 20px;
      color: var(--sidebar-text);
      text-decoration: none;
      transition: all 0.3s;
      cursor: pointer;
    }

    .sidebar-menu a:hover,
    .sidebar-menu a.active {
      background: rgba(255, 255, 255, 0.1);
      color: white;
      border-left: 3px solid var(--primary-color);
      padding-left: 17px;
    }

    .sidebar-menu i {
      width: 25px;
      font-size: 16px;
      margin-right: 10px;
    }

    .sidebar-menu .nav-icon {
      min-width: 25px;
    }

    .sidebar.collapsed .sidebar-menu span:not(.badge) {
      display: none;
    }

    .sidebar.collapsed .sidebar-menu i {
      margin-right: 0;
    }

    .sidebar.collapsed .menu-toggle .right {
      display: none;
    }

    /* Submenu Styles */
    .nav-treeview {
      list-style: none;
      padding: 0;
      display: none;
      background: rgba(0, 0, 0, 0.2);
    }

    .nav-treeview.show {
      display: block;
    }

    .nav-treeview li a {
      padding-left: 55px;
      font-size: 14px;
    }

    .sidebar.collapsed .nav-treeview {
      position: absolute;
      left: 100%;
      top: 0;
      width: 200px;
      background: var(--sidebar-bg);
      border-radius: 0 8px 8px 0;
      box-shadow: 2px 0 8px rgba(0,0,0,0.3);
    }

    .sidebar.collapsed .nav-treeview li a {
      padding-left: 20px;
    }

    .menu-toggle {
      position: relative;
    }

    .menu-toggle .right {
      margin-left: auto;
      transition: transform 0.3s;
    }

    .menu-toggle.menu-open .right {
      transform: rotate(-90deg);
    }

    .badge {
      margin-left: auto;
      font-size: 10px;
      padding: 3px 6px;
    }

    /* Navbar Styles */
    .main-navbar {
      position: fixed;
      top: 0;
      left: var(--sidebar-width);
      right: 0;
      height: 60px;
      background: var(--navbar-bg);
      box-shadow: 0 2px 4px rgba(0,0,0,0.1);
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding: 0 20px;
      transition: all 0.3s ease;
      z-index: 999;
    }

    .sidebar.collapsed ~ .main-navbar {
      left: var(--sidebar-collapsed-width);
    }

    .navbar-toggle {
      background: none;
      border: none;
      font-size: 20px;
      cursor: pointer;
      color: var(--navbar-text);
    }

    .navbar-menu {
      display: flex;
      align-items: center;
      gap: 20px;
    }

    .navbar-item {
      position: relative;
      cursor: pointer;
      color: var(--navbar-text);
      font-size: 18px;
      background: none;
      border: none;
      transition: color 0.3s;
    }

    .navbar-item:hover {
      color: var(--primary-color);
    }

    .badge-notify {
      position: absolute;
      top: -5px;
      right: -5px;
      background: var(--danger-color);
      color: white;
      border-radius: 50%;
      padding: 2px 6px;
      font-size: 10px;
      font-weight: bold;
    }

    .navbar-profile {
      display: flex;
      align-items: center;
      gap: 10px;
      cursor: pointer;
    }

    .navbar-profile img {
      width: 35px;
      height: 35px;
      border-radius: 50%;
      border: 2px solid var(--border-color);
    }

    /* Theme Switch */
    .theme-switch {
      position: relative;
      display: inline-block;
      width: 50px;
      height: 24px;
    }

    .theme-switch input {
      opacity: 0;
      width: 0;
      height: 0;
    }

    .theme-slider {
      position: absolute;
      cursor: pointer;
      top: 0;
      left: 0;
      right: 0;
      bottom: 0;
      background-color: #ccc;
      transition: 0.4s;
      border-radius: 24px;
    }

    .theme-slider:before {
      position: absolute;
      content: "";
      height: 18px;
      width: 18px;
      left: 3px;
      bottom: 3px;
      background-color: white;
      transition: 0.4s;
      border-radius: 50%;
    }

    input:checked + .theme-slider {
      background-color: var(--primary-color);
    }

    input:checked + .theme-slider:before {
      transform: translateX(26px);
    }

    .theme-icon {
      position: absolute;
      top: 50%;
      transform: translateY(-50%);
      font-size: 12px;
      color: white;
    }

    .theme-icon.sun {
      left: 5px;
    }

    .theme-icon.moon {
      right: 5px;
    }

    /* Content Wrapper */
    .content-wrapper {
      margin-left: var(--sidebar-width);
      margin-top: 60px;
      padding: 20px;
      min-height: calc(100vh - 60px);
      transition: margin-left 0.3s ease;
    }

    .sidebar.collapsed ~ .main-navbar ~ .content-wrapper {
      margin-left: var(--sidebar-collapsed-width);
    }

    /* Dashboard Cards */
    .info-box {
      background: var(--card-bg);
      border-radius: 8px;
      padding: 20px;
      box-shadow: 0 2px 4px rgba(0,0,0,0.1);
      margin-bottom: 20px;
      display: flex;
      align-items: center;
      transition: transform 0.3s, box-shadow 0.3s;
    }

    .info-box:hover {
      transform: translateY(-5px);
      box-shadow: 0 4px 8px rgba(0,0,0,0.15);
    }

    .info-box-icon {
      width: 70px;
      height: 70px;
      border-radius: 8px;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 30px;
      color: white;
      margin-right: 15px;
    }

    .info-box-content h3 {
      margin: 0;
      font-size: 28px;
      font-weight: bold;
      color: var(--text-color);
    }

    .info-box-content p {
      margin: 0;
      color: #777;
      font-size: 14px;
    }

    .bg-primary { background: var(--primary-color); }
    .bg-success { background: var(--success-color); }
    .bg-warning { background: var(--warning-color); }
    .bg-danger { background: var(--danger-color); }
    .bg-info { background: var(--info-color); }

    /* Card */
    .card {
      background: var(--card-bg);
      border-radius: 8px;
      box-shadow: 0 2px 4px rgba(0,0,0,0.1);
      margin-bottom: 20px;
      border: 1px solid var(--border-color);
    }

    .card-header {
      padding: 15px 20px;
      border-bottom: 1px solid var(--border-color);
      font-weight: bold;
      color: var(--text-color);
      background: transparent;
    }

    .card-body {
      padding: 20px;
    }

    /* Table */
    .table {
      color: var(--text-color);
    }

    .table-responsive {
      overflow-x: auto;
    }

    /* Mobile Overlay */
    .sidebar-overlay {
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: rgba(0, 0, 0, 0.5);
      z-index: 999;
      display: none;
    }

    .sidebar-overlay.show {
      display: block;
    }

    /* Responsive */
    @media (max-width: 768px) {
      .sidebar {
        left: calc(var(--sidebar-width) * -1);
        z-index: 1001;
      }

      .sidebar.show {
        left: 0;
      }
      
      .main-navbar {
        left: 0;
      }
      
      .content-wrapper {
        margin-left: 0;
      }

      .navbar-profile span {
        display: none;
      }
    }

    /* Scrollbar */
    .sidebar::-webkit-scrollbar {
      width: 6px;
    }

    .sidebar::-webkit-scrollbar-track {
      background: rgba(0,0,0,0.1);
    }

    .sidebar::-webkit-scrollbar-thumb {
      background: rgba(255,255,255,0.3);
      border-radius: 3px;
    }

    /* Dark mode scrollbar */
    [data-theme="dark"] .sidebar::-webkit-scrollbar-thumb {
      background: rgba(255,255,255,0.2);
    }

    /* Loader Styles */
    .page-loader {
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: rgba(255, 255, 255, 0.95);
      display: flex;
      align-items: center;
      justify-content: center;
      z-index: 9999;
      transition: opacity 0.3s ease, visibility 0.3s ease;
    }

    [data-theme="dark"] .page-loader {
      background: rgba(26, 26, 26, 0.95);
    }

    .page-loader.hidden {
      opacity: 0;
      visibility: hidden;
    }

    .loader {
      width: 60px;
      height: 60px;
      border: 5px solid #e0e0e0;
      border-top: 5px solid var(--primary-color);
      border-radius: 50%;
      animation: spin 1s linear infinite;
    }

    [data-theme="dark"] .loader {
      border-color: #404040;
      border-top-color: var(--primary-color);
    }

    @keyframes spin {
      0% { transform: rotate(0deg); }
      100% { transform: rotate(360deg); }
    }

    .loader-text {
      position: absolute;
      margin-top: 100px;
      color: var(--text-color);
      font-size: 14px;
      font-weight: 500;
    }

        .brand-logo {
      width: 50px; 
 
 
 
    }

    .brand-logo img {
      max-width: 30px;
      max-height: 30px;
    }

    
  </style>
</head>
<body>

  <!-- Page Loader -->
  <div class="page-loader" id="pageLoader">
    <div>
      <div class="loader"></div>
      <div class="loader-text">Loading...</div>
    </div>
  </div>

  <!-- Sidebar Overlay -->
  <div class="sidebar-overlay" id="sidebarOverlay" onclick="closeSidebar()"></div>

  <!-- Sidebar -->
  <aside class="sidebar" id="sidebar">
    <div class="sidebar-brand">
       <img src="./assets/img/logo.png" class="brand-logo" alt="Spicer Consulting Logo">
      <span class="brand-text">SDC</span>
    </div>
    
    <!-- User Panel -->
 
    
    <ul class="sidebar-menu">
      <li class="nav-header">MAIN NAVIGATION</li>
      <li>
        <a href="#" class="active" onclick="loadDashboard(); return false;">
          <i class="fas fa-tachometer-alt nav-icon"></i>
          <span>Dashboard</span>
        </a>
      </li>
      
      <li class="menu-toggle">
        <a href="#" onclick="toggleSubmenu(this); return false;">
          <i class="fas fa-users nav-icon"></i>
          <span>Users</span>
          <i class="fas fa-angle-left right"></i>
        </a>
        <ul class="nav-treeview">
          <li>
            <a href="#" onclick="loadContent('All Users'); return false;">
              <i class="far fa-circle nav-icon"></i>
              <span>All Users</span>
            </a>
          </li>
          <li>
            <a href="#" onclick="loadContent('Add User'); return false;">
              <i class="far fa-circle nav-icon"></i>
              <span>Add User</span>
            </a>
          </li>
          <li>
            <a href="#" onclick="loadContent('User Roles'); return false;">
              <i class="far fa-circle nav-icon"></i>
              <span>User Roles</span>
            </a>
          </li>
        </ul>
      </li>
      
      <li class="menu-toggle">
        <a href="#" onclick="toggleSubmenu(this); return false;">
          <i class="fas fa-box nav-icon"></i>
          <span>Products</span>
          <i class="fas fa-angle-left right"></i>
        </a>
        <ul class="nav-treeview">
          <li>
            <a href="#" onclick="loadContent('All Products'); return false;">
              <i class="far fa-circle nav-icon"></i>
              <span>All Products</span>
            </a>
          </li>
          <li>
            <a href="#" onclick="loadContent('Add Product'); return false;">
              <i class="far fa-circle nav-icon"></i>
              <span>Add Product</span>
            </a>
          </li>
          <li>
            <a href="#" onclick="loadContent('Categories'); return false;">
              <i class="far fa-circle nav-icon"></i>
              <span>Categories</span>
            </a>
          </li>
        </ul>
      </li>
      
      <li>
        <a href="#" onclick="loadOrders(); return false;">
          <i class="fas fa-shopping-cart nav-icon"></i>
          <span>Orders</span>
          <span class="badge bg-danger">5</span>
        </a>
      </li>
      
      <li class="nav-header">REPORTS</li>
      <li class="menu-toggle">
        <a href="#" onclick="toggleSubmenu(this); return false;">
          <i class="fas fa-chart-line nav-icon"></i>
          <span>Analytics</span>
          <i class="fas fa-angle-left right"></i>
        </a>
        <ul class="nav-treeview">
          <li>
            <a href="#" onclick="loadContent('Sales Report'); return false;">
              <i class="far fa-circle nav-icon"></i>
              <span>Sales Report</span>
            </a>
          </li>
          <li>
            <a href="#" onclick="loadContent('Analytics'); return false;">
              <i class="far fa-circle nav-icon"></i>
              <span>Analytics</span>
            </a>
          </li>
          <li>
            <a href="#" onclick="loadContent('Export Data'); return false;">
              <i class="far fa-circle nav-icon"></i>
              <span>Export Data</span>
            </a>
          </li>
        </ul>
      </li>
      
      <li class="nav-header">SYSTEM</li>
      <li class="menu-toggle">
        <a href="#" onclick="toggleSubmenu(this); return false;">
          <i class="fas fa-cog nav-icon"></i>
          <span>Settings</span>
          <i class="fas fa-angle-left right"></i>
        </a>
        <ul class="nav-treeview">
          <li>
            <a href="#" onclick="loadContent('General Settings'); return false;">
              <i class="far fa-circle nav-icon"></i>
              <span>General</span>
            </a>
          </li>
          <li>
            <a href="#" onclick="loadContent('Security'); return false;">
              <i class="far fa-circle nav-icon"></i>
              <span>Security</span>
            </a>
          </li>
          <li>
            <a href="#" onclick="loadContent('Notifications'); return false;">
              <i class="far fa-circle nav-icon"></i>
              <span>Notifications</span>
            </a>
          </li>
        </ul>
      </li>

      <li>
        <a href="#" onclick="loadContent('Logout'); return false;">
          <a href="<?php echo BASE_URL; ?>/login/logout"><i class="fas fa-sign-out-alt nav-icon"></i></a>
   
        </a>
      </li>
    </ul>
  </aside>

  <!-- Navbar -->
  <nav class="main-navbar">
    <button class="navbar-toggle" onclick="toggleSidebar()">
      <i class="fas fa-bars"></i>
    </button>
    
    <div class="navbar-menu">
      <!-- Theme Switch -->
      <label class="theme-switch">
        <input type="checkbox" id="themeToggle" onchange="toggleTheme()">
        <span class="theme-slider">
          <i class="fas fa-sun theme-icon sun"></i>
          <i class="fas fa-moon theme-icon moon"></i>
        </span>
      </label>
      
      <button class="navbar-item" onclick="showNotifications()">
        <i class="fas fa-bell"></i>
        <span class="badge-notify">3</span>
      </button>
      <button class="navbar-item" onclick="showMessages()">
        <i class="fas fa-envelope"></i>
        <span class="badge-notify">5</span>
      </button>
      <button class="navbar-item" onclick="toggleFullscreen()">
        <i class="fas fa-expand-arrows-alt"></i>
      </button>
      <div class="navbar-profile" onclick="showProfile()">
        <img src="https://ui-avatars.com/api/?name=Admin+User&background=3498db&color=fff&size=128" alt="Profile">
        <span class="d-none d-md-inline">Admin</span>
      </div>
    </div>
  </nav>

  <!-- Content Wrapper -->
  <div class="content-wrapper" id="contentWrapper">
    <div class="container-fluid">
      <h2 class="mb-4">Dashboard</h2>
      
      <!-- Info Boxes -->
      <div class="row">
        <div class="col-lg-3 col-md-6">
          <div class="info-box">
            <div class="info-box-icon bg-primary">
              <i class="fas fa-shopping-bag"></i>
            </div>
            <div class="info-box-content">
              <h3>150</h3>
              <p>New Orders</p>
            </div>
          </div>
        </div>
        
        <div class="col-lg-3 col-md-6">
          <div class="info-box">
            <div class="info-box-icon bg-success">
              <i class="fas fa-chart-line"></i>
            </div>
            <div class="info-box-content">
              <h3>53%</h3>
              <p>Bounce Rate</p>
            </div>
          </div>
        </div>
        
        <div class="col-lg-3 col-md-6">
          <div class="info-box">
            <div class="info-box-icon bg-warning">
              <i class="fas fa-user-plus"></i>
            </div>
            <div class="info-box-content">
              <h3>44</h3>
              <p>User Registrations</p>
            </div>
          </div>
        </div>
        
        <div class="col-lg-3 col-md-6">
          <div class="info-box">
            <div class="info-box-icon bg-danger">
              <i class="fas fa-eye"></i>
            </div>
            <div class="info-box-content">
              <h3>65</h3>
              <p>Unique Visitors</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Data Table Card -->
      <div class="row">
        <div class="col-12">
          <div class="card">
            <div class="card-header">
              <h5 class="mb-0">Recent Orders</h5>
            </div>
            <div class="card-body">
              <div class="table-responsive">
                <table class="table table-hover">
                  <thead>
                    <tr>
                      <th>#</th>
                      <th>Customer</th>
                      <th>Product</th>
                      <th>Amount</th>
                      <th>Status</th>
                      <th>Action</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr>
                      <td>1</td>
                      <td>John Doe</td>
                      <td>Laptop</td>
                      <td>$999</td>
                      <td><span class="badge bg-success">Delivered</span></td>
                      <td>
                        <button class="btn btn-sm btn-primary" onclick="viewOrder(1)">
                          <i class="fas fa-eye"></i>
                        </button>
                      </td>
                    </tr>
                    <tr>
                      <td>2</td>
                      <td>Jane Smith</td>
                      <td>Phone</td>
                      <td>$599</td>
                      <td><span class="badge bg-warning">Pending</span></td>
                      <td>
                        <button class="btn btn-sm btn-primary" onclick="viewOrder(2)">
                          <i class="fas fa-eye"></i>
                        </button>
                      </td>
                    </tr>
                    <tr>
                      <td>3</td>
                      <td>Bob Johnson</td>
                      <td>Tablet</td>
                      <td>$399</td>
                      <td><span class="badge bg-info">Processing</span></td>
                      <td>
                        <button class="btn btn-sm btn-primary" onclick="viewOrder(3)">
                          <i class="fas fa-eye"></i>
                        </button>
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </div>
      </div>

    </div>
  </div>

  <!-- Bootstrap 5 JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <!-- SweetAlert2 -->
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.10.0/dist/sweetalert2.all.min.js"></script>

  <script>
    // Theme Toggle
    function toggleTheme() {
      const html = document.documentElement;
      const currentTheme = html.getAttribute('data-theme');
      const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
      
      html.setAttribute('data-theme', newTheme);
      localStorage.setItem('theme', newTheme);
    }

    // Load saved theme
    function loadTheme() {
      const savedTheme = localStorage.getItem('theme') || 'light';
      document.documentElement.setAttribute('data-theme', savedTheme);
      document.getElementById('themeToggle').checked = savedTheme === 'dark';
    }

    // Toggle Sidebar
    function toggleSidebar() {
      const sidebar = document.getElementById('sidebar');
      const overlay = document.getElementById('sidebarOverlay');
      
      if (window.innerWidth <= 768) {
        // Mobile view - slide in/out
        sidebar.classList.toggle('show');
        overlay.classList.toggle('show');
      } else {
        // Desktop view - collapse/expand
        sidebar.classList.toggle('collapsed');
      }
    }

    // Close Sidebar
    function closeSidebar() {
      const sidebar = document.getElementById('sidebar');
      const overlay = document.getElementById('sidebarOverlay');
      
      sidebar.classList.remove('show');
      overlay.classList.remove('show');
    }

    // Toggle Submenu
    function toggleSubmenu(element) {
      const parent = element.parentElement;
      const submenu = parent.querySelector('.nav-treeview');
      
      // Close other submenus (AdminLTE behavior)
      document.querySelectorAll('.nav-treeview').forEach(menu => {
        if (menu !== submenu) {
          menu.classList.remove('show');
          menu.parentElement.classList.remove('menu-open');
        }
      });
      
      // Toggle current submenu
      submenu.classList.toggle('show');
      parent.classList.toggle('menu-open');
    }

    // Toggle Fullscreen
    function toggleFullscreen() {
      if (!document.fullscreenElement) {
        document.documentElement.requestFullscreen().catch(err => {
          Swal.fire({
            icon: 'error',
            title: 'Fullscreen Error',
            text: 'Unable to enter fullscreen mode'
          });
        });
      } else {
        if (document.exitFullscreen) {
          document.exitFullscreen();
        }
      }
    }

    // Navigation Functions
    function loadDashboard() {
      showLoader();
      setActiveMenu(0);
      setTimeout(() => {
        document.getElementById('contentWrapper').innerHTML = `
          <div class="container-fluid">
            <h2 class="mb-4">Dashboard</h2>
            <div class="alert alert-success">Dashboard loaded successfully!</div>
          </div>
        `;
      }, 500);
    }

    function loadContent(title) {
      showLoader();
      setTimeout(() => {
        document.getElementById('contentWrapper').innerHTML = `
          <div class="container-fluid">
            <h2 class="mb-4">${title}</h2>
            <div class="card">
              <div class="card-body">
                <p>${title} content goes here...</p>
              </div>
            </div>
          </div>
        `;
      }, 500);
    }

    function loadOrders() {
      showLoader();
      setTimeout(() => {
        document.getElementById('contentWrapper').innerHTML = `
          <div class="container-fluid">
            <h2 class="mb-4">Orders</h2>
            <div class="card">
              <div class="card-body">
                <p>Orders content goes here...</p>
              </div>
            </div>
          </div>
        `;
      }, 500);
    }

    function setActiveMenu(index) {
      const menuItems = document.querySelectorAll('.sidebar-menu > li > a');
      menuItems.forEach((item, i) => {
        if (i === index) {
          item.classList.add('active');
        } else {
          item.classList.remove('active');
        }
      });
    }

    // SweetAlert Examples
    function showNotifications() {
      Swal.fire({
        title: 'Notifications',
        html: `
          <div class="text-start">
            <p><i class="fas fa-info-circle text-info"></i> New user registered</p>
            <p><i class="fas fa-check-circle text-success"></i> Order #1234 completed</p>
            <p><i class="fas fa-exclamation-circle text-warning"></i> Low stock alert</p>
          </div>
        `,
        icon: 'info',
        confirmButtonText: 'Close'
      });
    }

    function showMessages() {
      Swal.fire({
        title: 'Messages',
        html: `
          <div class="text-start">
            <p><strong>John Doe:</strong> Hello, I need help...</p>
            <p><strong>Jane Smith:</strong> Order status?</p>
            <p><strong>Bob Johnson:</strong> Great service!</p>
          </div>
        `,
        icon: 'info',
        confirmButtonText: 'View All'
      });
    }

    function showProfile() {
      Swal.fire({
        title: 'User Profile',
        html: `
          <div class="text-center">
            <img src="https://ui-avatars.com/api/?name=Admin+User&background=3498db&color=fff&size=128" 
                 style="width: 100px; height: 100px; border-radius: 50%; margin-bottom: 15px;">
            <h4>Alexander Pierce</h4>
            <p class="text-muted">admin@example.com</p>
          </div>
        `,
        showCancelButton: true,
        confirmButtonText: 'Edit Profile',
        cancelButtonText: 'Logout',
        confirmButtonColor: '#3498db',
        cancelButtonColor: '#dc3545'
      }).then((result) => {
        if (result.isDismissed && result.dismiss === Swal.DismissReason.cancel) {
          Swal.fire('Logged Out', 'You have been logged out successfully', 'success');
        }
      });
    }

    function viewOrder(orderId) {
      Swal.fire({
        title: `Order #${orderId}`,
        html: `
          <div class="text-start">
            <p><strong>Customer:</strong> John Doe</p>
            <p><strong>Product:</strong> Laptop</p>
            <p><strong>Amount:</strong> $999</p>
            <p><strong>Status:</strong> Delivered</p>
          </div>
        `,
        icon: 'info',
        confirmButtonText: 'Close'
      });
    }

    // Auto-hide mobile sidebar on menu click (only for non-toggle items)
    document.querySelectorAll('.sidebar-menu a').forEach(link => {
      link.addEventListener('click', (e) => {
        // Don't close sidebar if clicking on menu-toggle (submenu parent)
        if (link.parentElement.classList.contains('menu-toggle')) {
          return; // Let the toggleSubmenu function handle it
        }
        
        // Close sidebar only for regular menu items on mobile
        if (window.innerWidth <= 768) {
          closeSidebar();
        }
      });
    });

    // Close submenus when sidebar collapses on mobile
    window.addEventListener('resize', () => {
      if (window.innerWidth <= 768) {
        // Only close sidebar if it's showing
        if (document.getElementById('sidebar').classList.contains('show')) {
          closeSidebar();
        }
        // Close submenus on resize
        document.querySelectorAll('.nav-treeview').forEach(menu => {
          menu.classList.remove('show');
          menu.parentElement.classList.remove('menu-open');
        });
      }
    });

    // Initialize theme on page load
    loadTheme();

    // Hide loader after page loads
    window.addEventListener('load', () => {
      setTimeout(() => {
        document.getElementById('pageLoader').classList.add('hidden');
      }, 500);
    });

    // Show loader function for navigation
    function showLoader() {
      const loader = document.getElementById('pageLoader');
      loader.classList.remove('hidden');
      
      // Auto-hide after 1 second
      setTimeout(() => {
        loader.classList.add('hidden');
      }, 1000);
    }
  </script>
<script src="<?php echo BASE_URL; ?>/assets/js/app.js"></script>
  

</body>
</html>