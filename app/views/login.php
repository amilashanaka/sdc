<!DOCTYPE html>
<html lang="en" data-bs-theme="light">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Admin Login</title>

  <!-- Bootstrap 5.3 (jsDelivr CDN) -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
  <!-- Bootstrap Icons -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
  <!-- Google Fonts: Inter -->
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

  <style>
    :root {
      --card-radius: 16px;
      --primary-color: #0d6efd;
      --text-muted: #6c757d;
      --text-subtle: #9aa3b2;
      --bg-gradient-start: #f6f8fb;
      --bg-gradient-end: #e9eef6;
    }

    body {
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      background: linear-gradient(180deg, var(--bg-gradient-start) 0%, var(--bg-gradient-end) 100%);
      font-family: 'Inter', system-ui, -apple-system, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
      font-size: 1rem;
      line-height: 1.5;
    }

    .login-wrapper {
      max-width: 980px;
      width: 100%;
      padding: 2rem;
    }

    .brand-logo {
      width: 60px;
      height: 60px;
      background: rgba(13, 110, 253, 0.1);
      border-radius: 12px;
      display: flex;
      align-items: center;
      justify-content: center;
    }

    .brand-logo img {
      max-width: 40px;
      max-height: 40px;
    }

    .device-meta {
      font-size: 0.875rem;
      color: var(--text-muted);
    }

    .card {
      border-radius: var(--card-radius);
      box-shadow: 0 6px 24px rgba(16, 24, 40, 0.08);
      border: none;
    }

    .form-control {
      border-radius: 0.375rem;
    }

    .form-control:focus {
      box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
      border-color: var(--primary-color);
    }

    .help-text {
      font-size: 0.875rem;
      color: var(--text-muted);
    }

    .small-muted {
      font-size: 0.8125rem;
      color: var(--text-subtle);
    }

    .left-illustration {
      background: linear-gradient(180deg, rgba(13, 110, 253, 0.1) 0%, transparent 100%);
      border-right: 1px solid rgba(13, 110, 253, 0.05);
    }

    @media (max-width: 991px) {
      .left-illustration {
        display: none;
      }
    }

    .list-unstyled li i {
      color: var(--primary-color);
    }

    .btn-primary {
      border-radius: 0.375rem;
      font-weight: 600;
    }

    footer a {
      text-decoration: none;
      color: var(--primary-color);
    }

    footer a:hover {
      text-decoration: underline;
    }

    /* Dark mode adjustments */
    [data-bs-theme="dark"] {
      --bg-gradient-start: #1a1d21;
      --bg-gradient-end: #2c3035;
      --text-muted: #adb5bd;
      --text-subtle: #6c757d;
    }

    [data-bs-theme="dark"] body {
      background: linear-gradient(180deg, var(--bg-gradient-start) 0%, var(--bg-gradient-end) 100%);
    }

    [data-bs-theme="dark"] .card {
      background-color: #212529;
      box-shadow: 0 6px 24px rgba(0, 0, 0, 0.2);
    }

    [data-bs-theme="dark"] .left-illustration {
      background: linear-gradient(180deg, rgba(13, 110, 253, 0.2) 0%, transparent 100%);
      border-right: 1px solid rgba(255, 255, 255, 0.1);
    }

    [data-bs-theme="dark"] .brand-logo {
      background-color: #343a40;
      border-color: rgba(255, 255, 255, 0.1);
    }

    [data-bs-theme="dark"] .form-control {
      background-color: #343a40;
      border-color: #495057;
      color: #ffffff;
    }

    [data-bs-theme="dark"] .form-control:focus {
      border-color: var(--primary-color);
    }

    [data-bs-theme="dark"] .input-group .btn-outline-secondary {
      border-color: #495057;
      color: #adb5bd;
    }

    [data-bs-theme="dark"] .input-group .btn-outline-secondary:hover {
      background-color: #495057;
    }

    [data-bs-theme="dark"] .border-top {
      border-color: #495057 !important;
    }
  </style>
