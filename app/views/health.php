<?php
include_once __DIR__ . '/header.php';

$form_config = [
    'heading' => 'Health Parameter',
    'form_action' => BASE_URL . '/health/save',
    'method' => 'post',
    'page_config' => [
        'update_title_prefix' => 'Edit',
        'new_title_prefix' => 'Add New',
        'container_class' => 'container-fluid',
        'card_class' => 'card health-detail-card shadow-sm border-0',
        'card_body_class' => 'card-body p-4 p-lg-5'
    ],
    'inputs' => [
        'id' => ['type' => 'hidden', 'value' => ''],
        'f1' => [
            'label' => 'Parameter Name',
            'type' => 'text',
            'class' => 'form-control',
            'div_class' => 'col-12 form-group',
            'required' => true,
            'minlength' => 1,
            'maxlength' => 100,
            'placeholder' => 'Enter parameter name (e.g. FPGA Temperature)',
            'validation' => [
                'type' => 'pattern',
                'rules' => [
                    'pattern' => '^[a-zA-Z0-9_\-\s]{1,100}$'
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
            'label' => 'Description',
            'type' => 'textarea',
            'class' => 'form-control',
            'div_class' => 'col-12 form-group',
            'required' => false,
            'rows' => 4,
            'placeholder' => 'Enter parameter description',
        ],
        'f4' => [
            'label' => 'Value',
            'type' => 'number',
            'class' => 'form-control',
            'div_class' => 'col-12 form-group',
            'required' => true,
            'step' => '0.01',
            'placeholder' => 'Enter current value (e.g. 42.5)',
        ],
        'f5' => [
            'label' => 'Icon',
            'type' => 'text',
            'class' => 'form-control',
            'div_class' => 'col-12 form-group',
            'required' => false,
            'maxlength' => 100,
            'placeholder' => 'Enter icon class (e.g. fa-thermometer-half)',
        ],
        'f6' => [
            'label' => 'Icon Color',
            'type' => 'color',
            'class' => 'form-control',
            'div_class' => 'col-1 form-group',
            'required' => false,
            'maxlength' => 20,
            'placeholder' => 'Enter color (e.g. #ef4444 or text-danger)',
        ],
    ],
    'data_config' => [
        'id_param' => 'id',
        'data_source' => 'health',
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
            'create_text' => 'Add Parameter',
            'create_class' => 'btn btn-block btn-success',
            'create_icon' => 'fas fa-save',
            'update_text' => 'Update Parameter',
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