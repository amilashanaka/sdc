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