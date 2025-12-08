<?php
// Dynamic data fetching based on configuration
$model = ${$form_config['model']};
$method = $form_config['method'];
$result = $model->$method();
$list = $result['error'] === null ? $result['data'] : null;

// Prepare foreign key data if needed
$fk_data = [];
if ($list) {
    foreach ($form_config['table']['columns'] as $column) {
        if (isset($column['fk']) && $column['fk'] === true) {
            $fk_model_name = $column['model'];
            $fk_model = ${$fk_model_name};
            $fk_result = $fk_model->get_all_with_delete();
            if ($fk_result['error'] === null) {
                $fk_data[$column['model']] = [];
                foreach ($fk_result['data'] as $fk_row) {
                    $fk_data[$column['model']][$fk_row['id']] = $fk_row[$column['show']];
                }
            }
        }
    }
}
?>

<?php include_once './navbar.php'; ?>
<?php include_once './sidebar.php'; ?>

<div class="<?= $form_config['layout']['content_wrapper_class'] ?>">
    <?php
    $heading = $form_config['heading'];
    $page_title = $form_config['title'];
    include_once './page_header.php'; ?>

    <section class="<?= $form_config['layout']['section_class'] ?>">
        <div class="<?= $form_config['layout']['row_class'] ?>">
            <div class="<?= $form_config['layout']['col_class'] ?>">
                <div class="<?= $form_config['table']['card_classes'] ?>">
                    <?php if ($form_config['permission']['add_new']) { ?>
                        <div class="<?= $form_config['table']['card_header_classes'] ?>">
                            <h3 class="<?= $form_config['layout']['card_title_class'] ?>">
                                <button type="button" class="<?= $form_config['buttons']['add_new']['class'] ?>" 
                                        onclick="location.href ='<?= $form_config['new'] ?>'">
                                    <i class="<?= $form_config['buttons']['add_new']['icon'] ?>"></i>
                                    <?= $form_config['buttons']['add_new']['text'] ?>
                                </button>
                            </h3>
                        </div>
                    <?php } ?>

                    <div class="<?= $form_config['table']['card_body_classes'] ?>">
                        <table id="<?= $form_config['table']['table_id'] ?>" 
                               class="<?= $form_config['table']['table_classes'] ?>" 
                               <?= $form_config['table']['table_attributes'] ?>>
                            <thead>
                                <tr>
                                    <?php foreach ($form_config['table']['th'] as $header) {
                                        echo $header === 'Action' ? 
                                            '<th style="'.$form_config['table']['action_style'].'">' . $header . '</th>' : 
                                            '<th>' . $header . '</th>';
                                    } ?>
                                </tr>
                            </thead>
                            <tfoot>
                                <tr>
                                    <?php foreach ($form_config['table']['th'] as $header) {
                                        echo $header === 'Action' ? 
                                            '<th style="'.$form_config['table']['action_style'].'">' . $header . '</th>' : 
                                            '<th>' . $header . '</th>';
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
                                                    
                                                    <?php
                                                    if ($column['name'] === '#') {
                                                        echo $i++;
                                                    } elseif (isset($column['fk']) && $column['fk'] === true) {
                                                        // Handle foreign key display
                                                        $fk_id = $row[$column['name']];
                                                        echo isset($fk_data[$column['model']][$fk_id]) ? 
                                                             $fk_data[$column['model']][$fk_id] : 
                                                             'N/A';
                                                    } else {
                                                        echo $row[$column['name']];
                                                    }
                                                    ?>
                                                    
                                                    <?php if ($column['link']) { ?>
                                                        </a>
                                                    <?php } ?>
                                                </td>
                                            <?php } ?>
                                            <td>
                                                <?php if ($row[$form_config['status']['column']] == $form_config['status']['active']) { ?>
                                                    <button type="button" class="<?= $form_config['buttons']['delete']['class'] ?>" 
                                                        onclick="<?= $form_config['buttons']['delete']['function'] ?>('<?= $row[$form_config['table']['id_column']]; ?>', '<?= $form_config['db_table']; ?>', 'id', '<?= $form_config['redirect']; ?>');">
                                                        <i class="fa <?= $form_config['buttons']['delete']['icon'] ?>"></i>
                                                    </button>
                                                <?php } else { ?>
                                                    <button type="button" class="<?= $form_config['buttons']['activate']['class'] ?>" 
                                                        onclick="<?= $form_config['buttons']['activate']['function'] ?>('<?= $row[$form_config['table']['id_column']]; ?>', '<?= $form_config['db_table']; ?>', 'id', '<?= $form_config['redirect']; ?>');">
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