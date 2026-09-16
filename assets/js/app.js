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
        document.body.classList.toggle('sidebar-collapsed');
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

      if (!submenu) {
        return;
      }
      
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
    function showProfile() {
      const profileBtn = document.querySelector('.navbar-profile');
      const userId = profileBtn ? profileBtn.dataset.userId : 0;

      Swal.fire({
        title: 'User Profile',
        html: `
          <div class="text-center">
            <i class="fas fa-user-circle profile-modal-icon" aria-hidden="true"></i>
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
        if (result.isConfirmed) {
          // Edit Profile clicked - redirect to user edit page
          if (userId > 0) {
            window.location.href = BASE_URL + '/user?id=' + btoa(userId);
          }
        } else if (result.isDismissed && result.dismiss === Swal.DismissReason.cancel) {
          Swal.fire({
            icon: 'success',
            title: 'Logged out',
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 1200,
            timerProgressBar: true
          });
          setTimeout(() => {
            window.location.href = BASE_URL + '/login/logout';
          }, 700);
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
        if (link.parentElement.classList.contains('menu-toggle') || link.parentElement.classList.contains('has-treeview')) {
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

    function setupPasswordFieldControls() {
      document.querySelectorAll('[data-password-toggle]').forEach(button => {
        button.addEventListener('click', () => {
          const input = document.getElementById(button.dataset.passwordToggle);
          if (!input) {
            return;
          }

          const isHidden = input.type === 'password';
          input.type = isHidden ? 'text' : 'password';

          const icon = button.querySelector('i');
          if (icon) {
            icon.classList.toggle('fa-eye', isHidden);
            icon.classList.toggle('fa-eye-slash', !isHidden);
          }

          const label = button.dataset.passwordLabel || 'password';
          const action = isHidden ? `Show ${label}` : `Hide ${label}`;
          button.setAttribute('aria-label', action);
          button.title = action;
        });
      });

      document.querySelectorAll('[data-password-copy]').forEach(button => {
        button.addEventListener('click', async () => {
          const input = document.getElementById(button.dataset.passwordCopy);
          if (!input || input.value === '') {
            return;
          }

          const icon = button.querySelector('i');
          const originalIconClass = icon ? icon.className : '';
          const label = button.dataset.passwordLabel || 'password';
          const setIcon = (className, title) => {
            if (icon) {
              icon.className = className;
            }
            button.title = title;
          };

          try {
            if (navigator.clipboard && window.isSecureContext) {
              await navigator.clipboard.writeText(input.value);
            } else {
              input.focus();
              input.select();
              input.setSelectionRange(0, input.value.length);
              if (typeof document.execCommand !== 'function' || !document.execCommand('copy')) {
                throw new Error('Copy failed');
              }
            }

            setIcon('fas fa-check', `Copied ${label}`);
            window.setTimeout(() => setIcon(originalIconClass, `Copy ${label}`), 1500);
          } catch {
            setIcon('fas fa-times', `Unable to copy ${label}`);
            window.setTimeout(() => setIcon(originalIconClass, `Copy ${label}`), 1500);
          }
        });
      });
    }

    setupPasswordFieldControls();