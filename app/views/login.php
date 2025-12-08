<!doctype html>
<html lang="en" data-bs-theme="light">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Spicer Consulting Device Login</title>

    <!-- Bootstrap 5.3 (jsDelivr CDN) -->
    <link href="./assets/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <!-- Google Fonts: Inter -->
    <link href="./assets/fonts/css2.css" rel="stylesheet">

    <link rel="stylesheet" href="./assets/css/login.css">
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
                            <h5 class="mb-0 fw-semibold">Spicer Consulting</h5>
                            <div class="device-meta">Serial Number: SC 24123 • Firmware: 1.2.0</div>
                        </div>
                    </div>

                    <h4 class="mt-4 fw-bold">Administrator Access</h4>
                    <p class="help-text mb-4">Manage device settings and user accounts securely.</p>

                    <ul class="list-unstyled small-muted">
                        <li class="mb-3"><i class="bi bi-shield-lock-fill me-2"></i>Supports 16 ADC Channels</li>
                        <li class="mb-3"><i class="bi bi-graph-up me-2"></i>Debug All Attached Modules</li>
                        <li class="mb-3"><i class="bi bi-wifi me-2"></i>Monitor Device Health</li>
                    </ul>

                    <div class="mt-auto small-muted">IP: <strong>192.168.0.1</strong> • Provisioned on: <strong>2025-12-05</strong></div>
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
                        <div class="alert error"><?php echo htmlspecialchars($error); ?></div>
                    <?php endif; ?>


                    <form id="loginForm" method="POST" action="" class="needs-validation" novalidate>
                        <div class="mb-3">
                            <label for="username" class="form-label fw-medium">Username</label>
                            <input type="text" class="form-control" id="username" value="admin" required aria-describedby="userHelp">
                            <div class="invalid-feedback">Please enter your username.</div>
                            <div id="userHelp" class="form-text help-text">Default: <code>admin</code></div>
                        </div>

                        <div class="mb-3 position-relative">
                            <label for="password" class="form-label fw-medium">Password</label>
                            <div class="input-group">
                                <input type="password" class="form-control" id="password" minlength="4" required aria-describedby="pwHelp">
                                <button class="btn btn-outline-secondary" type="button" id="togglePassword" aria-label="Toggle password visibility"><i class="bi bi-eye"></i></button>
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
                            <button type="submit" class="btn btn-primary btn-lg">Sign In</button>
                        </div>

                        <div class="border-top pt-3 d-flex justify-content-between align-items-center small-muted">
                            <div>Last login: <strong id="lastLogin">—</strong></div>
                            <div>Build: <strong>2025-12-05</strong></div>
                        </div>
                    </form>

                    <footer class="mt-4 small-muted text-center">Need help? Visit <a href="#">support.spicerconsulting.com</a></footer>
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
                    setTimeout(() => {
                        window.location.href = '/admin';
                    }, 800);
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
                this.querySelector('i').classList.toggle('bi-eye');
                this.querySelector('i').classList.toggle('bi-eye-slash');
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