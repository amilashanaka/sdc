<?php
include_once 'header.php';
include_once 'sidebar.php';
include_once 'navbar.php';

// Initialize User model
$id = isset($_GET['id']) ? intval(base64_decode($_GET['id'])) : 0;
$user = new User($id);

$form_config = [
    'heading' => 'User',
    'form_action' => BASE_URL . '/user/save',
    'method' => 'post',

    'tabs' => [
        'tab-info' => [
            'label' => 'User Information',
            'active' => true,
            'show_only_on_edit' => false,
            'type' => 'form',
            'inputs' => [
                'id' => ['type' => 'hidden', 'value' => $id, 'div_class' => 'd-none'],
                'f1' => [
                    'label' => 'Username',
                    'type' => 'text',
                    'class' => 'form-control',
                    'div_class' => 'form-group col-md-6',
                    'required' => true,
                    'disable_on_edit' => true,
                    'minlength' => 3,
                    'maxlength' => 50,
                    'pattern' => '^[a-zA-Z0-9_]{3,50}$',
                    'validation_message' => 'Username must be 3-50 characters (letters, numbers, underscore only)',
                    'validation' => [
                        'type' => 'pattern',
                        'rules' => [
                            'minlength' => 3,
                            'maxlength' => 50,
                            'pattern' => '^[a-zA-Z0-9_]{3,50}$'
                        ]
                    ]
                ],
       
            ],
        ],

        'tab-password' => [
            'label' => 'Change Password',
            'active' => false,
            'show_only_on_edit' => true,
            'type' => 'form',
            'info_message' => 'Password must contain at least 8 characters with uppercase, lowercase, and numbers.',
            'inputs' => [
                'f2' => [
                    'label' => 'New Password',
                    'type' => 'password',
                    'class' => 'form-control',
                    'div_class' => 'form-group col-md-6',
                    'placeholder' => 'Leave blank to keep current',
                    'minlength' => 8,
                    'maxlength' => 100,
                    'pattern' => '^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).{8,}$',
                    'validation_message' => 'Password must be 8+ characters with uppercase, lowercase, and number',
                    'validation' => [
                        'type' => 'password',
                        'rules' => [
                            'minlength' => 8,
                            'maxlength' => 100,
                            'pattern' => '^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).{8,}$',
                            'hasUpperCase' => true,
                            'hasLowerCase' => true,
                            'hasNumber' => true
                        ],
                        'realtime' => true
                    ]
                ],
                'f2_confirm' => [
                    'label' => 'Confirm Password',
                    'type' => 'password',
                    'class' => 'form-control',
                    'div_class' => 'form-group col-md-6',
                    'placeholder' => 'Re-type new password',
                    'validation_message' => 'Passwords must match',
                    'validation' => [
                        'type' => 'password_confirm',
                        'match_field' => 'f3',
                        'realtime' => true
                    ]
                ],
            ],
        ],
    ],

    'buttons' => [
        'submit' => [
            'div_class' => 'col-md-3',
            'create_text' => 'Add User',
            'create_class' => 'btn btn-block btn-danger',
            'create_icon' => 'fas fa-save',
            'update_text' => 'Update User',
            'update_class' => 'btn btn-block btn-success',
            'update_icon' => 'fas fa-save',
            'loading_text' => 'Saving...',
            'loading_icon' => 'fas fa-spinner fa-spin',
            'password_tab_text' => 'Update Password'
        ],
        'reset' => [
            'show' => true,
            'div_class' => 'col-md-3',
            'text' => 'Clear',
            'class' => 'btn btn-block btn-warning',
            'icon' => 'fas fa-undo'
        ]
    ],

    'hidden_fields' => [
        'updated_by' => ['type' => 'hidden', 'value' => $_SESSION['login_id'] ?? ''],
        'created_by' => ['type' => 'hidden', 'value' => $_SESSION['login_id'] ?? ''],
    ],

    'validation' => [
        'enabled' => true,
        'scroll_to_error' => true,
        'scroll_behavior' => 'smooth',
        'scroll_block' => 'center',
        'show_error_summary' => true,
        'bootstrap_validation_classes' => true, // Use Bootstrap's is-valid/is-invalid classes
    ],

    'ui' => [
        'card_classes' => 'card card-primary card-outline',
        'main_card_classes' => 'card',
        'alert_classes' => 'alert alert-{type} alert-dismissible fade show',
        'tab_nav_classes' => 'nav nav-pills',
    ]
];
?>
  <link rel="stylesheet" href="./assets/css/user.css">
