<?php
include_once __DIR__ . '/header.php';

$form_config = [
    'heading' => 'System Configuration',
    'form_action' => BASE_URL . '/settings/save',
    'inputs' => [
        'id' => ['type' => 'hidden', 'value' => ''],
        'f1' => ['label' => 'Serial Number', 'type' => 'text', 'class' => 'form-control', 'div_class' => 'col-lg-12 col-md-12 form-group'],
        'f2' => ['label' => 'Secret Key', 'type' => 'password', 'class' => 'form-control', 'div_class' => 'col-lg-12 col-md-12 form-group', 'toggle' => true],
        'f3' => ['label' => 'Phone Number', 'type' => 'text', 'class' => 'form-control', 'div_class' => 'col-lg-12 col-md-12 form-group'],
        'f4' => ['label' => 'owner', 'type' => 'text', 'class' => 'form-control', 'div_class' => 'col-lg-12 col-md-12 form-group'],
        'f5' => ['label' => 'Address', 'type' => 'textarea', 'class' => 'form-control summernote', 'div_class' => 'col-lg-12 col-md-12 form-group'],       
        'img1' => ['label' => 'favicon', 'type' => 'file', 'accept' => 'image/*', 'preview' => true, 'div_class' => 'col-lg-4 col-md-4 form-group'],
        'img2' => ['label' => 'header logo', 'type' => 'file', 'accept' => 'image/*', 'preview' => true, 'div_class' => 'col-lg-4 col-md-4 form-group'],
        'img3' => ['label' => 'footer logo', 'type' => 'file', 'accept' => 'image/*', 'preview' => true, 'div_class' => 'col-lg-4 col-md-4 form-group'],
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
                            <form action="<?= htmlspecialchars($form_config['form_action']) ?>" method="post"
                                enctype="multipart/form-data">
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

<script>
    document.querySelectorAll('input[type="file"]').forEach((input) => {
        input.addEventListener('change', () => {
            const preview = document.getElementById(`${input.id}-preview`);
            const file = input.files[0];
            if (!file) return;
            preview.src = URL.createObjectURL(file);
            preview.style.display = 'block';
        });
    });
</script>
</body>

</html>