</head>
<body>
  <main class="login-wrapper">
    <div class="card overflow-hidden">
      <div class="row g-0">
        <!-- Left illustration / info (hidden on small screens) -->
        <div class="col-lg-5 left-illustration d-flex flex-column justify-content-center p-5">
          <div class="mb-4 d-flex align-items-center gap-3">
            <div class="brand-logo">
             <img src="./assets/img/logo.png" alt="Spicer Consulting Logo">
            </div>
            <div>
              <h5 class="mb-0 fw-semibold">Admin Panel</h5>
              <div class="device-meta">System • Version: 1.0.0</div>
            </div>
          </div>

          <h4 class="mt-4 fw-bold">Administrator Access</h4>
          <p class="help-text mb-4">Manage system settings and user accounts securely.</p>

          <ul class="list-unstyled small-muted">
            <li class="mb-3"><i class="bi bi-shield-lock-fill me-2"></i>Secure Authentication</li>
            <li class="mb-3"><i class="bi bi-graph-up me-2"></i>User Management</li>
            <li class="mb-3"><i class="bi bi-wifi me-2"></i>System Control</li>
          </ul>

          <div class="mt-auto small-muted">System: <strong>Admin Panel</strong> • Date: <strong><?php echo date('Y-m-d'); ?></strong></div>
        </div>

        <!-- Right: login form -->
        <div class="col-lg-7 p-4 p-lg-5">
          <div class="d-flex justify-content-between align-items-start mb-4">
            <div>
              <h3 class="mb-1 fw-bold">Admin Login</h3>
              <div class="small-muted">Enter your administrator credentials to continue.</div>
            </div>

            <div class="text-end">
              <div class="form-check form-switch">
                <input class="form-check-input" type="checkbox" id="themeToggle" aria-label="Toggle dark mode">
                <label class="form-check-label small-muted" for="themeToggle">Dark Mode</label>
              </div>
            </div>
          </div>

          <?php if (!empty($error)): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
              <i class="bi bi-exclamation-triangle-fill me-2"></i>
              <?php echo htmlspecialchars($error); ?>
              <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
          <?php endif; ?>

          <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
              <i class="bi bi-check-circle-fill me-2"></i>
              <?php echo htmlspecialchars($_SESSION['success']); ?>
              <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            <?php unset($_SESSION['success']); ?>
          <?php endif; ?>

          <form method="POST" action="" class="needs-validation" novalidate>
            <div class="mb-3">
              <label for="username" class="form-label fw-medium">Username</label>
              <input type="text" class="form-control" id="username" name="username" 
                     value="<?php echo htmlspecialchars($_POST['username'] ?? 'admin'); ?>" 
                     required aria-describedby="userHelp">
              <div class="invalid-feedback">Please enter your username.</div>
              <div id="userHelp" class="form-text help-text">Default: <code>admin</code></div>
            </div>

            <div class="mb-3 position-relative">
              <label for="password" class="form-label fw-medium">Password</label>
              <div class="input-group">
                <input type="password" class="form-control" id="password" name="password" 
                       minlength="4" required aria-describedby="pwHelp">
                <button class="btn btn-outline-secondary" type="button" id="togglePassword" 
                        aria-label="Toggle password visibility"><i class="bi bi-eye"></i></button>
              </div>
              <div class="invalid-feedback">Please enter a valid password (minimum 4 characters).</div>
              <div id="pwHelp" class="form-text help-text">Password is case-sensitive. Default: <code>admin123</code></div>
            </div>

            <div class="row align-items-center mb-4">
              <div class="col-auto">
                <div class="form-check">
                  <input class="form-check-input" type="checkbox" id="remember" name="remember" value="1">
                  <label class="form-check-label" for="remember">Remember this browser</label>
                </div>
              </div>
              <div class="col text-end small-muted">
                <a href="#" class="text-decoration-none">Forgot password?</a>
              </div>
            </div>

            <div class="d-grid mb-4">
              <button type="submit" class="btn btn-primary btn-lg">Sign In</button>
            </div>

            <div class="border-top pt-3 d-flex justify-content-between align-items-center small-muted">
              <div>Last login: <strong id="lastLogin">—</strong></div>
              <div>Version: <strong>1.0.0</strong></div>
            </div>
          </form>

          <footer class="mt-4 small-muted text-center">Admin Panel &copy; <?php echo date('Y'); ?></footer>
        </div>
      </div>
    </div>
  </main>

  <!-- Bootstrap JS bundle -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>

  <script>
    (function() {
      'use strict';

      // Form validation on submit
      const form = document.querySelector('form');
      form.addEventListener('submit', function(e) {
        if (!form.checkValidity()) {
          e.preventDefault();
          form.classList.add('was-validated');
        }
        // If valid, form will submit to LoginController
      });

      // Password toggle
      const togglePw = document.getElementById('togglePassword');
      const pwField = document.getElementById('password');
      if (togglePw && pwField) {
        togglePw.addEventListener('click', function() {
          const type = pwField.getAttribute('type') === 'password' ? 'text' : 'password';
          pwField.setAttribute('type', type);
          this.querySelector('i').classList.toggle('bi-eye');
          this.querySelector('i').classList.toggle('bi-eye-slash');
        });
      }

      // Theme toggle
      const themeToggle = document.getElementById('themeToggle');
      if (themeToggle) {
        // Load saved theme
        const savedTheme = localStorage.getItem('theme') || 'light';
        document.documentElement.setAttribute('data-bs-theme', savedTheme);
        themeToggle.checked = savedTheme === 'dark';
        
        themeToggle.addEventListener('change', e => {
          const theme = e.target.checked ? 'dark' : 'light';
          document.documentElement.setAttribute('data-bs-theme', theme);
          localStorage.setItem('theme', theme);
        });
      }

      // Remember me functionality
      const rememberCheckbox = document.getElementById('remember');
      const usernameInput = document.getElementById('username');
      
      // Load saved username if remember me was checked
      const savedUsername = localStorage.getItem('rememberedUsername');
      if (savedUsername && usernameInput) {
        usernameInput.value = savedUsername;
        rememberCheckbox.checked = true;
      }
      
      // Save username on form submit if remember me is checked
      form.addEventListener('submit', function() {
        if (rememberCheckbox.checked && usernameInput.value) {
          localStorage.setItem('rememberedUsername', usernameInput.value);
        } else {
          localStorage.removeItem('rememberedUsername');
        }
      });

      // Last login display
      const lastLoginElement = document.getElementById('lastLogin');
      if (lastLoginElement) {
        const lastLogin = localStorage.getItem('lastLogin');
        if (lastLogin) {
          lastLoginElement.textContent = new Date(lastLogin).toLocaleString();
        }
        
        // Update last login time on form submit
        form.addEventListener('submit', function() {
          localStorage.setItem('lastLogin', new Date().toISOString());
        });
      }

      // Auto-focus username field
      if (usernameInput) {
        setTimeout(() => {
          usernameInput.focus();
          usernameInput.select();
        }, 100);
      }

      // Toast utility (for future enhancements)
      function showToast(message, variant = 'success') {
        const toast = document.createElement('div');
        toast.className = `toast align-items-center text-bg-${variant} border-0 position-fixed bottom-0 end-0 m-3`;
        toast.setAttribute('role', 'alert');
        toast.setAttribute('aria-live', 'assertive');
        toast.setAttribute('aria-atomic', 'true');
        toast.innerHTML = `
          <div class="d-flex">
            <div class="toast-body">${message}</div>
            <button type="button" class="btn-close me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
          </div>
        `;
        document.body.appendChild(toast);
        const bsToast = new bootstrap.Toast(toast);
        bsToast.show();
        
        // Remove from DOM after hide
        toast.addEventListener('hidden.bs.toast', function () {
          toast.remove();
        });
      }

    })();
  </script>
</body>
</html>