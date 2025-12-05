<!doctype html>
<html lang="en" data-bs-theme="light">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Spicer Consulting Device Login</title>

  <!-- Bootstrap 5.3 (jsDelivr CDN) -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="" crossorigin="anonymous">
  <!-- Bootstrap Icons (optional) -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

  <style>
    /* Professional, subtle background */
    :root{--card-radius:16px}
    body{min-height:100vh;display:flex;align-items:center;justify-content:center;background:linear-gradient(180deg,#f6f8fb 0%, #e9eef6 100%);font-family:Inter, system-ui, -apple-system, 'Segoe UI', Roboto, 'Helvetica Neue', Arial}
    .login-wrapper{max-width:980px;width:100%;padding:2rem}
    .brand-logo{width:56px;height:56px;border-radius:10px;display:inline-flex;align-items:center;justify-content:center;font-weight:700}
    .device-meta{font-size:.9rem;color:#6c757d}
    .card{border-radius:var(--card-radius);box-shadow:0 6px 24px rgba(16,24,40,0.08)}
    .form-control:focus{box-shadow:0 0 0 .15rem rgba(13,110,253,.12)}
    .help-text{font-size:.85rem;color:#6c757d}
    .small-muted{font-size:.78rem;color:#9aa3b2}
    /* center column */
    .left-illustration{background:linear-gradient(180deg,#0d6efd20, transparent);border-right:1px solid rgba(13,110,253,0.04);}
    @media (max-width:991px){.left-illustration{display:none}}
  </style>
</head>
<body>
  <main class="login-wrapper">
    <div class="card overflow-hidden">
      <div class="row g-0">
        <!-- Left illustration / info (hidden on small screens) -->
        <div class="col-lg-5 left-illustration d-flex flex-column justify-content-center p-5">
          <div class="mb-3 d-flex align-items-center gap-3">
            <div class="brand-logo bg-white border  align-items-center ">
               <img src="./assets/img/logo.png" style="max-width: 100px;">
            </div>
        
          </div>

              <div class="mt-3">
              <h5 class="mb-0">Spicer Consulting</h5>
              <div class="device-meta">Serila Number : SC 24123 • Firmware: 1.2.0</div>
            </div>

          <h4 class="mt-4">Administrator access</h4>
          <p class="help-text">Manage device settings and user accounts</p>

          <ul class="list-unstyled small-muted mt-3">
            <li class="mb-2"><i class="bi bi-shield-lock-fill me-2"></i> 16 ADC Channel Support</li>
            <li class="mb-2"><i class="bi bi-graph-up me-2"></i> Debug all attched modules </li>
            <li class="mb-2"><i class="bi bi-wifi me-2"></i> Monitor device Helth</li>
          </ul>

          <div class="mt-4 small-muted">IP: <strong>192.168.0.1</strong> ·Provisioned on: <strong>2025-12-05</strong></div>
        </div>

        <!-- Right: login form -->
        <div class="col-lg-7 p-4 p-lg-5">
          <div class="d-flex justify-content-between align-items-start mb-3">
            <div>
              <h3 class="mb-0">Admin Login</h3>
              <div class="small-muted">Enter your administrator credentials to continue</div>
            </div>

            <div class="text-end">
              <div class="form-check form-switch">
                <input class="form-check-input" type="checkbox" id="themeToggle" aria-label="Toggle dark mode">
                <label class="form-check-label small-muted" for="themeToggle">Dark</label>
              </div>
            </div>
          </div>

          <form id="loginForm" class="needs-validation" novalidate>
            <div class="mb-3">
              <label for="username" class="form-label">Username</label>
              <input type="text" class="form-control" id="username" value="admin" required aria-describedby="userHelp">
              <div class="invalid-feedback">Please enter your username.</div>
              <div id="userHelp" class="form-text help-text">Default: <code>admin</code></div>
            </div>

            <div class="mb-3 position-relative">
              <label for="password" class="form-label">Password</label>
              <div class="input-group">
                <input type="password" class="form-control" id="password" minlength="4" required aria-describedby="pwHelp">
                <button class="btn btn-outline-secondary" type="button" id="togglePassword" aria-label="Show password"><i class="bi bi-eye"></i></button>
                <div class="invalid-feedback">Please enter your password.</div>
              </div>
              <div id="pwHelp" class="form-text help-text">Password is case-sensitive.</div>
            </div>

            <div class="row align-items-center mb-3">
              <div class="col-auto">
                <div class="form-check">
                  <input class="form-check-input" type="checkbox" id="remember">
                  <label class="form-check-label" for="remember">Remember this browser</label>
                </div>
              </div>
              <div class="col text-end small-muted">
                <a href="#" class="link-primary">Forgot password?</a>
              </div>
            </div>

            <div class="d-grid mb-3">
              <button type="submit" class="btn btn-primary btn-lg">Sign in</button>
            </div>

 

       

            <div class="border-top pt-3 d-flex justify-content-between align-items-center">
              <div class="small-muted">Last login: <strong id="lastLogin">—</strong></div>
              <div class="small-muted">Build <strong>2025-12-05</strong></div>
            </div>
          </form>

          <footer class="mt-3 small-muted text-center">Need help? Visit <a href="#">support.acme.example</a></footer>
        </div>
      </div>
    </div>
  </main>

  <!-- Bootstrap JS bundle -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="" crossorigin="anonymous"></script>

  <script>
    // Simple form validation and interactions
    (function(){
      'use strict'

      // Form validation on submit
      const form = document.getElementById('loginForm');
      form.addEventListener('submit', function(e){
        if (!form.checkValidity()){
          e.preventDefault();
          e.stopPropagation();
        } else {
          e.preventDefault();
          // NOTE: replace below with real auth call
          const username = document.getElementById('username').value;
          const password = document.getElementById('password').value;
          // Mock response for demo
          if (username === 'admin' && password === 'admin'){
            showToast('Login successful — redirecting...');
            // simulate redirect
            setTimeout(()=>{ window.location.href = '/admin'; }, 800);
          } else {
            showToast('Invalid username or password', true);
          }
        }
        form.classList.add('was-validated');
      });

      // Password reveal
      const togglePw = document.getElementById('togglePassword');
      const pwField = document.getElementById('password');
      togglePw.addEventListener('click', function(){
        const type = pwField.getAttribute('type') === 'password' ? 'text' : 'password';
        pwField.setAttribute('type', type);
        this.querySelector('i').classList.toggle('bi-eye');
        this.querySelector('i').classList.toggle('bi-eye-slash');
      });

      // Theme toggle (light/dark) using data-bs-theme
      const themeToggle = document.getElementById('themeToggle');
      themeToggle.addEventListener('change', e=>{
        document.documentElement.setAttribute('data-bs-theme', e.target.checked ? 'dark' : 'light');
      });

      // Small toast utility
      function showToast(message, isError=false){
        // create ephemeral toast
        const toast = document.createElement('div');
        toast.className = 'position-fixed bottom-0 end-0 m-3 p-3 rounded shadow-sm';
        toast.style.zIndex = 1080;
        toast.innerHTML = `<div class="d-flex align-items-center gap-2 ${isError ? 'text-danger' : 'text-success'}">
          <i class="bi ${isError ? 'bi-x-circle-fill' : 'bi-check-circle-fill'} fs-4"></i>
          <div>${message}</div>
        </div>`;
        document.body.appendChild(toast);
        setTimeout(()=>{ toast.classList.add('opacity-0'); toast.style.transition='opacity .45s'; }, 2000);
        setTimeout(()=>{ toast.remove(); }, 2600);
      }

      // Display a mocked last login (for demo)
      document.getElementById('lastLogin').textContent = new Date().toLocaleString();

    })();
  </script>
</body>
</html>
