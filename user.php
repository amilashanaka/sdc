<?php
include_once './header.php';

// Enhanced Form Configuration with Tabs
$form_config = [
    'heading' => 'User',
    'form_action' => 'data/register_user.php',
    'profile_image' => [
        'enabled' => true,
        'preview' => true,
        'default_image' => './assets/img/profile.png',
        'upload_path' => './data/upload_profile_pic.php',
        'target' => 'users',
        'filepond_options' => [
            'imagePreviewHeight' => 170,
            'imageCropAspectRatio' => '1:1',
            'imageResizeTargetWidth' => 200,
            'imageResizeTargetHeight' => 200,
            'stylePanelLayout' => 'compact circle'
        ]
    ],
    'tabs' => [
        'tab-1' => [
            'label' => 'Basic Information',
            'active' => true,
            'fields' => ['f1', 'f3', 'f4', 'f7', 'f6'],
            'show_only_on_edit' => false
        ],
        'tab-2' => [
            'label' => 'Reset Password',
            'active' => false,
            'fields' => ['f2'],
            'show_only_on_edit' => true
        ]
    ],
    'inputs' => [
        'id' => ['type' => 'hidden', 'value' => ''],
        'f1' => [
            'label' => 'Email',
            'type' => 'email',
            'placeholder' => 'Email',
            'class' => 'form-control',
            'div_class' => 'form-group row',
            'label_class' => 'col-md-2 control-label',
            'input_div_class' => 'col-sm-10',
            'required' => true
        ],
        'f2' => [
            'label' => 'Password',
            'type' => 'password',
            'placeholder' => 'Password',
            'class' => 'form-control',
            'div_class' => 'form-group row',
            'label_class' => 'col-md-2 control-label',
            'input_div_class' => 'col-sm-10',
            'required' => true,
            'hide_on_edit' => false
        ],
        'f3' => [
            'label' => 'Mobile number',
            'type' => 'tel',
            'class' => 'form-control',
            'div_class' => 'form-group row',
            'label_class' => 'col-md-2 control-label',
            'input_div_class' => 'col-sm-10',
            'attributes' => [
                'pattern' => '^[0-9]{10,13}$',
                'inputmode' => 'numeric',
                'maxlength' => '13',
                'minlength' => '10',
                'oninput' => 'this.value=this.value.replace(/\\D/g,"")'
            ],
            'validation' => [
                'onkeyup' => 'validateContactNumber(this)',
                'messages' => [
                    'error' => 'Invalid contact number'
                ]
            ]
        ],
        'f4' => [
            'label' => 'Name',
            'type' => 'text',
            'placeholder' => 'Name',
            'class' => 'form-control',
            'div_class' => 'form-group row',
            'label_class' => 'col-md-2 control-label',
            'input_div_class' => 'col-sm-10'
        ],
        'f7' => [
            'label' => 'Date of Birth',
            'type' => 'date',
            'class' => 'form-control',
            'div_class' => 'form-group row',
            'label_class' => 'col-md-2 control-label',
            'input_div_class' => 'col-sm-10'
        ],
        'f5' => [
            'label' => 'Gender',
            'type' => 'select',
            'options' => [
                '' => 'Select Gender',
                'male' => 'Male',
                'female' => 'Female',
                'other' => 'Other'
            ],
            'class' => 'form-control',
            'div_class' => 'form-group row',
            'label_class' => 'col-md-2 control-label',
            'input_div_class' => 'col-sm-10'
        ],
        'f6' => [
            'label' => 'Address',
            'type' => 'textarea',
            'class' => 'form-control',
            'div_class' => 'form-group row',
            'label_class' => 'col-md-2 control-label',
            'input_div_class' => 'col-sm-10'
        ],
        'f8' => [
            'label' => 'Country',
            'type' => 'text',
            'class' => 'form-control',
            'div_class' => 'form-group row',
            'label_class' => 'col-md-2 control-label',
            'input_div_class' => 'col-sm-10'
        ],
        'img1' => [
            'label' => 'Profile Image',
            'type' => 'file',
            'accept' => 'image/*',
            'preview' => true,
            'div_class' => 'col-lg-12 col-md-12 form-group'
        ]
    ],
    'buttons' => [
        'submit' => [
            'class' => 'btn btn-block',
            'new_label' => 'Add New',
            'new_class' => 'btn-danger',
            'edit_label' => 'Update Now',
            'edit_class' => 'btn-success'
        ],
        'clear' => [
            'class' => 'btn btn-block btn-warning',
            'label' => 'Clear'
        ]
    ]
];

