<?php
include_once './header.php';
include_once './controllers/index.php';

// Initialize variables
$profile_image = null;
// Use a null coalesce operator for cleaner initialization
$id = isset($_GET['id']) ? base64_decode($_GET['id']) : 0;
$role = isset($_GET['role']) ? base64_decode($_GET['role']) : 0;
$row = null;
$user_list = null;
$user_act = $_SESSION['login'] ?? 0;
$user_role = $_SESSION['role'] ?? 0;

// Comprehensive form configuration
$form_config = [
    'heading' => 'Admin',
    'form_action' => 'data/register_admin.php',
    'upload_action' => './data/upload_profile_pic.php',
    'change_admin_action' => 'data/change_admin.php',

    // Tab configuration - tab3 (Assign Clients) is removed
    'tabs' => [
        'tab1' => [
            'id' => 'tab-1',
            'label' => 'Profile',
            'active' => true,
            'permission' => 'can_edit_profile' // New config key for tab access control
        ],
        'tab2' => [
            'id' => 'tab-2',
            'label' => 'Reset Password',
            'permission' => 'can_reset_password', // New config key for tab access control
            'role_specific_labels' => [
                3 => 'Reset Pin'
            ]
        ]
      
    ],

    // Button configuration (unchanged)
    'buttons' => [
        'add_new' => [
            'text' => 'Add New',
            'class' => 'btn btn-block btn-danger',
            'name' => 'add_new_Submit',
            'type' => 'submit'
        ],
        'update' => [
            'text' => 'Update Now',
            'class' => 'btn btn-block btn-success',
            'type' => 'submit'
        ],
        'reset' => [
            'text' => 'Reset',
            'class' => 'btn btn-block btn-warning',
            'type' => 'reset'
        ],
        'clear' => [
            'text' => 'Clear',
            'class' => 'btn btn-block btn-warning',
            'type' => 'reset'
        ],
        'assign' => [
            'text' => '<i class="fas fa-share"></i> Assign',
            'class' => 'btn btn-block btn-outline-secondary',
            'type' => 'submit'
        ],
        'login_as' => [
            'text' => 'Login',
            'class' => 'btn btn-primary btn-block',
            'confirm_message' => 'Are you sure you want to login as this user?'
        ]
    ],

    // Profile statistics configuration (unchanged)
    'profile_stats' => [
        'role_1_2' => [ // Roles less than 3
            'Last Login' => ['label' => 'Last Login', 'value' => '10'],
            'my_clients' => ['label' => 'My Clients', 'value' => '20'],
            'registered' => ['label' => 'Registered', 'value' => '20']
        ],
        'role_3' => [ // Role 3
            'staff_id' => ['label' => 'Staff Id', 'field' => 'f4'],
            'my_pin' => ['label' => 'My Pin', 'field' => 'f5'],
            'my_clients' => ['label' => 'My clients', 'value' => '80']
        ]
    ],

    // Form inputs configuration (unchanged)
    'inputs' => [
        'f1' => ['type' => 'h', 'skip' => true],
        'f2' => ['label' => 'User Name*', 'type' => 'text', 'required' => true, 'class' => 'form-control'],
        'f3' => ['label' => 'Password*', 'type' => 'password', 'required' => true, 'class' => 'form-control'],
        'f4' => ['label' => 'Staff Id*', 'type' => 'text', 'class' => 'form-control'],
        'f5' => ['label' => 'Pin*', 'type' => 'number', 'minlength' => '4', 'class' => 'form-control'],
        'f6' => ['label' => 'Name*', 'type' => 'text', 'class' => 'form-control'],
        'f8' => ['label' => 'Mobile Number', 'type' => 'text', 'class' => 'form-control'],
        'f9' => ['label' => 'e-mail', 'type' => 'email', 'class' => 'form-control'],
        'f10' => ['label' => 'Address', 'type' => 'textarea', 'class' => 'form-control'],
        'f11' => ['label' => 'National Insurance number', 'type' => 'text', 'class' => 'form-control'],
        'pwd' => [
            'label' => 'Password*',
            'type' => 'password',
            'class' => 'form-control',
            'role_specific' => [
                3 => ['label' => 'Pin', 'type' => 'number']
            ]
        ],
        'pwd_conf' => [
            'label' => 'Confirm Password*',
            'type' => 'password',
            'class' => 'form-control',
            'role_specific' => [
                3 => ['label' => 'Confirm Pin', 'type' => 'number']
            ]
        ],
        'user' => [
            'type' => 'combobox',
            'class' => 'form-control select2bs4',
            'dropdown-color' => 'success',
            'items' => [['value' => '', 'label' => '---SELECT ---']]
        ]
    ],

    // Layout configuration (unchanged)
    'layout' => [
        'profile_column_class' => 'col-md-3',
        'form_column_class_with_profile' => 'col-md-9',
        'form_column_class_without_profile' => 'col-md-12',
        'button_column_class' => 'col-lg-3 col-md-3 form-group'
    ],

    // File upload configuration (unchanged)
    'file_upload' => [
        'accept_types' => 'image/png, image/jpeg, image/gif',
        'filepond_settings' => [
            'allowImagePreview' => true,
            'imagePreviewHeight' => 170,
            'imageCropAspectRatio' => '1:1',
            'imageResizeTargetWidth' => 200,
            'imageResizeTargetHeight' => 200,
            'stylePanelLayout' => 'compact circle'
        ]
    ],

    // Permission configuration (can_assign_clients is unused but kept for completeness)
    'permissions' => [
        'can_edit_profile' => function($user_act, $user_role, $row_id, $role) {
            return ($user_act == $row_id && $user_role == 2) ||
                   $user_act == 1 ||
                   ($user_role == 2 && $role == 3);
        },
        'can_reset_password' => function($user_act, $user_role, $row_id, $role) {
            return ($user_act == $row_id && $user_role == 2) ||
                   $user_act == 1 ||
                   ($user_role == 2 && $role == 3);
        },
        'can_assign_clients' => function($role) {
            return $role > 1; // Kept for config structure
        },
        'can_login_as' => function($row_id, $user_act) {
            return isset($row_id) && $row_id != $user_act;
        }
    ],

    // Form field visibility by role and context (unchanged)
    'field_visibility' => [
        'new_form' => [
            'role_2' => ['f6', 'f2', 'f3'],
            'role_3' => ['f6', 'f4', 'f5']
        ],
        'edit_form' => [
            'all_roles' => ['f6', 'f8', 'f9'],
            'exclude_role_1' => ['f10', 'f11']
        ],
        'reset_password' => [
            'role_1_2' => ['pwd', 'pwd_conf'],
            'role_3' => ['pwd', 'pwd_conf'] // Will be modified for pin
        ]
    ],

    // Messages and text (unchanged)
    'messages' => [
        'page_titles' => [
            'new' => 'New %s',
            'update' => 'Update %s'
        ],
        'select_client_label' => 'Select Client'
    ]
];

