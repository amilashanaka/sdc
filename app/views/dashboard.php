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

  <link rel="stylesheet" href="./assets/css/dashboard.css">
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