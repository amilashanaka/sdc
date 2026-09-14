

<style>

    /* Add this CSS to your main stylesheet or in a <style> tag */

.validation-message {
    display: none;
    width: 100%;
    margin-top: 0.25rem;
    font-size: 0.875em;
    color: #dc3545;
}

.is-invalid ~ .validation-message {
    display: block;
}

.is-valid {
    border-color: #28a745;
}

.is-invalid {
    border-color: #dc3545;
}

.form-control:focus.is-valid {
    border-color: #28a745;
    box-shadow: 0 0 0 0.2rem rgba(40, 167, 69, 0.25);
}

.form-control:focus.is-invalid {
    border-color: #dc3545;
    box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.25);
}

/* Animation for validation messages */
.validation-message {
    animation: slideDown 0.3s ease-out;
}

@keyframes slideDown {
    from {
        opacity: 0;
        transform: translateY(-10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}
</style>

<?php



// Fetch data based on configuration
$id_param = $form_config['data_config']['id_param'];
$data_source = $form_config['data_config']['data_source'];
$method_name = $form_config['data_config']['method_name'];

$id = isset($_GET[$id_param]) ? intval(base64_decode($_GET[$id_param])) : 0;
$row = ($id > 0 && isset($$data_source)) ? $$data_source->$method_name($id)['data'] : null;

include_once './navbar.php';
include_once './sidebar.php';
?>
<!-- Content Wrapper -->
<div class="content-wrapper">
    <!-- Page Header -->
    <?php
    $heading = $form_config['heading'];
    $page_config = $form_config['page_config'];
    $page_title = $id > 0 ?
        $page_config['update_title_prefix'] . " $heading" :
        $page_config['new_title_prefix'] . " $heading";
    include_once './page_header.php';
    ?>
    <!-- Main Content -->
    <section class="content">
        <div class="<?= htmlspecialchars($page_config['container_class']) ?>">
            <div class="row">
                <div class="<?= htmlspecialchars($form_config['layout']['main_column_class']) ?>">
                    <div class="<?= htmlspecialchars($page_config['card_class']) ?>">
                        <div class="<?= htmlspecialchars($page_config['card_body_class']) ?>">
                            <form action="<?= htmlspecialchars($form_config['form_action']) ?>"
                                method="<?= htmlspecialchars($form_config['method']) ?>"
                                enctype="<?= htmlspecialchars($form_config['enctype']) ?>">
                                <div class="<?= htmlspecialchars($form_config['layout']['form_row_class']) ?>">
                                    <?php renderFormElements($form_config, $row); ?>
                                </div>

                                <?= $form_config['layout']['separator'] ?>

                                <div class="<?= htmlspecialchars($form_config['layout']['button_row_class']) ?>">
                                    <?php if (isset($form_config['buttons']['submit'])): ?>
                                        <div class="<?= htmlspecialchars($form_config['buttons']['submit']['div_class']) ?>">
                                            <?php
                                            $submit_btn = $form_config['buttons']['submit'];
                                            $btn_text = $id > 0 ? $submit_btn['update_text'] : $submit_btn['create_text'];
                                            $btn_class = $id > 0 ? $submit_btn['update_class'] : $submit_btn['create_class'];
                                            ?>
                                            <button type="submit" class="<?= htmlspecialchars($btn_class) ?>">
                                                <?= htmlspecialchars($btn_text) ?>
                                            </button>
                                        </div>
                                    <?php endif; ?>

                                    <?php if (isset($form_config['buttons']['reset']) && $form_config['buttons']['reset']['show']): ?>
                                        <div class="<?= htmlspecialchars($form_config['buttons']['reset']['div_class']) ?>">
                                            <button type="reset" class="<?= htmlspecialchars($form_config['buttons']['reset']['class']) ?>">
                                                <?= htmlspecialchars($form_config['buttons']['reset']['text']) ?>
                                            </button>
                                        </div>
                                    <?php endif; ?>
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
    const formConfig = <?= json_encode($form_config); ?>;

    // Execute configured preview function
    <?php if (isset($form_config['scripts']['preview_function'])): ?>
        <?= $form_config['scripts']['preview_function'] ?>(formConfig);
    <?php endif; ?>

    // Execute additional scripts if configured
    <?php if (!empty($form_config['scripts']['additional_scripts'])): ?>
        <?php foreach ($form_config['scripts']['additional_scripts'] as $script): ?>
            <?= $script ?>
        <?php endforeach; ?>
    <?php endif; ?>

    // Add this script to your page.php or include it in footer.php

document.addEventListener('DOMContentLoaded', function() {
    const form = document.querySelector('form');
    
    if (!form) return;
    
    // Function to show validation message
    function showValidationMessage(input, isValid, message = '') {
        const validationDiv = input.parentElement.querySelector('.validation-message');
        
        if (validationDiv) {
            if (isValid) {
                input.classList.remove('is-invalid');
                input.classList.add('is-valid');
                validationDiv.style.display = 'none';
            } else {
                input.classList.remove('is-valid');
                input.classList.add('is-invalid');
                validationDiv.textContent = message || validationDiv.textContent;
                validationDiv.style.display = 'block';
            }
        }
    }
    
    // Function to validate individual field
    function validateField(input) {
        const value = input.value.trim();
        const isRequired = input.hasAttribute('required');
        const pattern = input.getAttribute('pattern');
        const type = input.getAttribute('type');
        
        // Check if required field is empty
        if (isRequired && !value) {
            showValidationMessage(input, false, 'This field is required.');
            return false;
        }
        
        // Skip validation if field is empty and not required
        if (!value && !isRequired) {
            input.classList.remove('is-invalid', 'is-valid');
            const validationDiv = input.parentElement.querySelector('.validation-message');
            if (validationDiv) validationDiv.style.display = 'none';
            return true;
        }
        
        // Pattern validation
        if (pattern && value) {
            const regex = new RegExp(pattern);
            if (!regex.test(value)) {
                const validationMessage = input.getAttribute('title') || 'Invalid format.';
                showValidationMessage(input, false, validationMessage);
                return false;
            }
        }
        
        // Email validation
        if (type === 'email' && value) {
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(value)) {
                showValidationMessage(input, false, 'Please enter a valid email address.');
                return false;
            }
        }
        
        // Number validation
        if (type === 'number' && value) {
            const min = input.getAttribute('min');
            const max = input.getAttribute('max');
            const numValue = parseFloat(value);
            
            if (isNaN(numValue)) {
                showValidationMessage(input, false, 'Please enter a valid number.');
                return false;
            }
            
            if (min && numValue < parseFloat(min)) {
                showValidationMessage(input, false, `Value must be at least ${min}.`);
                return false;
            }
            
            if (max && numValue > parseFloat(max)) {
                showValidationMessage(input, false, `Value must not exceed ${max}.`);
                return false;
            }
        }
        
        // URL validation
        if (type === 'url' && value) {
            try {
                new URL(value);
            } catch {
                showValidationMessage(input, false, 'Please enter a valid URL.');
                return false;
            }
        }
        
        // Tel validation
        if (type === 'tel' && value) {
            const telPattern = input.getAttribute('pattern') || '^[+]?[0-9\\s\\-\\(\\)]{10,}$';
            const telRegex = new RegExp(telPattern);
            if (!telRegex.test(value)) {
                showValidationMessage(input, false, 'Please enter a valid phone number.');
                return false;
            }
        }
        
        showValidationMessage(input, true);
        return true;
    }
    
    // Add real-time validation to all form inputs
    const inputs = form.querySelectorAll('input[type="text"], input[type="email"], input[type="password"], input[type="number"], input[type="tel"], input[type="url"], input[type="search"], textarea, select');
    
    inputs.forEach(input => {
        // Validate on blur (when user leaves the field)
        input.addEventListener('blur', function() {
            validateField(this);
        });
        
        // Validate on input for immediate feedback (optional)
        input.addEventListener('input', function() {
            // Only validate if field was previously invalid or has content
            if (this.classList.contains('is-invalid') || this.value.trim()) {
                validateField(this);
            }
        });
    });
    
    // Form submission validation
    form.addEventListener('submit', function(e) {
        let isFormValid = true;
        
        inputs.forEach(input => {
            if (!validateField(input)) {
                isFormValid = false;
            }
        });
        
        // Validate file inputs
        const fileInputs = form.querySelectorAll('input[type="file"]');
        fileInputs.forEach(fileInput => {
            if (fileInput.hasAttribute('required') && !fileInput.files.length) {
                showValidationMessage(fileInput, false, 'Please select a file.');
                isFormValid = false;
            }
        });
        
        if (!isFormValid) {
            e.preventDefault();
            
            // Focus on first invalid field
            const firstInvalid = form.querySelector('.is-invalid');
            if (firstInvalid) {
                firstInvalid.focus();
                firstInvalid.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
        }
    });
    
    // Handle Bootstrap switches if present
    const switches = form.querySelectorAll('input[data-bootstrap-switch]');
    switches.forEach(switchInput => {
        switchInput.addEventListener('switchChange.bootstrapSwitch', function(e, state) {
            // Custom validation for switches if needed
            validateField(this);
        });
    });
});
</script>
</body>

</html>