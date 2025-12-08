 
<?php
include_once './header.php';

// Complete Form Configuration
$form_config = [
    // Basic Form Settings
    'heading' => 'Course',
    'form_action' => 'data/register_blog.php',
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
            'label' => 'Upload Image 1', 
            'type' => 'file', 
            'accept' => 'image/*', 
            'preview' => true, 
            'div_class' => 'col-lg-4 col-md-4 form-group',
            'required' => false
           ],
        'f1' => [
            'label' => 'Title', 
            'type' => 'text', 
            'class' => 'form-control', 
            'div_class' => 'col-lg-12 col-md-12 form-group',
            'required' => false,
            'placeholder' => ''
        ],
        'f2' => [
            'label' => 'Sub Title 1', 
            'type' => 'text', 
            'class' => 'form-control', 
            'div_class' => 'col-lg-12 col-md-12 form-group',
            'required' => false,
            'placeholder' => ''
        ],
        'f5' => [
            'label' => 'Content 1', 
            'type' => 'textarea', 
            'class' => 'form-control summernote', 
            'div_class' => 'col-lg-12 col-md-12 form-group',
            'required' => false,
            'rows' => 4
        ],
         'f6' => [
            'label' => 'Category', 
            'type' => 'text', 
            'class' => 'form-control', 
            'div_class' => 'col-lg-12 col-md-12 form-group',
            'required' => true,
            'rows' => 4
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
        'data_source' => 'blog', // Variable name for data source
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