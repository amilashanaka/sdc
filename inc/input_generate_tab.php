<?php
if ($tab_config['tabs'] != null) {
    foreach ($tab_config['tabs'] as $key_tab => $form_config) {
        // Check if the input should be skipped
       
?>
        <div class="active tab-pane" id="<?= $key_tab ?>">

        <?php include_once './input_generate.php'; ?>
        </div>

<?php
    }
}

?>