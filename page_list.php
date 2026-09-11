<?php
include_once './sidebar.php';
include_once './navbar.php';

// -------------------------------------------------------------------
// 1. Validate and fetch main model data
// -------------------------------------------------------------------
if (!isset($form_config['model'])) {
    die('Configuration error: model not defined.');
}
$model_name = $form_config['model'];
if (!isset($$model_name) || !is_object($$model_name)) {
    die("Model object \${$model_name} is not available.");
}
$model = $$model_name;

$method = $form_config['method'];
if (!method_exists($model, $method)) {
    die("Method {$method} does not exist in model {$model_name}.");
}
$result = $model->$method();
$list = ($result['error'] === null) ? $result['data'] : null;
$csv_import_config = csvImportConfigFromFormConfig($form_config);
$can_add_records = has_Permission($_SESSION['role'], $form_config['model'], 'add');


// -------------------------------------------------------------------
// 2. Build foreign key lookup data (store full related rows)
// -------------------------------------------------------------------
$fk_data = []; // format: $fk_data[$model_name][$id] = $full_row_array
if ($list) {
    foreach ($form_config['table']['columns'] as $column) {
        if (!empty($column['fk']) && $column['fk'] === true) {
            $fk_model_name = $column['model'];
            if (!isset($$fk_model_name) || !is_object($$fk_model_name)) {
                continue; // skip if related model not available
            }
            // Only fetch if we haven't already fetched this model
            if (!isset($fk_data[$fk_model_name])) {
                $fk_model = $$fk_model_name;
                $fk_result = $fk_model->get_all(); // adjust if method name differs
                if ($fk_result['error'] === null) {
                    $fk_data[$fk_model_name] = [];
                    foreach ($fk_result['data'] as $fk_row) {
                        $fk_data[$fk_model_name][$fk_row['id']] = $fk_row;
                    }
                } else {
                    $fk_data[$fk_model_name] = []; // empty to avoid repeated errors
                }
            }
        }
    }
}

