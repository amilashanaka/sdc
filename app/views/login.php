<!DOCTYPE html>
<html lang="en" data-bs-theme="light">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin Login</title>

    <!-- Bootstrap 5.3 (jsDelivr CDN) -->
    <link href="./assets/css/bootstrap.min.css" rel="stylesheet" >
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <!-- Google Fonts: Inter -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

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
                            <div id="pwHelp" class="form-text help-text">Password is case-sensitive. </div>
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
                toast.addEventListener('hidden.bs.toast', function() {
                    toast.remove();
                });
            }

        })();
    </script>
</body>

</html>