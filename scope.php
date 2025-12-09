<?php
include_once './header.php';

$form_config = [
    'heading' => 'ADC Test ',
 
];

// Fetch work data if an ID is provided
$id = 1;
$row = ($id > 0 && isset($setting)) ? $setting->get_by_id($id)['data'] : null;



include_once './navbar.php';
include_once './sidebar.php';
?>
<!-- Content Wrapper -->
<div class="content-wrapper">
    <!-- Page Header -->
    <?php
    $heading = $form_config['heading'];
    $page_title = $id > 0 ? "Update $heading" : "New $heading";
    include_once './page_header.php';
    ?>
    <!-- Main Content -->
    <section class="content">
        <div class="container-fluid">
          <?php include_once './pynq/static/scope.php'; ?>
        </div>
    </section>
</div>
<?php include_once './footer.php'; ?>

<script>
    const formConfig = <?= json_encode($form_config); ?>;
</script>
</body>

</html>