// Fetch user data if an ID is provided
$id = isset($_GET['id']) ? intval(base64_decode($_GET['id'])) : 0;
$row = ($id > 0 && isset($user)) ? $user->get_by_id($id)['data'] : null;

// Get level parameter
$level = isset($_GET['level']) ? base64_decode($_GET['level']) : 1;

// Set profile image
$profile_image = $form_config['profile_image']['default_image'];
if ($id > 0 && $row && isset($row['img1']) && $row['img1'] != '') {
    $profile_image = $row['img1'];
    if (!file_exists($profile_image)) {
        $profile_image = $form_config['profile_image']['default_image'];
    }
}

include_once './navbar.php';
include_once './sidebar.php';
?>

<!-- Content Wrapper -->
<div class="content-wrapper">
    <!-- Page Header -->
    <?php
    $heading = $form_config['heading'];
    $page_title = $id > 0 ? "Update Profile" : "New Profile";
    include_once './page_header.php';
    ?>

    <!-- Main Content -->
    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <!-- Profile Image Column -->
                <?php if ($form_config['profile_image']['enabled']): ?>
                    <div class="col-md-3">
                        <div class="card card-primary card-outline">
                            <div class="card-body box-profile fs-6">
                                <div class="text-center">
                                    <?php if ($id > 0 && $profile_image) { ?>
                                        <input type="file" class="filepond d-none" name="filepond" accept="image/png, image/jpeg, image/gif" />
                                        <div id="profile-image-container" class="filepond--image-preview-wrapper">
                                            <img id="profile-image-filepond" class="filepond--image-preview" src="<?php echo htmlspecialchars($profile_image); ?>" alt="Profile Image">
                                            <button id="remove-profile-image" class="filepond--action-button filepond--action-button-remove-item" type="button">
                                                <svg aria-hidden="true" focusable="false" data-prefix="fas" data-icon="times" class="svg-inline--fa fa-times fa-w-11" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 352 512">
                                                    <path fill="currentColor" d="M242.72 256L349.72 149.72C356.36 143.07 356.36 132.93 349.72 126.28L325.72 102.28C319.07 95.64 308.93 95.64 302.28 102.28L195.72 208.72L89.72 102.28C83.07 95.64 72.93 95.64 66.28 102.28L42.28 126.28C35.64 132.93 35.64 143.07 42.28 149.72L149.72 256L42.28 362.28C35.64 368.93 35.64 379.07 42.28 385.72L66.28 409.72C72.93 416.36 83.07 416.36 89.72 409.72L195.72 303.28L302.28 409.72C308.93 416.36 319.07 416.36 325.72 409.72L349.72 385.72C356.36 379.07 356.36 368.93 349.72 362.28L242.72 256z"></path>
                                                </svg>
                                            </button>
                                        </div>
                                    <?php } else { ?>
                                        <div id="profile-image-container" class="filepond--image-preview-wrapper">
                                            <img id="profile-image-filepond" class="filepond--image-preview" src="<?php echo htmlspecialchars($profile_image); ?>" alt="Profile Image">
                                        </div>
                                    <?php } ?>
                                </div>

                                <h3 class="profile-username text-center"><?= htmlspecialchars($row['f4'] ?? ''); ?></h3>
                                <p class="text-muted text-center"><?= htmlspecialchars($row['f1'] ?? ''); ?></p>
                                <?php if ($id > 0): ?>
                                    <div class="mt-4 mb-2 px-2">
                                        <ul class="list-group list-group-flush shadow-sm rounded">
                                            <li class="list-group-item d-flex justify-content-between align-items-center border-0 pb-1">
                                                <span class="fw-bold text-secondary">Country</span>
                                                <span class="text-dark"><?= htmlspecialchars($row['f5'] ?? ''); ?></span>
                                            </li>
                                            <li class="list-group-item d-flex justify-content-between align-items-center border-0 pt-1">
                                                <span class="fw-bold text-secondary">Mobile</span>
                                                <span class="text-dark"><?= htmlspecialchars($row['f3'] ?? ''); ?></span>
                                            </li>
                                        </ul>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Form Column -->
                <div class="<?= $form_config['profile_image']['enabled'] ? 'col-md-9' : 'col-md-12' ?>">
                    <div class="card">
                        <div class="card-header p-2">
                            <ul class="nav nav-pills">
                                <?php foreach ($form_config['tabs'] as $tab_id => $tab):
                                    $show_only_on_edit = $tab['show_only_on_edit'] ?? false;
                                    if ($show_only_on_edit && $id <= 0) continue;
                                ?>
                                    <li class="nav-item">
                                        <a class="nav-link <?= ($tab['active'] ?? false) ? 'active' : '' ?>"
                                            href="#<?= htmlspecialchars($tab_id); ?>"
                                            data-toggle="tab">
                                            <?= htmlspecialchars($tab['label']); ?>
                                        </a>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>

                        <div class="card-body">
                            <div class="tab-content">
                                <?php foreach ($form_config['tabs'] as $tab_id => $tab):
                                    $show_only_on_edit = $tab['show_only_on_edit'] ?? false;
                                    if ($show_only_on_edit && $id <= 0) continue;
                                ?>
                                    <div class="tab-pane <?= ($tab['active'] ?? false) ? 'active' : '' ?>" id="<?= htmlspecialchars($tab_id); ?>">
                                        <form action="<?= htmlspecialchars($form_config['form_action']); ?>"
                                            class="form-horizontal" method="post" enctype="multipart/form-data">

                                            <input type="hidden" name="<?= $id > 0 ? 'updated_by' : 'created_by' ?>" value="<?= htmlspecialchars($_SESSION['login']); ?>">
                                            <input type="hidden" name="level" value="<?= htmlspecialchars($level); ?>">
                                            <input type="hidden" name="action" value="<?= $id > 0 ? 'update' : 'register' ?>">

                                            <?php if ($id > 0) { ?>
                                                <input type="hidden" name="id" value="<?= htmlspecialchars($id); ?>">
                                            <?php } ?>

                                            <div class="row">
                                                <?php
                                                foreach ($tab['fields'] as $field_key) {
                                                    if (isset($form_config['inputs'][$field_key])) {
                                                        $field = $form_config['inputs'][$field_key];

                                                        // Skip fields that should be hidden in edit mode
                                                        if ($id > 0 && ($field['hide_on_edit'] ?? false)) {
                                                            continue;
                                                        }

                                                        $value = $row[$field_key] ?? '';
                                                        if ($field_key === 'f2' && $id > 0) {
                                                            // Don't show password value for security
                                                            $value = '';
                                                        }
                                                        renderFormInput($field_key, $field, $value);
                                                    }
                                                }
                                                ?>
                                            </div>

                                            <hr>
                                            <div class="row">
                                                <div class="col-lg-3 col-md-3 form-group">
                                                    <button type="submit" class="<?= htmlspecialchars($form_config['buttons']['submit']['class']); ?> <?= $id > 0 ? htmlspecialchars($form_config['buttons']['submit']['edit_class']) : htmlspecialchars($form_config['buttons']['submit']['new_class']); ?>">
                                                        <?= htmlspecialchars($id > 0 ? $form_config['buttons']['submit']['edit_label'] : $form_config['buttons']['submit']['new_label']); ?>
                                                    </button>
                                                </div>
                                                <div class="col-lg-3 col-md-3 form-group">
                                                    <button type="reset" class="<?= htmlspecialchars($form_config['buttons']['clear']['class']); ?>">
                                                        <?= htmlspecialchars($form_config['buttons']['clear']['label']); ?>
                                                    </button>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<?php include_once './footer.php'; ?>

