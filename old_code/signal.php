<?php
include_once './header.php';


// Form Configuration - All configurable values centralized here
$form_config = [
    'heading' => 'Signals',
    'form_action' => 'data/register_signal.php',
    'form_method' => 'post',
    'form_enctype' => 'multipart/form-data',
    
    // Page Configuration
    'page_config' => ['container_class' => 'container-fluid','row_class' => 'row','col_class' => 'col-md-12', 'card_class' => 'card','card_body_class' => 'card-body'],
    
    // Button Configuration
    'buttons' => [
        'submit' => [
            'class_new' => 'btn btn-block btn-outline-secondary',
            'class_update' => 'btn btn-block btn-outline-success',
            'text_new' => 'Add New',
            'text_update' => 'Update Now',
            'div_class' => 'col-lg-2 col-md-2 form-group'
        ],
        'reset' => [
            'class' => 'btn btn-block btn-outline-warning',
            'text' => 'Reset',
            'div_class' => 'col-lg-2 col-md-2 form-group'
        ]
    ],
    
    // Form Structure
    'layout' => [
        'form_row_class' => 'row',
        'separator' => '<hr>',
        'button_row_class' => 'row'
    ],
    
    // Data Configuration
    'data_config' => [
        'id_param' => 'id',
        'id_encoding' => 'base64', // base64, none, etc.
        'data_source' => 'signal', // variable name for data source
        'get_method' => 'get_by_id' // method to fetch data
    ],
    
    // Script Configuration
    'scripts' => [
        'preview_function' => 'previewImage',
        'config_variable' => 'formConfig'
    ],
    
    // Form Inputs
    'inputs' => [
        'id' => [
            'type' => 'hidden', 
            'value' => ''
        ],
        'f1' => [
            'label' => 'Period', 
            'type' => 'text', 
            'class' => 'form-control', 
            'div_class' => 'col-lg-12 col-md-12 form-group',
            'required' => false,
            'placeholder' => ''
        ],
        'f2' => [
            'label' => 'Pips Gained', 
            'type' => 'text', 
            'class' => 'form-control', 
            'div_class' => 'col-lg-12 col-md-12 form-group',
            'required' => false,
            'placeholder' => ''
        ],
        'f3' => [
            'label' => 'Green Trades', 
            'type' => 'number', 
            'class' => 'form-control ', 
            'div_class' => 'col-lg-12 col-md-12 form-group',
            'required' => false,
            'rows' => 4
        ],
        'f4' => [
            'label' => 'Red Trades', 
            'type' => 'number', 
            'class' => 'form-control ', 
            'div_class' => 'col-lg-12 col-md-12 form-group',
            'required' => false,
            'rows' => 4
        ]
    ]
];

// Fetch data logic using configuration
$id_param = $form_config['data_config']['id_param'];
$id_encoding = $form_config['data_config']['id_encoding'];
$data_source = $form_config['data_config']['data_source'];
$get_method = $form_config['data_config']['get_method'];

 

$id = isset($_GET[$id_param]) ? 
    ($id_encoding === 'base64' ? intval(base64_decode($_GET[$id_param])) : intval($_GET[$id_param])) : 0;

$row = ($id > 0 && isset($$data_source)) ? $$data_source->$get_method($id)['data'] : null;



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
        <div class="<?= htmlspecialchars($form_config['page_config']['container_class']) ?>">
            <div class="<?= htmlspecialchars($form_config['page_config']['row_class']) ?>">
                <div class="<?= htmlspecialchars($form_config['page_config']['col_class']) ?>">
                    <div class="<?= htmlspecialchars($form_config['page_config']['card_class']) ?>">
                        <div class="<?= htmlspecialchars($form_config['page_config']['card_body_class']) ?>">
                            <form action="<?= htmlspecialchars($form_config['form_action']) ?>" 
                                  method="<?= htmlspecialchars($form_config['form_method']) ?>"
                                  enctype="<?= htmlspecialchars($form_config['form_enctype']) ?>">
                                
                                <div class="<?= htmlspecialchars($form_config['layout']['form_row_class']) ?>">
                                    <?php renderFormElements($form_config, $row); ?>
                                </div>

                                <?= $form_config['layout']['separator'] ?>
                                
                                <div class="<?= htmlspecialchars($form_config['layout']['button_row_class']) ?>">
                                    <div class="<?= htmlspecialchars($form_config['buttons']['submit']['div_class']) ?>">
                                        <button type="submit"
                                            class="<?= $id > 0 ? 
                                                htmlspecialchars($form_config['buttons']['submit']['class_update']) : 
                                                htmlspecialchars($form_config['buttons']['submit']['class_new']) ?>">
                                            <?= $id > 0 ? 
                                                htmlspecialchars($form_config['buttons']['submit']['text_update']) : 
                                                htmlspecialchars($form_config['buttons']['submit']['text_new']) ?>
                                        </button>
                                    </div>
                                    <div class="<?= htmlspecialchars($form_config['buttons']['reset']['div_class']) ?>">
                                        <button type="reset" class="<?= htmlspecialchars($form_config['buttons']['reset']['class']) ?>">
                                            <?= htmlspecialchars($form_config['buttons']['reset']['text']) ?>
                                        </button>
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
<?php include_once './footer.php'; ?>

<script>
    const <?= $form_config['scripts']['config_variable'] ?> = <?= json_encode($form_config); ?>;
    <?= $form_config['scripts']['preview_function'] ?>(<?= $form_config['scripts']['config_variable'] ?>);
</script>
</body>

</html>