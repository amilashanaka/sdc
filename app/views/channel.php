<?php
include_once __DIR__ . '/header.php';

$form_config = [
    'heading' => 'Channel',
    'form_action' => BASE_URL . '/channel/save',
    'method' => 'post',
    'page_config' => [
        'update_title_prefix' => 'Edit',
        'new_title_prefix' => 'Add New',
        'container_class' => 'container-fluid',
        'card_class' => 'card channel-card shadow-sm border-0',
        'card_body_class' => 'card-body p-4 p-lg-5'
    ],
    'inputs' => [
        'id' => ['type' => 'hidden', 'value' => ''],
        'f1' => [
            'label' => 'Channel Name',
            'type' => 'text',
            'class' => 'form-control',
            'div_class' => 'col-12 form-group',
            'required' => true,
            'minlength' => 1,
            'maxlength' => 50,
            'placeholder' => 'Enter channel name',
            'validation' => [
                'type' => 'pattern',
                'rules' => [
                    'pattern' => '^[a-zA-Z0-9_\\-\\s]{1,50}$'
                ]
            ]
        ],
        'f2' => [
            'label' => 'Sequence',
            'type' => 'number',
            'class' => 'form-control',
            'div_class' => 'col-12 form-group',
            'required' => true,
            'min' => 0,
            'max' => 9999,
            'placeholder' => 'Enter display sequence',
        ],
        'f3' => [
            'label' => 'Decimation Factor',
            'type' => 'number',
            'class' => 'form-control',
            'div_class' => 'col-12 form-group',
            'required' => false,
            'min' => 0,
            'max' => 999999,
            'placeholder' => 'Enter decimation factor',
        ],
        'f4' => [
            'label' => 'Sample Rate',
            'type' => 'number',
            'class' => 'form-control',
            'div_class' => 'col-12 form-group',
            'required' => false,
            'min' => 0,
            'max' => 99999999,
            'placeholder' => 'Enter sample rate',
        ],
        'status' => [
            'label' => 'Status',
            'type' => 'select',
            'class' => 'form-control',
            'div_class' => 'col-12 form-group',
            'required' => true,
            'items' => [
                ['value' => '1', 'label' => 'Active'],
                ['value' => '0', 'label' => 'Inactive'],
            ],
        ],
    ],
    'data_config' => [
        'id_param' => 'id',
        'data_source' => 'channel',
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
            'create_text' => 'Add Channel',
            'create_class' => 'btn btn-block btn-success',
            'create_icon' => 'fas fa-save',
            'update_text' => 'Update Channel',
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