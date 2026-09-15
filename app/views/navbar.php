
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
      
      <button class="navbar-item" onclick="toggleFullscreen()">
        <i class="fas fa-expand-arrows-alt"></i>
      </button>
      <div class="navbar-profile" onclick="showProfile()" data-user-id="<?= $_SESSION['user_id'] ?? 0 ?>">
        <i class="fas fa-user-circle navbar-profile-icon" aria-hidden="true"></i>
        <span class="d-none d-md-inline">Admin</span>
      </div>
    </div>
  </nav>

<!-- Flash Messages with SweetAlert2 fallback -->
<?php if (isset($_SESSION['flash']) && !empty($_SESSION['flash'])): ?>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        <?php foreach ($_SESSION['flash'] as $type => $message): ?>
            <?php
            // Map PHP flash types to SweetAlert icons & colors
            $swalIcon = match($type) {
                'success' => 'success',
                'error', 'danger' => 'error',
                'warning' => 'warning',
                'info' => 'info',
                default => 'info'
            };
            $swalTitle = match($type) {
                'success' => 'Success!',
                'error', 'danger' => 'Error!',
                'warning' => 'Warning!',
                'info' => 'Information',
                default => 'Notification'
            };
            ?>

            Swal.fire({
                icon: '<?= $swalIcon ?>',
                title: '<?= $swalTitle ?>',
                text: <?= json_encode($message) ?>,
                timer: 3500,
                timerProgressBar: true,
                showConfirmButton: false,
                toast: true,
                position: 'top-end',
                customClass: {
                    popup: 'colored-toast'
                }
            });
        <?php endforeach; ?>

        // Optional: also keep classic alert as fallback
        <?php if (empty($_SESSION['flash']['success'])): // only show classic if no success ?>
            // ... your previous alert code ...
        <?php endif; ?>
    });
    </script>

    <?php unset($_SESSION['flash']); ?>
<?php endif; ?>