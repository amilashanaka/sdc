<?php
include_once './header.php';

// Complete Form Configuration
$form_config = [
    // Basic Form Settings
    'heading' => 'package',
    'form_action' => 'data/register_package.php',
    'method' => 'post',
    'enctype' => 'multipart/form-data',

    // Page Configuration
    'page_config' => [
        'update_title_prefix' => 'Update',
        'new_title_prefix' => 'New',
        'container_class' => 'container-fluid',
        'card_class' => 'card',
        'card_body_class' => 'card-body'
    ],

    // Form Input Configuration
    'inputs' => [
        'id' => [
            'type' => 'hidden',
            'value' => ''
        ],
        'f1' => [
            'label' => 'Package Level',
            'type' => 'text',
            'class' => 'form-control',
            'div_class' => 'col-lg-6 col-md-6 form-group',
            'required' => true,
            'placeholder' => 'Enter package Level'
        ],
        'f2' => [
            'label' => 'Package Name',
            'type' => 'text',
            'class' => 'form-control',
            'div_class' => 'col-lg-6 col-md-6 form-group',
            'required' => true,
            'placeholder' => 'Enter package Name'
        ],
        'f3' => [
            'label' => 'Package Amount',
            'type' => 'number',
            'class' => 'form-control',
            'div_class' => 'col-lg-6 col-md-6 form-group',
            'required' => true,
            'placeholder' => 'Enter package Amount'
        ],
        'f4' => [
            'label' => 'Package Description',
            'type' => 'textarea',
            'class' => 'form-control summernote',
            'div_class' => 'col-lg-12 col-md-12 form-group',
            'placeholder' => 'Enter package Description'
        ],
        'f5' => [
            'label' => 'Feature 1',
            'type' => 'text',
            'class' => 'form-control',
            'div_class' => 'col-lg-6 col-md-6 form-group',
            'required' => true,
            'placeholder' => 'Enter Feature 1'
        ],
        'f6' => [
            'label' => 'Feature 2',
            'type' => 'text',
            'class' => 'form-control',
            'div_class' => 'col-lg-6 col-md-6 form-group',
            'placeholder' => 'Enter Feature 2'
        ],
        'f7' => [
            'label' => 'Feature 3',
            'type' => 'text',
            'class' => 'form-control',
            'div_class' => 'col-lg-6 col-md-6 form-group',
            'required' => true,
            'placeholder' => 'Enter Feature 3'
        ],
        'f8' => [
            'label' => 'Feature 4',
            'type' => 'text',
            'class' => 'form-control',
            'div_class' => 'col-lg-6 col-md-6 form-group',
            'required' => true,
            'placeholder' => 'Enter Feature 4'
        ],
        'f9' => [
            'label' => 'Feature 5',
            'type' => 'text',
            'class' => 'form-control',
            'div_class' => 'col-lg-6 col-md-6 form-group',
            'required' => true,
            'placeholder' => 'Enter Feature 5'
        ],
        'f10' => [
            'label' => 'Button Text',
            'type' => 'text',
            'class' => 'form-control',
            'div_class' => 'col-lg-6 col-md-6 form-group',
            'required' => true,
            'placeholder' => 'Enter Button Text'
        ],
        'f11' => [
            'label' => 'Stripe Link',
            'type' => 'text',
            'class' => 'form-control',
            'div_class' => 'col-lg-6 col-md-6 form-group',
            'placeholder' => 'Enter Stripe Link'
        ],
    ],

    // Button Configuration
    'buttons' => [
        'submit' => [
            'update_text' => 'Update Now',
            'create_text' => 'Add New',
            'update_class' => 'btn btn-block btn-outline-success',
            'create_class' => 'btn btn-block btn-outline-secondary',
            'div_class' => 'col-lg-2 col-md-2 form-group'
        ],
        'reset' => [
            'text' => 'Reset',
            'class' => 'btn btn-block btn-outline-warning',
            'div_class' => 'col-lg-2 col-md-2 form-group',
            'show' => true
        ]
    ],

    // Data Configuration
    'data_config' => [
        'id_param' => 'id',
        'data_source' => 'package',
        'method_name' => 'get_by_id'
    ],

    // Layout Configuration
    'layout' => [
        'form_row_class' => 'row',
        'button_row_class' => 'row',
        'separator' => '<hr>',
        'main_column_class' => 'col-md-12'
    ],

    // JavaScript Configuration
    'scripts' => [
        'preview_function' => 'previewImage',
       
    ]
];

include_once './page.php';
?>