<div class="content-wrapper" id="contentWrapper">
    <div class="container-fluid">

        <!-- Flash Messages -->
        <?php if (isset($_SESSION['flash'])): ?>
            <?php foreach ($_SESSION['flash'] as $type => $message): ?>
                <div class="<?= str_replace('{type}', htmlspecialchars($type), $form_config['ui']['alert_classes']) ?>" role="alert">
                    <?= htmlspecialchars($message) ?>
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <?php unset($_SESSION['flash'][$type]); ?>
            <?php endforeach; ?>
        <?php endif; ?>

        <div class="row mb-4">
            <div class="col-12">
                <h3><?= $id > 0 ? 'Update User' : 'Add New User' ?></h3>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12">
                <div class="<?= $form_config['ui']['main_card_classes'] ?>">
                    <!-- Tab Navigation -->
                    <div class="card-header p-2">
                        <ul class="<?= $form_config['ui']['tab_nav_classes'] ?>" id="userTab" role="tablist">
                            <?php foreach ($form_config['tabs'] as $tab_key => $tab):
                                if (($tab['show_only_on_edit'] ?? false) && $id <= 0) continue; ?>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link <?= $tab['active'] ? 'active' : '' ?>"
                                            id="<?= $tab_key ?>-tab"
                                            data-bs-toggle="tab"
                                            data-bs-target="#<?= $tab_key ?>"
                                            type="button"
                                            role="tab"
                                            aria-controls="<?= $tab_key ?>"
                                            aria-selected="<?= $tab['active'] ? 'true' : 'false' ?>">
                                        <?= htmlspecialchars($tab['label']) ?>
                                    </button>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>

                    <!-- Tab Content -->
                    <div class="card-body">
                        <form id="userForm" action="<?= $form_config['form_action'] ?>" 
                              method="<?= $form_config['method'] ?>" 
                              novalidate>
                              
                            <!-- Hidden Fields -->
                            <?php foreach ($form_config['hidden_fields'] as $name => $cfg): ?>
                                <input type="hidden" name="<?= $name ?>" value="<?= htmlspecialchars($cfg['value']) ?>">
                            <?php endforeach; ?>
                            <?php if ($id > 0): ?>
                                <input type="hidden" name="id" value="<?= $id ?>">
                            <?php endif; ?>

                            <div class="tab-content" id="userTabContent">
                                <?php foreach ($form_config['tabs'] as $tab_key => $tab):
                                    if (($tab['show_only_on_edit'] ?? false) && $id <= 0) continue; ?>
                                    <div class="tab-pane fade <?= $tab['active'] ? 'show active' : '' ?>"
                                         id="<?= $tab_key ?>"
                                         role="tabpanel"
                                         aria-labelledby="<?= $tab_key ?>-tab">

                                        <!-- Render Form Fields -->
                                        <div class="row">
                                            <?php
                                            $inputs = $tab['inputs'];
                                            if ($tab_key === 'tab-info' && $id > 0) {
                                                foreach ($inputs as &$field) {
                                                    if ($field['disable_on_edit'] ?? false) {
                                                        $field['disabled'] = true;
                                                        $field['readonly'] = true;
                                                    }
                                                }
                                            }
                                            $user->renderFormElements(['inputs' => $inputs]);
                                            ?>
                                        </div>

                                        <?php if (isset($tab['info_message'])): ?>
                                            <div class="row mt-3">
                                                <div class="col-12">
                                                    <small class="text-muted">
                                                        <i class="fas fa-info-circle"></i>
                                                        <?= htmlspecialchars($tab['info_message']) ?>
                                                    </small>
                                                </div>
                                            </div>
                                        <?php endif; ?>

                                        <hr class="my-4">
                                        <div class="row mt-4">
                                            <div class="<?= $form_config['buttons']['submit']['div_class'] ?>">
                                                <button type="submit" 
                                                        class="<?= $id > 0 ? $form_config['buttons']['submit']['update_class'] : $form_config['buttons']['submit']['create_class'] ?>">
                                                    <i class="<?= $id > 0 ? $form_config['buttons']['submit']['update_icon'] : $form_config['buttons']['submit']['create_icon'] ?>"></i>
                                                    <?= $id > 0 ? ($tab_key === 'tab-password' ? $form_config['buttons']['submit']['password_tab_text'] : $form_config['buttons']['submit']['update_text']) : $form_config['buttons']['submit']['create_text'] ?>
                                                </button>
                                            </div>
                                            <?php if ($form_config['buttons']['reset']['show']): ?>
                                                <div class="<?= $form_config['buttons']['reset']['div_class'] ?>">
                                                    <button type="reset" class="<?= $form_config['buttons']['reset']['class'] ?>">
                                                        <i class="<?= $form_config['buttons']['reset']['icon'] ?>"></i> 
                                                        <?= $form_config['buttons']['reset']['text'] ?>
                                                    </button>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include_once 'footer.php'; ?>
 
