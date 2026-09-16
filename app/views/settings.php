<?php
include_once __DIR__ . '/header.php';
$device_types = new DeviceType();

$form_config = [
    'heading' => 'System Configuration',
    'form_action' => BASE_URL . '/settings/save',
    'inputs' => [
        'id' => ['type' => 'hidden', 'value' => ''],
        'f1' => ['label' => 'Host Name', 'type' => 'text', 'class' => 'form-control', 'div_class' => 'col-lg-12 col-md-12 form-group'],
        'f2' => ['label' => 'Secret Key', 'type' => 'password', 'class' => 'form-control', 'div_class' => 'col-lg-12 col-md-12 form-group', 'toggle' => true, 'copy' => true, 'disabled'=>true],
        'f3' => ['label' => 'Device Office', 'type' => 'text', 'class' => 'form-control', 'div_class' => 'col-lg-12 col-md-12 form-group'],
        'f4' => ['label' => 'Device Local IP', 'type' => 'text', 'class' => 'form-control', 'div_class' => 'col-lg-12 col-md-12 form-group'],
        'f5' => ['label' => 'Device Firmware Version', 'type' => 'text', 'class' => 'form-control', 'div_class' => 'col-lg-12 col-md-12 form-group'],
        'f6' => ['label' => 'Device Serial Number', 'type' => 'text', 'class' => 'form-control', 'div_class' => 'col-lg-12 col-md-12 form-group'],
        'device_type' => ['label' => 'Device Type', 'type' => 'select', 'class' => 'form-control', 'div_class' => 'col-lg-12 col-md-12 form-group', 'items' => $device_types->get_items()],
    ],
];

$id = 1;
/** @var Setting $setting */
$setting = $setting ?? new Setting($id);

include_once __DIR__ . '/navbar.php';
include_once __DIR__ . '/sidebar.php';
?>
<!-- Content Wrapper -->
<div class="content-wrapper">
    <!-- Page Header -->
    <?php
    $heading = $form_config['heading'];
    $page_title = $id > 0 ? "Update $heading" : "New $heading";
    include_once __DIR__ . '/page_header.php';
    ?>
    <!-- Main Content -->
    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-body">
                            <form action="<?= htmlspecialchars($form_config['form_action']) ?>" method="post">
                                <div class="row">
                                    <?php $setting->renderFormElements($form_config); ?>
                                </div>

                                <hr>
                                <div class="row">
                                    <div class="col-lg-2 col-md-2 form-group">
                                        <button type="submit"
                                            class="btn btn-block btn-outline-<?= $id > 0 ? 'success' : 'secondary' ?>">
                                            <?= $id > 0 ? 'Update Now' : 'Add New' ?>
                                        </button>
                                    </div>
                                    <div class="col-lg-2 col-md-2 form-group">
                                        <button type="reset" class="btn btn-block btn-outline-warning">Reset</button>
                                    </div>
                                </div>
                            </form>
                        </div><!-- /.card-body -->
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
<?php include_once __DIR__ . '/footer.php'; ?>

</body>

</html>