// Helper function to get button HTML (unchanged)
function getButton($config, $button_key, $additional_attributes = '') {
    $button = $config['buttons'][$button_key];
    $class = $button['class'];
    $text = $button['text'];
    $type = $button['type'] ?? 'button';
    $name = isset($button['name']) ? "name=\"{$button['name']}\"" : '';
    $attributes = isset($button['attributes']) ? $button['attributes'] : ''; // Added to allow array merging
    return "<button type=\"{$type}\" {$name} class=\"{$class}\" {$additional_attributes}>{$text}</button>";
}

// Helper function to check permissions (unchanged)
function hasPermission($config, $permission_key, ...$args) {
    return $config['permissions'][$permission_key](...$args);
}

// Helper function to get tab label (unchanged)
function getTabLabel($config, $tab_key, $role = null) {
    $tab = $config['tabs'][$tab_key];
    if ($role && isset($tab['role_specific_labels'][$role])) {
        return $tab['role_specific_labels'][$role];
    }
    return $tab['label'];
}

// Fetch admin data if ID exists (unchanged)
if ($id > 0) {
    $row = $admin->getAdminById($id)['admin'];
    $role = $row['f1'];

    if (!empty($row['img1']) && file_exists($row['img1'])) {
        $profile_image = $row['img1'];
    }else
    {
        $profile_image = './assets/img/profile.png';
    }
}

// Include other required files (unchanged)
include_once './navbar.php';
include_once './sidebar.php';
?>