<script>
// Pass PHP config to JavaScript
const formConfig = <?= json_encode($form_config) ?>;
const userId = <?= $id ?>;

document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('userForm');
    
    // Initialize field validations
    initFieldValidations();
    
    // Initialize form submission
    initFormSubmission();
    
    // Initialize form reset
    initFormReset();
});

function initFieldValidations() {
    // Iterate through all tabs and their inputs
    Object.entries(formConfig.tabs).forEach(([tabKey, tab]) => {
        Object.entries(tab.inputs).forEach(([fieldId, fieldConfig]) => {
            const element = document.getElementById(fieldId);
            if (!element || !fieldConfig.validation) return;
            
            const validation = fieldConfig.validation;
            
            // Real-time validation if enabled
            if (validation.realtime) {
                element.addEventListener('input', function() {
                    validateField(element, fieldConfig);
                });
            }
            
            // Special handling for password confirmation
            if (validation.type === 'password_confirm') {
                const matchField = document.getElementById(validation.match_field);
                if (matchField) {
                    // Validate on input
                    element.addEventListener('input', function() {
                        validatePasswordConfirm(element, matchField);
                    });
                    // Also validate when the original password changes
                    matchField.addEventListener('input', function() {
                        if (element.value.length > 0) {
                            validatePasswordConfirm(element, matchField);
                        }
                    });
                }
            }
        });
    });
}

function validateField(element, config) {
    const value = element.value;
    const validation = config.validation;
    
    // Skip validation if field is empty and not required
    if (value.length === 0 && !config.required) {
        element.classList.remove('is-invalid', 'is-valid');
        return true;
    }
    
    // Skip validation if empty
    if (value.length === 0) {
        element.classList.remove('is-invalid', 'is-valid');
        return true;
    }
    
    let isValid = true;
    
    // Validation based on type
    switch (validation.type) {
        case 'email':
            const emailRegex = new RegExp(validation.rules.pattern);
            isValid = emailRegex.test(value);
            break;
            
        case 'tel':
            const telRegex = new RegExp(validation.rules.pattern);
            isValid = telRegex.test(value);
            break;
            
        case 'password':
            const rules = validation.rules;
            const hasUpperCase = rules.hasUpperCase ? /[A-Z]/.test(value) : true;
            const hasLowerCase = rules.hasLowerCase ? /[a-z]/.test(value) : true;
            const hasNumber = rules.hasNumber ? /\d/.test(value) : true;
            const isLongEnough = value.length >= rules.minlength;
            isValid = hasUpperCase && hasLowerCase && hasNumber && isLongEnough;
            break;
            
        case 'pattern':
            const patternRegex = new RegExp(validation.rules.pattern);
            isValid = patternRegex.test(value);
            break;
            
        default:
            // Basic length validation
            if (validation.rules.minlength) {
                isValid = isValid && value.length >= validation.rules.minlength;
            }
            if (validation.rules.maxlength) {
                isValid = isValid && value.length <= validation.rules.maxlength;
            }
    }
    
    if (formConfig.validation.bootstrap_validation_classes) {
        if (isValid) {
            element.classList.remove('is-invalid');
            element.classList.add('is-valid');
        } else {
            element.classList.remove('is-valid');
            element.classList.add('is-invalid');
        }
    }
    
    return isValid;
}

