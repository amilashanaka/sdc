<!doctype html>
<html lang="en" data-bs-theme="light">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Spicer Consulting Device Login</title>

  <!-- Bootstrap 5.3 (jsDelivr CDN) -->
  <link href="./assets/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
  <!-- Font Awesome -->
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
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
      max-width: 1000px;
      width: 100%;
      padding: 2rem;
    }

    .brand-logo {
      width: 120px;
      height: 120px;
      border-radius: 20px;
   
      display: flex;
      align-items: center;
      justify-content: center;
 
    }

    .brand-logo img {
      max-width: 80px;
      max-height: 80px;
    }

    .brand-logo i {
      font-size: 48px;
      color: white;
    }

    .device-meta {
      font-size: 0.875rem;
      color: var(--text-muted);
      font-weight: 500;
    }

    .card {
      border-radius: var(--card-radius);
      box-shadow: 0 8px 32px rgba(16, 24, 40, 0.12);
      border: none;
      overflow: hidden;
    }

    .form-control {
      border-radius: 0.5rem;
      padding: 0.75rem 1rem;
      padding-left: 3rem;
      border: 1px solid #dee2e6;
      font-size: 0.9375rem;
    }

    .form-control:focus {
      box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.15);
      border-color: var(--primary-color);
    }

    .input-icon-wrapper {
      position: relative;
    }

    .input-icon {
      position: absolute;
      left: 1rem;
      top: 50%;
      transform: translateY(-50%);
      color: var(--text-muted);
      font-size: 1rem;
      z-index: 10;
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
      background: linear-gradient(135deg, rgba(13, 110, 253, 0.08) 0%, rgba(13, 110, 253, 0.02) 100%);
      border-right: 1px solid rgba(13, 110, 253, 0.08);
    }

    @media (max-width: 991px) {
      .left-illustration {
        display: none;
      }
    }

    .list-unstyled li i {
      color: var(--primary-color);
      width: 20px;
    }

    .btn-primary {
      border-radius: 0.5rem;
      font-weight: 600;
      padding: 0.875rem;
      font-size: 1rem;
      letter-spacing: 0.3px;
    }

    .btn-primary:hover {
      transform: translateY(-1px);
      box-shadow: 0 4px 12px rgba(13, 110, 253, 0.3);
      transition: all 0.2s ease;
    }

    footer a {
      text-decoration: none;
      color: var(--primary-color);
      font-weight: 500;
    }

    footer a:hover {
      text-decoration: underline;
    }

    .brand-header {
      display: flex;
      align-items: center;
      gap: 1.5rem;
      margin-bottom: 2.5rem;
    }

    .brand-info h5 {
      font-size: 1.25rem;
      margin-bottom: 0.25rem;
      font-weight: 700;
      letter-spacing: -0.3px;
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
      box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
    }

    [data-bs-theme="dark"] .left-illustration {
      background: linear-gradient(135deg, rgba(13, 110, 253, 0.15) 0%, rgba(13, 110, 253, 0.05) 100%);
      border-right: 1px solid rgba(255, 255, 255, 0.1);
    }

    [data-bs-theme="dark"] .brand-logo {
      background: linear-gradient(135deg, #0d6efd 0%, #0a58ca 100%);
      box-shadow: 0 8px 24px rgba(13, 110, 253, 0.4);
    }

    [data-bs-theme="dark"] .form-control {
      background-color: #2b3035;
      border-color: #495057;
      color: #ffffff;
    }

    [data-bs-theme="dark"] .form-control:focus {
      border-color: var(--primary-color);
      background-color: #2b3035;
    }

    [data-bs-theme="dark"] .input-icon {
      color: #adb5bd;
    }

    [data-bs-theme="dark"] .input-group .btn-outline-secondary {
      border-color: #495057;
      color: #adb5bd;
      background-color: #2b3035;
    }

    [data-bs-theme="dark"] .input-group .btn-outline-secondary:hover {
      background-color: #495057;
    }

    [data-bs-theme="dark"] .border-top {
      border-color: #495057 !important;
    }

    .feature-badge {
      display: inline-flex;
      align-items: center;
      padding: 0.5rem 1rem;
      background: rgba(13, 110, 253, 0.08);
      border-radius: 8px;
      font-size: 0.875rem;
      font-weight: 500;
      color: var(--primary-color);
      margin-bottom: 0.75rem;
    }

    [data-bs-theme="dark"] .feature-badge {
      background: rgba(13, 110, 253, 0.2);
    }
  </style>
</head>
<body>
  <main class="login-wrapper">
    <div class="card">
      <div class="row g-0">
        <!-- Left illustration / info (hidden on small screens) -->
        <div class="col-lg-5 left-illustration d-flex flex-column justify-content-center p-5">
          <div class="brand-header">
            <div class="brand-logo">
              <img src="./assets/img/logo.png" alt="Spicer Consulting Logo">
            </div>
            <div class="brand-info">
              <h5 class="mb-0">Spicer Consulting</h5>
              <div class="device-meta">SC-24123 • FW 1.2.0</div>
            </div>
          </div>

          <div class="feature-badge">
            <i class="fas fa-shield-alt me-2"></i>
            Administrator Access
          </div>

          <h4 class="mt-2 mb-3 fw-bold">Device Management Portal</h4>
          <p class="help-text mb-4">Securely manage device settings, monitor performance, and configure user accounts.</p>

          <ul class="list-unstyled small-muted">
            <li class="mb-3"><i class="fas fa-chart-line me-2"></i>16 ADC Channel Support</li>
            <li class="mb-3"><i class="fas fa-plug me-2"></i>Debug Attached Modules</li>
            <li class="mb-3"><i class="fas fa-wifi me-2"></i>Real-time Health Monitoring</li>
            <li class="mb-3"><i class="fas fa-lock me-2"></i>Enterprise-grade Security</li>
          </ul>

          <div class="mt-auto pt-4 small-muted">
            <div class="mb-2"><i class="fas fa-network-wired me-2"></i>IP: <strong>192.168.0.1</strong></div>
            <div><i class="fas fa-calendar-alt me-2"></i>Provisioned: <strong>December 5, 2025</strong></div>
          </div>
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

          <form id="loginForm" class="needs-validation" novalidate>
            <div class="mb-3">
              <label for="username" class="form-label fw-medium">Username</label>
              <div class="input-icon-wrapper">
                <i class="fas fa-user input-icon"></i>
                <input type="text" class="form-control" id="username" value="admin" required aria-describedby="userHelp">
              </div>
              <div class="invalid-feedback">Please enter your username.</div>
              <div id="userHelp" class="form-text help-text">Default: <code>admin</code></div>
            </div>

            <div class="mb-3">
              <label for="password" class="form-label fw-medium">Password</label>
              <div class="input-icon-wrapper">
                <i class="fas fa-lock input-icon"></i>
                <div class="input-group">
                  <input type="password" class="form-control" id="password" minlength="4" required aria-describedby="pwHelp" style="padding-left: 3rem;">
                  <button class="btn btn-outline-secondary" type="button" id="togglePassword" aria-label="Toggle password visibility">
                    <i class="fas fa-eye"></i>
                  </button>
                </div>
              </div>
              <div class="invalid-feedback">Please enter a valid password (minimum 4 characters).</div>
              <div id="pwHelp" class="form-text help-text">Password is case-sensitive.</div>
            </div>

            <div class="row align-items-center mb-4">
              <div class="col-auto">
                <div class="form-check">
                  <input class="form-check-input" type="checkbox" id="remember">
                  <label class="form-check-label" for="remember">Remember this browser</label>
                </div>
              </div>
              <div class="col text-end small-muted">
                <a href="#" class="text-decoration-none">Forgot password?</a>
              </div>
            </div>

            <div class="d-grid mb-4">
              <button type="submit" class="btn btn-primary btn-lg">
                <i class="fas fa-sign-in-alt me-2"></i>Sign In
              </button>
            </div>

            <div class="border-top pt-3 d-flex justify-content-between align-items-center small-muted">
              <div><i class="fas fa-clock me-1"></i>Last login: <strong id="lastLogin">—</strong></div>
              <div><i class="fas fa-code-branch me-1"></i>Build: <strong>2025-12-05</strong></div>
            </div>
          </form>

          <footer class="mt-4 small-muted text-center">
            Need help? Visit <a href="#"><i class="fas fa-life-ring me-1"></i>support.spicerconsulting.com</a>
          </footer>
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
      const form = document.getElementById('loginForm');
      form.addEventListener('submit', function(e) {
        e.preventDefault();
        if (!form.checkValidity()) {
          form.classList.add('was-validated');
          return;
        }

        // Mock authentication (replace with real auth logic)
        const username = document.getElementById('username').value;
        const password = document.getElementById('password').value;
        if (username === 'admin' && password === 'admin') {
          showToast('Login successful — redirecting...', 'success');
          setTimeout(() => { window.location.href = '/admin'; }, 800);
        } else {
          showToast('Invalid username or password', 'danger');
        }
      });

      // Password toggle
      const togglePw = document.getElementById('togglePassword');
      const pwField = document.getElementById('password');
      togglePw.addEventListener('click', function() {
        const type = pwField.getAttribute('type') === 'password' ? 'text' : 'password';
        pwField.setAttribute('type', type);
        const icon = this.querySelector('i');
        icon.classList.toggle('fa-eye');
        icon.classList.toggle('fa-eye-slash');
      });

      // Theme toggle
      const themeToggle = document.getElementById('themeToggle');
      themeToggle.addEventListener('change', e => {
        document.documentElement.setAttribute('data-bs-theme', e.target.checked ? 'dark' : 'light');
      });

      // Toast utility
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
      }

      // Mock last login
      document.getElementById('lastLogin').textContent = new Date().toLocaleString();
    })();
  </script>
</body>
</html>