<?php include_once './header.php';
?>

<?php include_once './navbar.php'; ?>

<?php include_once './sidebar.php'; ?>
<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <?php

    $heading = "Home";
    $page_title = "Dash Board";


    include_once './page_header.php'; ?>
    <!-- /.content-header -->

    <!-- Main content -->
    <section class="content">
        <div class="container-fluid">
            <?php include_once './infobox.php'; ?>

            <?php
            $modeFile = '/var/www/html/pynq/.mode';
            $systemMode = 'RUN';
            if (file_exists($modeFile)) {
                $systemMode = trim(file_get_contents($modeFile));
            }
            $targetMode = ($systemMode === 'DEBUG') ? 'RUN' : 'DEBUG';
            $buttonText = ($systemMode === 'DEBUG') ? 'Switch to RUN mode' : 'Switch to DEBUG mode';
            $badgeClass = ($systemMode === 'DEBUG') ? 'badge badge-danger' : 'badge badge-success';
            ?>
            <div class="row">
                <div class="col-lg-6">
                    <div class="card card-outline card-primary">
                        <div class="card-header">
                            <h3 class="card-title">System Mode</h3>
                        </div>
                        <div class="card-body">
                            <div class="d-flex align-items-center justify-content-between mb-3">
                                <div>
                                    <h5>Current mode</h5>
                                    <span class="<?= $badgeClass ?>" style="font-size: 1rem; padding: 0.5rem 0.75rem;"><?php echo htmlspecialchars($systemMode); ?></span>
                                </div>
                                <div>
                                    <button id="mode-switch-btn" class="btn btn-primary" data-target-mode="<?= $targetMode ?>"><?= $buttonText ?></button>
                                </div>
                            </div>
                            <p>The web interface remains available at all times. Only one backend mode runs at once: either <strong>RUN</strong> (startup TCP mode) or <strong>DEBUG</strong> (server.py WebSocket mode).</p>
                            <p class="text-muted">After the switch is confirmed, the system will restart the active backend and continue on the selected mode.</p>
                        </div>
                    </div>
                </div>
            </div>
            <script>
                document.addEventListener('DOMContentLoaded', function () {
                    var button = document.getElementById('mode-switch-btn');
                    if (!button) {
                        return;
                    }
                    button.addEventListener('click', function () {
                        var targetMode = button.getAttribute('data-target-mode');
                        button.disabled = true;
                        button.textContent = 'Switching...';

                        fetch('data/mode_action.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/x-www-form-urlencoded'
                            },
                            body: 'mode=' + encodeURIComponent(targetMode)
                        })
                        .then(function (response) {
                            return response.json();
                        })
                        .then(function (data) {
                            if (data.success) {
                                alert(data.message);
                                window.location.reload();
                            } else {
                                alert('Error: ' + (data.error || 'Unable to switch mode'));
                                button.disabled = false;
                                button.textContent = 'Switch to ' + targetMode + ' mode';
                            }
                        })
                        .catch(function () {
                            alert('Mode switch request failed.');
                            button.disabled = false;
                            button.textContent = 'Switch to ' + targetMode + ' mode';
                        });
                    });
                });
            </script>

            <?php // include_once './dashboard_report.php'; ?>
            <!-- /.row -->

            <!-- /.row -->
        </div>
        <!--/. container-fluid -->
    </section>
    <!-- /.content -->
</div>
<?php include_once './footer.php'; ?>

</body>
</html>