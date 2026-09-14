<?php
if ($form_config['inputs'] != null) {
    foreach ($form_config['inputs'] as $key => $input) {
        // Check if the input should be skipped
        if (isset($input['skip']) && $input['skip'] === true) {
            continue;
        }
?>


        <?php
        switch ($input['type']) {
            case 'text':
        ?>
                <div class="<?= isset($input['div_class']) ? $input['div_class'] : 'col-lg-12 col-md-12 form-group' ?>">

                    <?php if (isset($input['label'])) echo ' <label>' . $input['label'] . '</label>'; ?>
                    <input type="text" class="<?= isset($input['class']) ? $input['class'] : 'form-control' ?>" id="<?= $key ?>" name="<?= $key ?>" <?php if (isset($input['required'])) echo 'required'; ?> <?php if (isset($input['pattern'])) echo 'pattern="' . $input['pattern'] . '"'; ?> value="<?= isset($row[$key]) ? $row[$key] : (isset($input['value']) ? $input['value'] : '') ?>">
                </div>
            <?php
                break;

            case 'custom':

                if (isset($input['value']) && $input['value'] != '') {
                    echo $input['value'];
                }

                break;
            case 'h':
            ?>

                <input type="hidden" id="<?= $key ?>" name="<?= $key ?>" value="<?= isset($row[$key]) ? $row[$key] : (isset($input['value']) ? $input['value'] : '') ?>">

            <?php
                break;

            case 'number':
            ?>
                <div class="<?= isset($input['div_class']) ? $input['div_class'] : 'col-lg-12 col-md-12 form-group' ?>">

                    <?php if (isset($input['label'])) echo ' <label>' . $input['label'] . '</label>'; ?>
                    <input type="number" class="<?= isset($input['class']) ? $input['class'] : 'form-control' ?>" id="<?= $key ?>" name="<?= $key ?>" <?php if (isset($input['required'])) echo 'required'; ?> <?php if (isset($input['pattern'])) echo 'pattern="' . $input['pattern'] . '"'; ?> value="<?= isset($row[$key]) ? $row[$key] : (isset($input['value']) ? $input['value'] : '') ?>">
                </div>

            <?php
                break;

            case 'switch':
            ?>

                <div class="<?= isset($input['div_class']) ? $input['div_class'] : 'col-lg-12 col-md-12 form-group' ?>">
                    <input type="checkbox" id="<?= $key ?>" name="<?= $key ?>" <?php if (isset($input['checked']) && $input['checked'] === true) echo 'checked'; ?> data-bootstrap-switch data-off-color="<?= isset($input['color'][1]) ? $input['color'][1] : 'primary' ?>" data-on-color="<?= isset($input['color'][0]) ? $input['color'][0] : 'success' ?> " data-off-text="<?= isset($input['label'][1]) ? $input['label'][1] : 'OFF' ?>" data-on-text="<?= isset($input['label'][0]) ? $input['label'][0] : 'ON' ?>">

                </div>

            <?php
                break;

            case 'multy-select':


            ?>


                <div class="<?= isset($input['div_class']) ? $input['div_class'] : 'col-lg-12 col-md-12 form-group' ?>" >
                    <?php if (isset($input['label'])) echo ' <label>' . $input['label'] . '</label>'; ?>
                    <div class="<?= isset($input['selected-color']) ? 'select2-' . $input['selected-color'] : 'select2-info' ?>">
                        <select id="<?= $key ?>" name="<?= $key ?>[]" class="select2 modal-class" multiple="multiple" data-placeholder="<?= isset($input['placeholder']) ? $input['placeholder'] : '' ?>" data-dropdown-css-class="<?= isset($input['dropdown-color']) ? 'select2-' . $input['dropdown-color'] : 'select2-info' ?>" <?php if (isset($input['required'])) echo 'required'; ?> style="width: 100%;">
                            <?php foreach ($input['items'] as $item) { ?>
                                <option value="<?= $item['value'] ?>" <?php
                                                                        if (isset($row[$key]) && $row[$key] == $item['value']) {
                                                                            echo 'selected';
                                                                        } elseif (isset($input['value']) && $input['value'] == $item['value']) {
                                                                            echo 'selected';
                                                                        }
                                                                        ?>>
                                    <?= $item['label'] ?></option>
                            <?php } ?>
                        </select>
                    </div>
                </div>
            <?php
                break;

            case 'image':
            ?>

                <div class="<?= isset($input['div_class']) ? $input['div_class'] : 'col-lg-12 col-md-12 form-group' ?>">

                    <input type="file" class="<?= isset($input['class']) ? $input['class'] : '' ?>" id="<?= $key ?>" name="<?= $key ?>" accept="image/*" <?php if (isset($input['required'])) echo 'required'; ?>>
                </div>
            <?php
                break;

            case 'checkbox':
            ?>


                <!-- checkbox -->
                <div class="form-group">
                    <div class="<?= isset($input['div_class']) ? $input['div_class'] : 'icheck-primary d-inline' ?>">
                        <input type="checkbox" id="<?= $key ?>" name="<?= $key ?>" <?php if (isset($input['required'])) echo 'required'; ?> <?php if (isset($input['checked']) && $input['checked'] === true) echo 'checked'; ?>>
                        <label class="form-check-label" for="<?= $key ?>"><?= $input['label'] ?></label>
                    </div>

                </div>





            <?php
                break;

            case 'textarea':
            ?>
                <div class="<?= isset($input['div_class']) ? $input['div_class'] : 'col-lg-12 col-md-12 form-group' ?>">

                    <?php if (isset($input['label'])) echo ' <label>' . $input['label'] . '</label>'; ?>

                    <textarea class="<?= $input['class'] ?>" id="<?= $key ?>" name="<?= $key ?>" <?php if (isset($input['required'])) echo 'required'; ?> <?php if (isset($input['pattern'])) echo 'pattern="' . $input['pattern'] . '"'; ?> rows="<?= isset($input['rows']) ? $input['rows'] : '5' ?>"><?= isset($row[$key]) ? $row[$key] : (isset($input['value']) ? $input['value'] : '') ?></textarea>

                </div>
            <?php
                break;

            case 'datetime':
            ?>

                <div class="<?= isset($input['div_class']) ? $input['div_class'] : 'col-lg-12 col-md-12 form-group' ?>">

                    <?php if (isset($input['label'])) echo ' <label>' . $input['label'] . '</label>'; ?>
                    <input type="datetime-local" class="<?= $input['class'] ?>" id="<?= $key ?>" name="<?= $key ?>" <?php if (isset($input['required'])) echo 'required'; ?> <?php if (isset($input['pattern'])) echo 'pattern="' . $input['pattern'] . '"'; ?> value="<?= isset($row[$key]) ? $row[$key] : (isset($input['value']) ? $input['value'] : '') ?>">
                </div>

            <?php
                break;

            case 'combobox':
            ?>

                <div class="<?= isset($input['div_class']) ? $input['div_class'] : 'col-lg-12 col-md-12 form-group' ?>">


                    <?php if (isset($input['label'])) echo ' <label>' . $input['label'] . '</label>'; ?>

                    <select class="<?= $input['class'] ?>" id="<?= $key ?>" name="<?= $key ?>" <?php if (isset($input['required'])) echo 'required'; ?> style="width: 100%;">


                        <?php foreach ($input['items'] as $item) { ?>
                            <option value="<?= $item['value'] ?>" <?php
                                                                    if (isset($row[$key]) && $row[$key] == $item['value']) {
                                                                        echo 'selected';
                                                                    } elseif (isset($input['value']) && $input['value'] == $item['value']) {
                                                                        echo 'selected';
                                                                    }
                                                                    ?>>
                                <?= $item['label'] ?>
                            </option>
                        <?php } ?>
                    </select>
                </div>
        <?php
                break;

                // Add more cases as needed for other input types (radio, etc.)
        }
        ?>


<?php
    }
}

?>