// -------------------------------------------------------------------
// 3. Helper function to format a cell value from a related row
// -------------------------------------------------------------------
function formatFkCell($related_row, $column_config) {
    if (empty($related_row)) return '';

    // If display_template is set, replace {field} placeholders
    if (!empty($column_config['display_template'])) {
        $template = $column_config['display_template'];
        $replacements = [];
        foreach ($related_row as $key => $value) {
            $replacements['{' . $key . '}'] = $value ?? '';
        }
        return strtr($template, $replacements);
    }

    // Otherwise, use the 'show' field (fallback to empty string)
    $field = $column_config['show'] ?? null;
    return $field ? ($related_row[$field] ?? '') : '';
}
?>
<div class="<?= htmlspecialchars($form_config['layout']['content_wrapper_class']) ?>">
    <?php
    $heading = $form_config['heading'];
    $page_title = $form_config['title'];
    include_once './page_header.php';
 
    ?>

    <section class="<?= htmlspecialchars($form_config['layout']['section_class']) ?>">
        <div class="<?= htmlspecialchars($form_config['layout']['row_class']) ?>">
            <div class="<?= htmlspecialchars($form_config['layout']['col_class']) ?>">
                <div class="<?= htmlspecialchars($form_config['table']['card_classes']) ?>">

                    <?php if ($can_add_records && ((isset($form_config['buttons']['add_new']) && $form_config['new'] !== '') || !empty($csv_import_config['fields']))) : ?>
                    <div class="<?= htmlspecialchars($form_config['table']['card_header_classes']) ?>">
                        <h3 class="<?= htmlspecialchars($form_config['layout']['card_title_class'])  ?>">
                            <?php if (isset($form_config['buttons']['add_new']) && $form_config['new'] !== '') : ?>
                            <button type="button"
                                class="<?= htmlspecialchars($form_config['buttons']['add_new']['class']) ?>"
                                onclick="location.href='<?= htmlspecialchars($form_config['new']) ?>'">
                                <i class="<?= htmlspecialchars($form_config['buttons']['add_new']['icon']) ?>"></i>
                                <?= htmlspecialchars($form_config['buttons']['add_new']['text']) ?>
                            </button>
                            <?php endif; ?>
                            <?php if (!empty($csv_import_config['fields'])) : ?>
                            <button type="button"
                                class="<?= htmlspecialchars($form_config['buttons']['import_csv']['class'] ?? 'btn btn-app') ?>"
                                data-toggle="modal"
                                data-target="#csvImportModal">
                                <i class="<?= htmlspecialchars($form_config['buttons']['import_csv']['icon'] ?? 'fas fa-file-csv') ?>"></i>
                                <?= htmlspecialchars($form_config['buttons']['import_csv']['text'] ?? 'Import CSV') ?>
                            </button>
                            <?php endif; ?>
                        </h3>
                    </div>
                    <?php endif; ?>

                    <div class="<?= htmlspecialchars($form_config['table']['card_body_classes']) ?>">
                        <table id="<?= htmlspecialchars($form_config['table']['table_id']) ?>"
                               class="<?= htmlspecialchars($form_config['table']['table_classes']) ?>"
                               <?= $form_config['table']['table_attributes'] // already safe ?>>
                            <thead>
                                <tr>
                                    <?php foreach ($form_config['table']['th'] as $header): ?>
                                        <?php if ($header === 'Action'): ?>
                                            <th style="<?= htmlspecialchars($form_config['table']['action_style']) ?>">
                                                <?= htmlspecialchars($header) ?>
                                            </th>
                                        <?php else: ?>
                                            <th><?= htmlspecialchars($header) ?></th>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                </tr>
                            </thead>
                            <tfoot>
                                <tr>
                                    <?php foreach ($form_config['table']['th'] as $header): ?>
                                        <?php if ($header === 'Action'): ?>
                                            <th style="<?= htmlspecialchars($form_config['table']['action_style']) ?>">
                                                <?= htmlspecialchars($header) ?>
                                            </th>
                                        <?php else: ?>
                                            <th><?= htmlspecialchars($header) ?></th>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                </tr>
                            </tfoot>
                            <tbody>
                                <?php if ($list): ?>
                                    <?php $i = 1; ?>
                                    <?php foreach ($list as $row): ?>
                                        <tr>
                                            <?php foreach ($form_config['table']['columns'] as $column): ?>
                                                <td>
                                                    <?php
                                                    $cellContent = '';

                                                    // Determine value based on column type
                                                    if ($column['name'] === '#') {
                                                        $cellContent = $i++;
                                                    }
                                                    elseif (!empty($column['custom']) && $column['custom'] === true) {
                                                        // Custom rendering based on type
                                                        if (!empty($column['type']) && $column['type'] === 'status_badge') {
                                                            $status = $row['status'] ?? 1;
                                                            $cellContent = $status == 0 ? '<span class="badge badge-danger">Used</span>' : '<span class="badge badge-success">Not Used</span>';
                                                        } elseif (isset($column['callback'])) {
                                                            // Fallback for callback function
                                                            $cellContent = call_user_func($column['callback'], $row);
                                                        } else {
                                                            $cellContent = htmlspecialchars($row[$column['name']] ?? '');
                                                        }
                                                    }
                                                    elseif (!empty($column['fk']) && $column['fk'] === true) {
                                                        // Foreign key column
                                                        $fk_id = $row[$column['name']] ?? null;
                                                        $related_row = $fk_data[$column['model']][$fk_id] ?? null;
                                                        $cellContent = formatFkCell($related_row, $column);
                                                        $cellContent = htmlspecialchars($cellContent);
                                                    }
                                                    elseif (!empty($column['format']) && $column['format'] === 'date') {
                                                        // Date formatting
                                                        $timestamp = strtotime($row[$column['name']] ?? '');
                                                        $cellContent = $timestamp ? date('Y-m-d', $timestamp) : '';
                                                    }
                                                    elseif (!empty($column['display_type'])) {
                                                        // Custom display (e.g. prepend text)
                                                        $cellContent = $column['display_type'] . htmlspecialchars($row[$column['name']] ?? '');
                                                    }
                                                    else {
                                                        // Regular column
                                                        $cellContent = htmlspecialchars($row[$column['name']] ?? '');
                                                    }

                                                    // Wrap in link if required (skip for '#' because it's a counter)
                                                    if (!empty($column['link']) && $column['link'] === true && $column['name'] !== '#') {
                                                        $linkUrl = $form_config['table']['link_base']
                                                                 . '?id=' . base64_encode($row[$form_config['table']['id_column']]);
                                                        echo '<a href="' . htmlspecialchars($linkUrl) . '">' . $cellContent . '</a>';
                                                    } else {
                                                        echo $cellContent;
                                                    }
                                                    ?>
                                                </td>
                                            <?php endforeach; ?>

                                            <td style="<?= htmlspecialchars($form_config['table']['action_style']) ?>">
                                                <?php
                                                $statusColumn = $form_config['status']['column'];
                                                $rowId = $row[$form_config['table']['id_column']];
                                                $dbTable = $form_config['db_table'];
                                                $redirect = $form_config['redirect'];

                                                // Determine which button to use for active records based on form config
                                                $activeButtonKey = isset($form_config['buttons']['delete']) ? 'delete' : (isset($form_config['buttons']['deactivate']) ? 'deactivate' : null);

                                                if ($row[$statusColumn] == $form_config['status']['active']):
                                                    if (isset($form_config['buttons'][$activeButtonKey])):
                                                ?>
                                                    <button type="button"
                                                            class="<?= htmlspecialchars($form_config['buttons'][$activeButtonKey]['class']) ?>"
                                                            onclick="<?= htmlspecialchars($form_config['buttons'][$activeButtonKey]['function']) ?>('<?= $rowId ?>', '<?= $dbTable ?>', 'id', '<?= $redirect ?>');">
                                                        <i class="fa <?= htmlspecialchars($form_config['buttons'][$activeButtonKey]['icon']) ?>"></i>
                                                    </button>
                                                <?php
                                                    endif;
                                                else:
                                                    if (isset($form_config['buttons']['activate'])):
                                                ?>
                                                    <button type="button"
                                                            class="<?= htmlspecialchars($form_config['buttons']['activate']['class']) ?>"
                                                            onclick="<?= htmlspecialchars($form_config['buttons']['activate']['function']) ?>('<?= $rowId ?>', '<?= $dbTable ?>', 'id', '<?= $redirect ?>');">
                                                        <i class="fa <?= htmlspecialchars($form_config['buttons']['activate']['icon']) ?>"></i>
                                                    </button>
                                                <?php
                                                    endif;
                                                endif;
                                                ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<?php if ($can_add_records && !empty($csv_import_config['fields'])) : ?>
