<?php
include_once './header.php';

// Complete Form Configuration
$form_config = [
    // Basic Form Settings
    'heading' => 'Course',
    'form_action' => 'data/register_course.php',
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
            'label' => 'Course Level',
            'type' => 'text',
            'class' => 'form-control',
            'div_class' => 'col-lg-12 col-md-12 form-group',
            'required' => true,
            'placeholder' => 'Enter course level',
            'validation' => '^[A-Za-z0-9 ]+$',
            'validation_message' => 'Only alphanumeric characters and spaces are allowed.'
        ],
        'f2' => [
            'label' => 'Course Title',
            'type' => 'text',
            'class' => 'form-control',
            'div_class' => 'col-lg-12 col-md-12 form-group',
            'required' => true,
            'placeholder' => 'Enter course title',
        ],
        'f3' => [
            'label' => 'Course Name',
            'type' => 'text',
            'class' => 'form-control',
            'div_class' => 'col-lg-12 col-md-12 form-group',
            'required' => true,
            'placeholder' => 'Enter course name',
        ],
        'f4' => [
            'label' => 'For',
            'type' => 'text',
            'class' => 'form-control',
            'div_class' => 'col-lg-12 col-md-12 form-group',
            'required' => false,
            'placeholder' => 'Enter course for',
        ],
        'f5' => [
            'label' => 'Price',
            'type' => 'number',
            'class' => 'form-control',
            'div_class' => 'col-lg-12 col-md-12 form-group',
            'placeholder' => 'Enter course price',
            'required' => true
        ],
        'f6' => [
            'label' => 'Point 1',
            'type' => 'text',
            'class' => 'form-control',
            'div_class' => 'col-lg-12 col-md-12 form-group',
            'rows' => 5,
            'required' => false,
            'placeholder' => 'Enter point 1',
        ],
        'f7' => [
            'label' => 'Point 2',
            'type' => 'text',
            'class' => 'form-control',
            'div_class' => 'col-lg-12 col-md-12 form-group',
            'rows' => 5,
            'required' => false
        ],
        'f8' => [
            'label' => 'Point 3',
            'type' => 'text',
            'class' => 'form-control',
            'div_class' => 'col-lg-12 col-md-12 form-group',
            'rows' => 5,
            'required' => false
        ],
        'f9' => [
            'label' => 'Point 4',
            'type' => 'text',
            'class' => 'form-control',
            'div_class' => 'col-lg-12 col-md-12 form-group',
            'rows' => 5,
            'required' => false
        ],
        'f10' => [
            'label' => 'Description',
            'type' => 'textarea',
            'class' => 'form-control summernote',
            'div_class' => 'col-lg-12 col-md-12 form-group',
            'rows' => 5,
            'required' => false
        ],
        'f11' => [
            'label' => 'Stripe Link',
            'type' => 'text',
            'class' => 'form-control',
            'div_class' => 'col-lg-12 col-md-12 form-group',
            'rows' => 5,
            'required' => false // Initially false, will be handled by JavaScript
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
        'data_source' => 'course',
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