<script>
    const formConfig = <?= json_encode($form_config); ?>;
    const imagePath = '<?= addslashes($profile_image); ?>';
    const userId = '<?= $id; ?>';
    const hasImage = Boolean('<?= $profile_image ? 'true' : 'false'; ?>');

    let pond; // Declare pond variable globally

    function initializeFilePond() {
        if (typeof FilePond === 'undefined') {
            console.error('FilePond is not loaded');
            return;
        }

        FilePond.registerPlugin(
            FilePondPluginFileValidateType,
            FilePondPluginImageExifOrientation,
            FilePondPluginImagePreview,
            FilePondPluginImageCrop,
            FilePondPluginImageResize,
            FilePondPluginImageTransform
        );

        const filePondElement = document.querySelector('.filepond');
        if (!filePondElement) return;

        pond = FilePond.create(filePondElement, {
            ...formConfig.profile_image.filepond_options,
            server: {
                process: {
                    url: formConfig.profile_image.upload_path,
                    method: 'POST',
                    withCredentials: false,
                    headers: {},
                    timeout: 7000,
                    onload: (response) => response,
                    onerror: (response) => response,
                    ondata: (formData) => {
                        formData.append('id', userId);
                        formData.append('target', formConfig.profile_image.target);
                        return formData;
                    }
                }
            }
        });

        pond.on('processfile', (error, file) => {
            if (error) {
                console.error('Error processing file:', error);
                return;
            }
            const serverResponse = file.serverId;
            try {
                const response = JSON.parse(serverResponse);
                if (response.status === 'success') {
                    const imgPath = response.img_path;
                    const profileImg = document.getElementById('profile-image-filepond');
                    if (profileImg) profileImg.src = imgPath;
                    console.log('File uploaded successfully');
                } else {
                    console.error('File upload error:', response.message);
                }
            } catch (e) {
                console.error('Failed to parse server response:', serverResponse);
            }
        });
    }

    // Initialize FilePond and Clear button when DOM is loaded
    document.addEventListener('DOMContentLoaded', function() {
        initializeFilePond();

        // Remove profile image functionality
        const removeButton = document.getElementById('remove-profile-image');
        if (removeButton) {
            removeButton.addEventListener('click', () => {
                // Show FilePond elements
                const filePondInput = document.querySelector('.filepond');
                if (filePondInput) filePondInput.classList.remove('d-none');

                // Hide profile image container
                const profileImageContainer = document.getElementById('profile-image-container');
                if (profileImageContainer) profileImageContainer.style.display = 'none';

                // Reinitialize FilePond
                initializeFilePond();
            });
        }

        // Clear button functionality
        const clearButton = document.querySelector('button[type="reset"]');
        if (clearButton) {
            clearButton.addEventListener('click', (event) => {
                event.preventDefault(); // Prevent default reset to fully control the behavior
                const form = clearButton.closest('form');
                if (form) {
                    // Reset standard form inputs
                    form.querySelectorAll('input:not([type="hidden"]), textarea, select').forEach(input => {
                        if (input.type === 'select-one') {
                            input.selectedIndex = 0; // Reset select to first option
                        } else {
                            input.value = ''; // Clear text, email, date, textarea, etc.
                        }
                    });

                    // Reset FilePond
                    if (pond) {
                        pond.removeFiles(); // Clear FilePond files
                        const profileImg = document.getElementById('profile-image-filepond');
                        if (profileImg) {
                            profileImg.src = formConfig.profile_image.default_image; // Reset to default image
                        }
                        const filePondInput = document.querySelector('.filepond');
                        if (filePondInput) filePondInput.classList.remove('d-none');
                        const profileImageContainer = document.getElementById('profile-image-container');
                        if (profileImageContainer) profileImageContainer.style.display = 'block';
                    }
                }
            });
        }
    });
</script>
<script>
    function validateContactNumber(inputEl){
        const value = (inputEl && inputEl.value) ? inputEl.value : '';
        const isValid = /^[0-9]{10,13}$/.test(value);
        const err = document.getElementById('f3_error');
        if(err){
            err.style.display = isValid || value === '' ? 'none' : 'block';
        }
        if(inputEl){
            if(!isValid && value !== ''){ inputEl.classList.add('is-invalid'); }
            else { inputEl.classList.remove('is-invalid'); }
        }
        return isValid || value === '';
    }

    // Block form submit if contact number invalid
    document.addEventListener('submit', function(e){
        const target = e.target;
        if(!(target && target.tagName === 'FORM')) return;
        const cn = target.querySelector('#f3');
        if(!cn) return;
        // sanitize again
        cn.value = cn.value.replace(/\D/g,'');
        const ok = validateContactNumber(cn);
        if(!ok){
            e.preventDefault();
            e.stopPropagation();
            cn.focus();
        }
    }, true);
</script>
</body>
</html>