<div class='content-wrapper'>
    <?php
    $heading = $form_config['heading'];
    $page_title = $id > 0 ?
        sprintf($form_config['messages']['page_titles']['update'], $heading) :
        sprintf($form_config['messages']['page_titles']['new'], $heading);
    include_once './page_header.php';
    ?>
    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <?php if ($id > 0) : ?>
                    <div class="<?= $form_config['layout']['profile_column_class'] ?>">
                        <div class="card card-primary card-outline">
                            <div class="card-body box-profile">
                                <div class="text-center">
                                    <?php if ($profile_image) : ?>
                                        <input type="file" class="filepond d-none" name="filepond" accept="<?= $form_config['file_upload']['accept_types'] ?>" />
                                        <div id="profile-image-container" class="filepond--image-preview-wrapper">
                                            <img id="profile-image-filepond" class="filepond--image-preview" src="<?php echo $profile_image; ?>" alt="Profile Image">
                                            <button id="remove-profile-image" class="filepond--action-button filepond--action-button-remove-item" type="button">
                                                <svg aria-hidden="true" focusable="false" data-prefix="fas" data-icon="times" class="svg-inline--fa fa-times fa-w-11" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 352 512">
                                                    <path fill="currentColor" d="M242.72 256L349.72 149.72C356.36 143.07 356.36 132.93 349.72 126.28L325.72 102.28C319.07 95.64 308.93 95.64 302.28 102.28L195.72 208.72L89.72 102.28C83.07 95.64 72.93 95.64 66.28 102.28L42.28 126.28C35.64 132.93 35.64 143.07 42.28 149.72L149.72 256L42.28 362.28C35.64 368.93 35.64 379.07 42.28 385.72L66.28 409.72C72.93 416.36 83.07 416.36 89.72 409.72L195.72 303.28L302.28 409.72C308.93 416.36 319.07 416.36 325.72 409.72L349.72 385.72C356.36 379.07 356.36 368.93 349.72 362.28L242.72 256z"></path>
                                                </svg>
                                            </button>
                                        </div>
                                    <?php else : ?>
                                        <input type="file" class="filepond" name="filepond" accept="<?= $form_config['file_upload']['accept_types'] ?>" />
                                    <?php endif; ?>
                                </div>

                                <h3 class="profile-username text-center"><?= ($row != null) ? $row['f6'] : ""; ?></h3>
                                <p class="text-muted text-center"><?= ($row != null) ? $row['f9'] : ""; ?></p>
                                <ul class="list-group list-group-unbordered mb-3">
                                    <?php if ($role < 3) : ?>
                                        <?php foreach ($form_config['profile_stats']['role_1_2'] as $key => $stat) : ?>
                                            <li class="list-group-item">
                                                <b><?= $stat['label'] ?></b>
                                                <a class="float-right"><?= $stat['value'] ?></a>
                                            </li>
                                        <?php endforeach; ?>
                                    <?php endif; ?>

                                    <?php if ($role == 3) : ?>
                                        <?php foreach ($form_config['profile_stats']['role_3'] as $key => $stat) : ?>
                                            <li class="list-group-item">
                                                <b><?= $stat['label'] ?></b>
                                                <a class="float-right">
                                                    <?= isset($stat['field']) ? (($row != null) ? $row[$stat['field']] : "") : $stat['value'] ?>
                                                </a>
                                            </li>
                                        <?php endforeach; ?>
                                    <?php endif; ?>

                                    <?php if (hasPermission($form_config, 'can_login_as', $row['id'] ?? null, $user_act)) : ?>
                                        <?php
                                        $onclick = "changeAdmin('" . base64_encode($row['id']) . "','" . base64_encode($row['f1']) . "')";
                                        echo getButton($form_config, 'login_as', "onclick=\"$onclick\"");
                                        ?>
                                    <?php endif; ?>
                                </ul>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <div class="<?= $id > 0 ? $form_config['layout']['form_column_class_with_profile'] : $form_config['layout']['form_column_class_without_profile'] ?>">
                    <div class="card">
                        <div class="card-header p-2">
                            <ul class="nav nav-pills">
                                <?php
                                $first_tab = true;
                                foreach ($form_config['tabs'] as $tab_key => $tab) :
                                    // Logic for tab visibility based on context (new form vs. edit form) and permissions
                                    $show_tab = false;

                                    if ($tab_key === 'tab1' && $id == 0) {
                                        $show_tab = true; // Always show Profile on New form
                                    } elseif ($id > 0 && isset($tab['permission'])) {
                                        // On Edit form, check the defined permission for the tab
                                        $permission_key = $tab['permission'];
                                        if (hasPermission($form_config, $permission_key, $user_act, $user_role, $row['id'] ?? null, $role)) {
                                            $show_tab = true;
                                        }
                                    }

                                    if ($show_tab) :
                                        $is_active = $first_tab ? 'active' : '';
                                ?>
                                    <li class="nav-item">
                                        <a class="nav-link <?= $is_active ?>" href="#<?= $tab['id'] ?>" data-toggle="tab">
                                            <?= getTabLabel($form_config, $tab_key, $role) ?>
                                        </a>
                                    </li>
                                <?php
                                        $first_tab = false;
                                    endif;
                                endforeach;
                                ?>
                            </ul>
                        </div>

                        <div class="card-body">
                            <div class="tab-content">
                                <?php
                                // Reset the first tab flag for content
                                $first_tab = true;
                                foreach ($form_config['tabs'] as $tab_key => $tab) :
                                    // Re-run visibility check to match the nav logic
                                    $show_tab = false;
                                    if ($tab_key === 'tab1' && $id == 0) {
                                        $show_tab = true;
                                    } elseif ($id > 0 && isset($tab['permission'])) {
                                        $permission_key = $tab['permission'];
                                        if (hasPermission($form_config, $permission_key, $user_act, $user_role, $row['id'] ?? null, $role)) {
                                            $show_tab = true;
                                        }
                                    }

                                    if ($show_tab) :
                                        $is_active = $first_tab ? 'active' : '';
                                ?>
                                    <div class="<?= $is_active ?> tab-pane" id="<?= $tab['id'] ?>">

                                        <?php if ($tab_key === 'tab1') : // Profile Tab Content ?>
                                            <form action="<?= $form_config['form_action'] ?>" class="form-horizontal" method="post" enctype="multipart/form-data" name="update_members">
                                                <input type="hidden" name="register_by" value="<?= $user_act ?>">
                                                <input type="hidden" name="f1" value="<?= $role ?>">
                                                <?php if ($id == 0) : ?>
                                                    <input type="hidden" name="action" value="register">
                                                <?php else : ?>
                                                    <input type="hidden" name="action" value="update">
                                                    <input type="hidden" name="id" value="<?= $id ?>">
                                                <?php endif; ?>

                                                <?php if ($id > 0) : // Edit Form Fields ?>
                                                    <div class="row">
                                                        <?php
                                                        // Show fields for edit form
                                                        foreach ($form_config['field_visibility']['edit_form']['all_roles'] as $field) {
                                                            echo input($field);
                                                        }
                                                        if ($role != 1) {
                                                            foreach ($form_config['field_visibility']['edit_form']['exclude_role_1'] as $field) {
                                                                echo input($field);
                                                            }
                                                        }
                                                        ?>
                                                    </div>
                                                <?php endif; ?>

                                                <div class="row">
                                                    <?php if ($id == 0) : // New Form Fields ?>
                                                        <?php
                                                        $fields_to_show = $form_config['field_visibility']['new_form']['role_' . $role] ?? [];
                                                        foreach ($fields_to_show as $field) {
                                                            echo input($field);
                                                        }
                                                        ?>
                                                    <?php endif; ?>
                                                </div>

                                                <div class="col-lg-12 col-md-12 form-group">
                                                    <div class="row">
                                                        <?php if ($id == 0) : ?>
                                                            <div class="<?= $form_config['layout']['button_column_class'] ?>">
                                                                <?= getButton($form_config, 'add_new') ?>
                                                            </div>
                                                        <?php else : ?>
                                                            <div class="<?= $form_config['layout']['button_column_class'] ?>">
                                                                <?= getButton($form_config, 'update') ?>
                                                            </div>
                                                        <?php endif; ?>
                                                        <div class="<?= $form_config['layout']['button_column_class'] ?>">
                                                            <?= getButton($form_config, 'reset') ?>
                                                        </div>
                                                    </div>
                                                </div>
                                            </form>

                                        <?php elseif ($tab_key === 'tab2') : // Reset Password Tab Content ?>
                                            <form action="<?= $form_config['form_action'] ?>" method="post" enctype="multipart/form-data">
                                                <input type="hidden" name="update_by" value="<?= $user_act ?>">
                                                <input type="hidden" name="f1" value="<?= $role ?>">
                                                <input type="hidden" name="action" value="reset_pwd">
                                                <input type="hidden" name="id" value="<?= $id ?>">

                                                <?php if ($id > 0) : ?>
                                                    <div class="row">
                                                        <?php
                                                        // Modify field config for Role 3 before rendering
                                                        if ($role == 3) {
                                                             $form_config['inputs']['pwd']['label'] = $form_config['inputs']['pwd']['role_specific'][3]['label'];
                                                             $form_config['inputs']['pwd']['type'] = $form_config['inputs']['pwd']['role_specific'][3]['type'];
                                                             $form_config['inputs']['pwd_conf']['label'] = $form_config['inputs']['pwd_conf']['role_specific'][3]['label'];
                                                             $form_config['inputs']['pwd_conf']['type'] = $form_config['inputs']['pwd_conf']['role_specific'][3]['type'];
                                                        }

                                                        // Get fields based on role
                                                        $fields_to_show = ($role == 3)
                                                            ? $form_config['field_visibility']['reset_password']['role_3']
                                                            : $form_config['field_visibility']['reset_password']['role_1_2'];

                                                        foreach ($fields_to_show as $field) {
                                                            echo input($field);
                                                        }
                                                        ?>
                                                    </div>
                                                <?php endif; ?>

                                                <div class="col-lg-12 col-md-12 form-group">
                                                    <div class="row">
                                                        <div class="<?= $form_config['layout']['button_column_class'] ?>">
                                                            <?= getButton($form_config, 'reset') ?>
                                                        </div>
                                                        <div class="<?= $form_config['layout']['button_column_class'] ?>">
                                                            <?= getButton($form_config, 'clear') ?>
                                                        </div>
                                                    </div>
                                                </div>
                                            </form>
                                        <?php endif; ?>

                                    </div>
                                <?php
                                        $first_tab = false;
                                    endif;
                                endforeach;
                                ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<script>
    // FilePond initialization
    document.addEventListener('DOMContentLoaded', function() {
        const imagePath = '<?= $profile_image ?>';
        const userId = '<?= $id ?>';
        const hasImage = Boolean('<?= $profile_image ? 'true' : 'false' ?>');
        const uploadUrl = '<?= $form_config['upload_action'] ?>';

        // Get FilePond settings from config
        const filePondSettings = <?= json_encode($form_config['file_upload']['filepond_settings']) ?>;

        // Register plugins
        FilePond.registerPlugin(
            FilePondPluginFileValidateType,
            FilePondPluginImagePreview,
            FilePondPluginImageCrop,
            FilePondPluginImageResize
        );

        // Create FilePond instance
        const pond = FilePond.create(document.querySelector('.filepond'), {
            ...filePondSettings,
            server: {
                process: {
                    url: uploadUrl,
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '<?= $_SESSION['csrf_token'] ?? '' ?>'
                    },
                    ondata: (formData) => {
                        formData.append('id', userId);
                        formData.append('target', 'admins');
                        return formData;
                    }
                }
            }
        });

        // Handle remove profile image button
        if (document.getElementById('remove-profile-image')) {
            document.getElementById('remove-profile-image').addEventListener('click', function() {
                document.querySelector('.filepond').classList.remove('d-none');
                document.getElementById('profile-image-container').style.display = 'none';
            });
        }

        // Password toggle functionality
        $(document).on('click', '.toggle-password', function() {
            const target = $(this).data('target');
            const icon = $(this).find('i');

            if ($(target).attr('type') === 'password' || $(target).attr('type') === 'number') {
                $(target).attr('type', 'text');
                icon.removeClass('fa-eye').addClass('fa-eye-slash');
            } else {
                $(target).attr('type', $(target).data('original-type') || 'password'); // Reset to original type
                icon.removeClass('fa-eye-slash').addClass('fa-eye');
            }
        });
    });

    function changeAdmin(id, role) {
        const confirmMessage = '<?= $form_config['buttons']['login_as']['confirm_message'] ?>';
        const changeAdminUrl = '<?= $form_config['change_admin_action'] ?>';

        if (confirm(confirmMessage)) {
            window.location.href = changeAdminUrl + '?id=' + id + '&role=' + role;
        }
    }
</script>

<?php include_once './footer.php'; ?>
</body>
</html>