<?php
include_once './header.php';

// Complete Form Configuration
$form_config = [
    // Basic Form Settings
    'heading' => 'Payment',
    'form_action' => 'data/register_payment.php',
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
            'label' => 'Full Name',
            'type' => 'text',
            'class' => 'form-control',
            'div_class' => 'col-lg-12 col-md-12 form-group',
            'required' => true,
            'placeholder' => 'Enter Full Name',
        ],
        'f2' => [
            'label' => 'Email',
            'type' => 'text',
            'class' => 'form-control',
            'div_class' => 'col-lg-12 col-md-12 form-group',
            'required' => true,
            'placeholder' => 'Enter Email',
        ],
        'f3' => [
            'label' => 'Billing Address',
            'type' => 'text',
            'class' => 'form-control',
            'div_class' => 'col-lg-12 col-md-12 form-group',
            'required' => true,
            'placeholder' => 'Enter Billing Address',
        ],
        'f4' => [
            'label' => 'Terms and Conditions',
            'type' => 'text',
            'class' => 'form-control',
            'div_class' => 'col-lg-12 col-md-12 form-group',
            'required' => false,
            'placeholder' => 'On/Off Terms and Conditions',
        ],
        'f6' => [
            'label' => 'Price',
            'type' => 'number',
            'class' => 'form-control',
            'div_class' => 'col-lg-12 col-md-12 form-group',
            'placeholder' => 'Enter course price',
            'required' => true
        ],
        'f5' => [
            'label' => 'Course Level',
            'type' => 'text',
            'class' => 'form-control',
            'div_class' => 'col-lg-12 col-md-12 form-group',
            'rows' => 5,
            'required' => false,
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
        'data_source' => 'payment',
        'method_name' => 'get_by_id'
    ],

    // Layout Configuration
    'layout' => [
        'form_row_class' => 'row',
        'button_row_class' => 'row',
        'separator' => '<hr>',
        'main_column_class' => 'col-md-12'
    ],

    
];

include_once './page.php';
?>