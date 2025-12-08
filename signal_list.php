<?php
include_once './header.php';

$form_config = [
    'heading' => 'Signal List',
    'title' => 'list',
    'new' => 'signal',
    'table' => [
        'th' => ['#', 'Period', 'Pips Gained', 'Green Trades','Read Trades','Action'],
        'action_style' => 'width:3%; text-align: center;',
        'id_column' => 'id',
        'columns' => [
            ['name' => '#', 'link' => true],  // ID column (counter)
            ['name' => 'f1', 'link' => true], // Period column
            ['name' => 'f2', 'link' => false], // Pips Gained column
            ['name' => 'f3', 'link' => false], // Green Trades column
            ['name' => 'f4', 'link' => false]  // Red Trades column

        ],
        'link_base' => 'signal' // Base URL for links
    ],
    'db_table' => 'signals',
    'redirect' => 'signal_list',
    'buttons' => [
        'delete' => [
            'class' => 'btn-outline-danger',
            'icon' => 'fa-times'
        ],
        'activate' => [
            'class' => 'btn-outline-success',
            'icon' => 'fa-check'
        ]
    ],
    'permission' => [
        'add_new' => $_SESSION['role'] < 3
    ]
];

$list = $signal->get_all_with_delete()['error'] === null ? $signal->get_all_with_delete()['data'] : null;
?>

<?php include_once './navbar.php'; ?>
<?php include_once './sidebar.php'; ?>

<div class="content-wrapper">
    <?php
    $heading = $form_config['heading'];
    $page_title = $form_config['title'];
    include_once './page_header.php'; ?>

    <section class="content">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <?php if ($form_config['permission']['add_new']) { ?>
                        <div class="card-header">
                            <h3 class="card-title">
                                <button type="button" class="btn btn-app" onclick="location.href ='<?= $form_config['new'] ?>'">
                                    <i class="fas fa-file"></i>Add New
                                </button>
                            </h3>
                        </div>
                    <?php } ?>

                    <div class="card-body">
                        <table id="example23" class="display nowrap table table-hover table-striped table-bordered" cellspacing="0" width="100%">
                            <thead>
                                <tr>
                                    <?php foreach ($form_config['table']['th'] as $header) {
                                        echo $header === 'Action' ? '<th style="'.$form_config['table']['action_style'].'">' . $header . '</th>' : '<th>' . $header . '</th>';
                                    } ?>
                                </tr>
                            </thead>
                            <tfoot>
                                <tr>
                                    <?php foreach ($form_config['table']['th'] as $header) {
                                        echo $header === 'Action' ? '<th style="'.$form_config['table']['action_style'].'">' . $header . '</th>' : '<th>' . $header . '</th>';
                                    } ?>
                                </tr>
                            </tfoot>
                            <tbody>
                                <?php if ($list) {
                                    $i = 1;
                                    foreach ($list as $row) { ?>
                                        <tr>
                                            <?php foreach ($form_config['table']['columns'] as $column) { ?>
                                                <td>
                                                    <?php if ($column['link']) { ?>
                                                        <a href="<?= $form_config['table']['link_base'] ?>?id=<?= base64_encode($row[$form_config['table']['id_column']]); ?>">
                                                    <?php } ?>
                                                    
                                                    <?= ($column['name'] === '#') ? $i++ : $row[$column['name']] ?>
                                                    
                                                    <?php if ($column['link']) { ?>
                                                        </a>
                                                    <?php } ?>
                                                </td>
                                            <?php } ?>
                                            <td>
                                                <?php if ($row['status'] == '1') { ?>
                                                    <button type="button" class="btn btn-block <?= $form_config['buttons']['delete']['class'] ?> btn-flat" 
                                                        onclick="delete_record('<?= $row[$form_config['table']['id_column']]; ?>', '<?= $form_config['db_table']; ?>', 'id', '<?= $form_config['redirect']; ?>');">
                                                        <i class="fa <?= $form_config['buttons']['delete']['icon'] ?>"></i>
                                                    </button>
                                                <?php } else { ?>
                                                    <button type="button" class="btn btn-block <?= $form_config['buttons']['activate']['class'] ?> btn-flat" 
                                                        onclick="activate_record('<?= $row[$form_config['table']['id_column']]; ?>', '<?= $form_config['db_table']; ?>', 'id', '<?= $form_config['redirect']; ?>');">
                                                        <i class="fa <?= $form_config['buttons']['activate']['icon'] ?>"></i>
                                                    </button>
                                                <?php } ?>
                                            </td>
                                        </tr>
                                <?php }
                                } ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<?php include_once './footer.php'; ?>
</body>
</html>