function validatePasswordConfirm(confirmElement, passwordElement) {
    const confirmValue = confirmElement.value;
    
    if (confirmValue.length === 0) {
        confirmElement.classList.remove('is-invalid', 'is-valid');
        return true;
    }
    
    const isValid = confirmValue === passwordElement.value;
    
    if (formConfig.validation.bootstrap_validation_classes) {
        if (isValid) {
            confirmElement.classList.remove('is-invalid');
            confirmElement.classList.add('is-valid');
        } else {
            confirmElement.classList.remove('is-valid');
            confirmElement.classList.add('is-invalid');
        }
    }
    
    return isValid;
}

function initFormSubmission() {
    const form = document.getElementById('userForm');
    
    form.addEventListener('submit', function (e) {
        let isValid = true;
        let errorMessages = [];
        
        // Remove previous validation states
        if (formConfig.validation.bootstrap_validation_classes) {
            form.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
            form.querySelectorAll('.is-valid').forEach(el => el.classList.remove('is-valid'));
        }
        
        // Validate all fields based on config
        Object.entries(formConfig.tabs).forEach(([tabKey, tab]) => {
            Object.entries(tab.inputs).forEach(([fieldId, fieldConfig]) => {
                const element = document.getElementById(fieldId);
                if (!element) return;
                
                // Required field validation
                if (fieldConfig.required && element.type !== 'file') {
                    if (!element.value.trim()) {
                        element.classList.add('is-invalid');
                        isValid = false;
                        const fieldName = fieldConfig.label || 'Field';
                        errorMessages.push(`${fieldName} is required`);
                        return;
                    } else {
                        element.classList.add('is-valid');
                    }
                }
                
                // Field-specific validation
                if (element.value && fieldConfig.validation) {
                    const fieldValid = validateField(element, fieldConfig);
                    if (!fieldValid) {
                        isValid = false;
                        errorMessages.push(fieldConfig.validation_message || `Invalid ${fieldConfig.label}`);
                    }
                }
            });
        });
        
        if (!isValid) {
            e.preventDefault();
            e.stopPropagation();
            
            // Scroll to first invalid field
            if (formConfig.validation.scroll_to_error) {
                const firstInvalid = form.querySelector('.is-invalid');
                if (firstInvalid) {
                    firstInvalid.focus();
                    firstInvalid.scrollIntoView({ 
                        behavior: formConfig.validation.scroll_behavior,
                        block: formConfig.validation.scroll_block
                    });
                }
            }
            
            // Show error summary
            if (formConfig.validation.show_error_summary && errorMessages.length > 1) {
                const uniqueMessages = [...new Set(errorMessages)];
                console.error('Validation errors:', uniqueMessages);
            }
        } else {
            // Show loading indicator
            const submitBtn = form.querySelector('button[type="submit"]');
            if (submitBtn) {
                submitBtn.disabled = true;
                const loadingIcon = formConfig.buttons.submit.loading_icon;
                const loadingText = formConfig.buttons.submit.loading_text;
                submitBtn.innerHTML = `<i class="${loadingIcon}"></i> ${loadingText}`;
            }
        }
        
        form.classList.add('was-validated');
    });
}

function initFormReset() {
    const form = document.getElementById('userForm');
    
    form.addEventListener('reset', function() {
        form.classList.remove('was-validated');
        
        if (formConfig.validation.bootstrap_validation_classes) {
            form.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
            form.querySelectorAll('.is-valid').forEach(el => el.classList.remove('is-valid'));
        }
    });
}
</script>
 