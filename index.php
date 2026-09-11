<?php
include_once 'session.php';
include_once './inc/functions.php';
include_once './controllers/index.php';

if (isset($_SESSION['login'])) {
    header('Location: dashboard');
    exit();
}

$errorMessage = '';
if (isset($_SESSION['error'])) {
    $errorMessage = $_SESSION['error'];
    unset($_SESSION['error']);
}

$f3_setting = isset($setting) ? $setting->getSettings('f2') : null;
$pageTitle = htmlspecialchars($f3_setting ?? 'Admin Login', ENT_QUOTES, 'UTF-8');
$copyrightName = htmlspecialchars($f3_setting ?? 'Your Company', ENT_QUOTES, 'UTF-8');

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrfToken = $_SESSION['csrf_token'];

// Get current system mode
$modeFile = '/var/www/html/pynq/.mode';
$currentMode = 'RUN';
if (file_exists($modeFile)) {
    $currentMode = trim(file_get_contents($modeFile));
}
?>

<!doctype html>
<html lang="en" data-bs-theme="light">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= $pageTitle ?></title>
    <link rel="icon" href="<?= $setting->getSettings('img1') ?>" type="image/png">

    <!-- Bootstrap 5.3 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">

    <!-- Google Font -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="./assets/css/login.css">
</head>