<div class="modal fade" id="csvImportModal" tabindex="-1" role="dialog" aria-labelledby="csvImportModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <form class="modal-content" action="data/import_csv.php" method="post" enctype="multipart/form-data">
            <div class="modal-header">
                <h5 class="modal-title" id="csvImportModalLabel">Import CSV</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                <input type="hidden" name="model" value="<?= htmlspecialchars($csv_import_config['model']) ?>">
                <input type="hidden" name="db_table" value="<?= htmlspecialchars($csv_import_config['db_table']) ?>">
                <input type="hidden" name="redirect" value="<?= htmlspecialchars($csv_import_config['redirect']) ?>">
                <input type="hidden" name="fields" value="<?= htmlspecialchars(json_encode($csv_import_config['fields']), ENT_QUOTES, 'UTF-8') ?>">

                <div class="form-group">
                    <label for="csv_file">CSV File</label>
                    <input type="file" class="form-control-file" id="csv_file" name="csv_file" accept=".csv,text/csv" required>
                </div>

                <div class="alert alert-info mb-0">
                    <strong>Accepted headers:</strong>
                    <?= htmlspecialchars(implode(', ', array_map(fn($field) => $field['label'] . ' or ' . $field['name'], $csv_import_config['fields']))) ?>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-upload"></i> Import
                </button>
            </div>
        </form>
    </div>
</div>
<?php endif; ?>

<?php include_once './footer.php'; ?>
</body>
</html>
