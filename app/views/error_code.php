<?php
include_once __DIR__ . '/header.php';

$form_config = [
    'heading' => 'Error Code',
    'form_action' => BASE_URL . '/error_code/save',
    'method' => 'post',
    'page_config' => [
        'update_title_prefix' => 'Edit',
        'new_title_prefix' => 'Add New',
        'container_class' => 'container-fluid',
        'card_class' => 'card error-code-card shadow-sm border-0',
        'card_body_class' => 'card-body p-4 p-lg-5'
    ],
    'inputs' => [
        'id' => ['type' => 'hidden', 'value' => ''],
        'code' => [
            'label' => 'Error Code',
            'type' => 'number',
            'class' => 'form-control',
            'div_class' => 'col-12 form-group',
            'required' => true,
            'min' => 0,
            'max' => 2147483647,
            'step' => '1',
            'placeholder' => 'Enter error code',
        ],
        'message' => [
            'label' => 'Message',
            'type' => 'textarea',
            'class' => 'form-control',
            'div_class' => 'col-12 form-group',
            'required' => true,
            'rows' => 5,
            'maxlength' => 250,
            'placeholder' => 'Enter error message',
        ],
    ],
    'data_config' => [
        'id_param' => 'id',
        'data_source' => 'error_code',
        'method_name' => 'find_by_id'
    ],
    'layout' => [
        'form_row_class' => 'row',
        'button_row_class' => 'row',
        'separator' => '',
        'main_column_class' => 'col-md-12'
    ],
    'buttons' => [
        'submit' => [
            'div_class' => 'col-md-3',
            'create_text' => 'Add Error Code',
            'create_class' => 'btn btn-block btn-success',
            'create_icon' => 'fas fa-save',
            'update_text' => 'Update Error Code',
            'update_class' => 'btn btn-block btn-primary',
            'update_icon' => 'fas fa-save',
            'loading_text' => 'Saving...',
            'loading_icon' => 'fas fa-spinner fa-spin'
        ],
        'reset' => [
            'show' => true,
            'div_class' => 'col-md-3',
            'text' => 'Clear',
            'class' => 'btn btn-block btn-warning',
            'icon' => 'fas fa-undo'
        ]
    ],
    'validation' => [
        'enabled' => true,
        'scroll_to_error' => true,
        'scroll_behavior' => 'smooth',
        'scroll_block' => 'center',
        'show_error_summary' => true,
        'bootstrap_validation_classes' => true,
    ],
    'ui' => [
        'card_classes' => 'card card-primary card-outline',
        'main_card_classes' => 'card',
        'alert_classes' => 'alert alert-{type} alert-dismissible fade show',
    ]
];

include_once __DIR__ . '/page.php';
