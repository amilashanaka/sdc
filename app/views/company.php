<?php
include_once __DIR__ . '/header.php';

// Complete Form Configuration
$form_config = [
    // Basic Form Settings
    'heading' => 'Company',
    'form_action' => BASE_URL . '/company/save',
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

        'img1' => [
            'label' => 'Company Logo',
            'type' => 'file',
            'accept' => 'image/*',
            'preview' => true,
            'div_class' => 'col-lg-6 col-md-6 form-group',
            'validation_message' => 'Please select a valid image file.'
        ],

        'f1' => [
            'label' => 'Company Name',
            'type' => 'text',
            'class' => 'form-control',
            'div_class' => 'col-lg-12 col-md-12 form-group',
            'required' => true,
            'placeholder' => 'Enter company name',
            'pattern' => '^[A-Za-z ]+$',
            'title' => 'Only alphanumeric characters and spaces are allowed.',
            'minlength' => '2',
            'maxlength' => '100',
            'validation_message' => 'Company name must be 2-100 characters and contain only letters, numbers, and spaces.'
        ],

        'f2' => [
            'label' => 'Website',
            'type' => 'url',
            'class' => 'form-control',
            'div_class' => 'col-lg-6 col-md-6 form-group',
            'required' => false,
            'placeholder' => 'Enter company website',
            'pattern' => '^(https?:\/\/)?([\da-z\.-]+)\.([a-z\.]{2,6})([\/\w \.-]*)*\/?$',
            'title' => 'Please enter a valid website URL.',
            'validation_message' => 'Please enter a valid website URL (e.g., https://www.company.com).'
        ],

        // 'f5' => [
        //     'label' => 'Company Phone',
        //     'type' => 'tel',
        //     'class' => 'form-control',
        //     'div_class' => 'col-lg-6 col-md-6 form-group',
        //     'required' => false,
        //     'placeholder' => 'Enter company phone',
        //     'pattern' => '^[0-9]{10,15}$',
        //     'title' => 'Please enter a valid phone number (minimum 10 digits).',
        //     'minlength' => '10',
        //     'maxlength' => '20',
        //     'validation_message' => 'Phone number must be at least 10 digits and can include +, -, (), and spaces.'
        // ],

        // 'f3' => [
        //     'label' => 'Company Address',
        //     'type' => 'text',
        //     'class' => 'form-control',
        //     'div_class' => 'col-lg-12 col-md-12 form-group',
        //     'placeholder' => 'Enter company address',
        //     'minlength' => '5',
        //     'maxlength' => '255',
        //     'validation_message' => 'Address must be between 5-255 characters.'
        // ],

        // 'f6' => [
        //     'label' => 'Country',
        //     'type' => 'select',
        //     'class' => 'form-control',
        //     'div_class' => 'col-lg-12 col-md-12 form-group',
        //     'items' => list_all_countries(),
        //     'placeholder' => 'Select a country'
        // ],

        'f3' => [
            'label' => 'Company Description',
            'type' => 'textarea',
            'class' => 'form-control summernote',
            'div_class' => 'col-lg-12 col-md-12 form-group',
            'placeholder' => 'Enter company description',
            'rows' => '4',
            'minlength' => '10',
            'maxlength' => '1000',
            'validation_message' => 'Description must be between 10-1000 characters.'
        ]

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
        'data_source' => 'company', // Variable name for data source
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

include_once __DIR__ . '/page.php';
