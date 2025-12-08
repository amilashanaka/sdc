
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

  <link href="./assets/css/style.css" rel="stylesheet">
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
  <script src="./assets/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>

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
<?php
// Start session
session_start();

// Define paths
define('ROOT', dirname(__FILE__));
define('APP', ROOT . '/app');
define('CONFIG', ROOT . '/config');
define('VIEWS', APP . '/views');
define('ASSETS', ROOT . '/assets');

// Auto-detect base URL
$isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || 
           (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') ||
           (isset($_SERVER['HTTP_X_FORWARDED_SSL']) && $_SERVER['HTTP_X_FORWARDED_SSL'] === 'on');

$protocol = $isHttps ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'];
$scriptDir = dirname($_SERVER['SCRIPT_NAME']);

// Clean path (handle Windows backslashes)
$basePath = rtrim(str_replace('\\', '/', $scriptDir), '/');
$baseUrl = $protocol . '://' . $host . $basePath;

// Load config
$config = require CONFIG . '/config.php';

// Allow config to override, otherwise use auto-detected
if (!empty($config['base_url'])) {
    define('BASE_URL', rtrim($config['base_url'], '/'));
} else {
    define('BASE_URL', $baseUrl);
}

// Simple autoloader
spl_autoload_register(function($class) {
    $paths = [APP . '/core/', APP . '/controllers/', APP . '/models/'];
    foreach ($paths as $path) {
        $file = $path . $class . '.php';
        if (file_exists($file)) {
            require_once $file;
            return;
        }
    }
});

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