<body>
    <main class="login-wrapper">
        <div class="card overflow-hidden">
            <div class="row g-0">

                <!-- LEFT SIDE INFO -->
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

                    <!-- System Mode Indicator -->
                    <div class="mt-3 p-2 rounded" style="background: rgba(0,0,0,0.2);">
                        <div class="small-muted mb-1">System Mode</div>
                        <div class="d-flex align-items-center gap-2">
                            <span class="led-pulse" style="width: 10px; height: 10px; background-color: <?= $currentMode === 'DEBUG' ? '#ff3b30' : '#39ff14' ?>; border-radius: 50%;"></span>
                            <strong class="fw-bold" style="font-size: 1.1rem; color: <?= $currentMode === 'DEBUG' ? '#ff6b6b' : '#39ff14' ?>"><?= $currentMode ?></strong>
                        </div>
                    </div>
                    
                    <?php if ($currentMode === 'RUN'): ?>
                    <button type="button" class="btn btn-router-action mt-3 w-100" id="debugSwitchBtn">
                        <span class="led-pulse"></span>Switch to Debug
                    </button>
                    <?php endif; ?>
                </div>

                <!-- RIGHT SIDE -->
                <div class="col-lg-7 p-4 p-lg-5">
                    <!-- RUN MODE PROMPT (shown when system is in RUN mode) -->
                    <div id="runModePrompt" class="d-flex flex-column justify-content-center align-items-center text-center" style="min-height: 400px;">
                        <div class="mb-4">
                            <i class="bi bi-pc-display" style="font-size: 4rem; color: #6c757d;"></i>
                        </div>
                        <h3 class="mb-2 fw-bold">System in RUN Mode</h3>
                        <p class="text-muted mb-4">The device is currently running in normal operational mode.<br>Switch to DEBUG mode to access the administrator dashboard.</p>
                        <button type="button" class="btn btn-router-action btn-lg" id="debugSwitchBtnRight">
                            <span class="led-pulse"></span>Switch to Debug Mode
                        </button>
                        <p class="small text-muted mt-4">or use the button on the left panel</p>
                    </div>

                    <!-- LOGIN FORM (shown when system is in DEBUG mode, or after clicking switch button in RUN mode) -->
                    <div id="loginForm" style="display: none;">
                        <div class="d-flex justify-content-between align-items-start mb-4">
                            <div>
                                <h3 class="mb-1 fw-bold">Admin Login</h3>
                                <div class="small-muted">Enter your administrator credentials to continue.</div>
                            </div>

                            <div class="text-end">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="themeToggle">
                                    <label class="form-check-label small-muted" for="themeToggle">Dark Mode</label>
                                </div>
                            </div>
                        </div>

                        <!-- ERROR MESSAGE -->
                        <?php if ($errorMessage): ?>
                            <div class="alert alert-danger text-center">
                                <?= htmlspecialchars($errorMessage, ENT_QUOTES, 'UTF-8') ?>
                            </div>
                        <?php endif; ?>

                        <!-- LOGIN FORM -->
                        <form method="POST" action="data/data_login.php" class="needs-validation" novalidate autocomplete="off" id="loginFormElement">

                            <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">

                            <div class="mb-3">
                                <label class="form-label fw-medium">Username</label>
                                <input type="text" class="form-control" placeholder="Username" name="a_username" required>
                                <div class="invalid-feedback">Please enter your username.</div>
                            </div>

                            <div class="mb-3 position-relative">
                                <label class="form-label fw-medium">Password</label>
                                <div class="input-group">
                                    <input type="password" id="password" placeholder="Password" name="a_password" class="form-control" required>
                                    <button class="btn btn-outline-secondary" type="button" id="togglePassword" aria-label="Toggle password visibility">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                </div>
                                <div class="invalid-feedback">Please enter your password.</div>
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

                            <div class="border-top pt-3 d-flex justify-content-between small-muted">
                                <div>Last login: <strong>—</strong></div>
                                <div>Build: <strong>2025-12-05</strong></div>
                            </div>
                        </form>

                        <footer class="mt-4 small-muted text-center">
                            Need help? Visit <a href="#">support.spicerconsulting.com</a>
                        </footer>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <!-- CLEANED JAVASCRIPT -->
    <script>
    document.addEventListener("DOMContentLoaded", () => {

        var currentMode = '<?= $currentMode ?>';
        var runModePrompt = document.getElementById('runModePrompt');
        var loginForm = document.getElementById('loginForm');

        // Show/hide sections based on current mode
        function updateView() {
            if (currentMode === 'RUN') {
                if (runModePrompt) runModePrompt.style.display = '';
                if (loginForm) loginForm.style.display = 'none';
            } else {
                if (runModePrompt) runModePrompt.style.display = 'none';
                if (loginForm) loginForm.style.display = '';
            }
        }

        updateView();

        /* PASSWORD TOGGLE */
        const togglePw = document.getElementById('togglePassword');
        const pwField = document.getElementById('password');

        if (togglePw && pwField) {
            togglePw.addEventListener('click', () => {
                pwField.type = pwField.type === 'password' ? 'text' : 'password';
                togglePw.querySelector("i").classList.toggle("bi-eye");
                togglePw.querySelector("i").classList.toggle("bi-eye-slash");
            });
        }

        /* THEME SWITCH */
        const themeToggle = document.getElementById('themeToggle');
        themeToggle?.addEventListener('change', e => {
            document.documentElement.setAttribute(
                'data-bs-theme',
                e.target.checked ? 'dark' : 'light'
            );
        });

        /* DEBUG MODE SWITCH BUTTON (right side) */
        const debugSwitchBtnRight = document.getElementById('debugSwitchBtnRight');
        if (debugSwitchBtnRight) {
            debugSwitchBtnRight.addEventListener('click', () => {
                // Hide RUN mode prompt and show login form
                if (runModePrompt) runModePrompt.style.display = 'none';
                if (loginForm) loginForm.style.display = '';
            });
        }

        /* DEBUG MODE SWITCH BUTTON (left side) */
        const debugSwitchBtnLeft = document.getElementById('debugSwitchBtn');
        if (debugSwitchBtnLeft) {
            debugSwitchBtnLeft.addEventListener('click', () => {
                // Hide RUN mode prompt and show login form
                if (runModePrompt) runModePrompt.style.display = 'none';
                if (loginForm) loginForm.style.display = '';
            });
        }
    });
    </script>

</body>
</html>
