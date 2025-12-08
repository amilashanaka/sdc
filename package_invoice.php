<?php
include_once './header.php';

// Complete Form Configuration
$form_config = [
    // Basic Form Settings
    'heading' => 'Package Invoice',
    'form_action' => 'data/register_package_invoice.php',
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
            'label' => 'Amount',
            'type' => 'number',
            'class' => 'form-control',
            'div_class' => 'col-lg-12 col-md-12 form-group',
            'required' => true,
            'placeholder' => 'Enter amount'
        ],
        'f2' => [
            'label' => 'Client',
            'type' => 'select',
            'items' => items_from_model($user),
            'class' => 'form-control',
            'div_class' => 'col-lg-12 col-md-12 form-group',
            'required' => true,
            'placeholder' => 'Select client'
        ],
        'f3' => [
            'label' => 'Course',
            'type' => 'select',
             'items' => items_from_model($course),
            'class' => 'form-control',
            'div_class' => 'col-lg-12 col-md-12 form-group',
            'required' => false,
            'placeholder' => 'Enter course name'
        ],
        'f4' => [
            'label' => 'Package',
            'type' => 'select',
            'items' =>  items_from_model($package, 'f2'),
            'class' => 'form-control',
            'div_class' => 'col-lg-12 col-md-12 form-group',
            'required' => false,
            'placeholder' => 'Enter package name'
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
        'data_source' => 'payment', // Variable name for data source
        'method_name' => 'get_by_id' // Method to call on data source
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
        'additional_scripts' => []
    ]
];

include_once './page.php';