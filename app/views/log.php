<?php
include_once __DIR__ . '/header.php';

$form_config = [
    'heading' => 'Log Details',
    'form_action' => '#',
    'method' => 'get',
    'page_config' => [
        'update_title_prefix' => 'View',
        'new_title_prefix' => 'View',
        'container_class' => 'container-fluid',
        'card_class' => 'card log-detail-card shadow-sm border-0',
        'card_body_class' => 'card-body p-4 p-lg-5'
    ],
    'inputs' => [
        'id' => ['type' => 'hidden', 'value' => ''],
        'f1' => [
            'label' => 'Priority',
            'type' => 'text',
            'class' => 'form-control log-detail-field',
            'div_class' => 'col-lg-6 col-md-6 form-group',
            'readonly' => true
        ],
        'f2' => [
            'label' => 'Message',
            'type' => 'textarea',
            'class' => 'form-control log-detail-field',
            'div_class' => 'col-12 form-group',
            'rows' => 4,
            'readonly' => true
        ],
        'error' => [
            'label' => 'Error Code',
            'type' => 'text',
            'class' => 'form-control log-detail-field',
            'div_class' => 'col-lg-6 col-md-6 form-group',
            'readonly' => true
        ],
        'module_name' => [
            'label' => 'Module',
            'type' => 'text',
            'class' => 'form-control log-detail-field',
            'div_class' => 'col-lg-6 col-md-6 form-group',
            'readonly' => true
        ],
        'created_date' => [
            'label' => 'Logged At',
            'type' => 'text',
            'class' => 'form-control log-detail-field',
            'div_class' => 'col-lg-6 col-md-6 form-group',
            'readonly' => true
        ],
        'status' => [
            'label' => 'Status',
            'type' => 'text',
            'class' => 'form-control log-detail-field',
            'div_class' => 'col-lg-6 col-md-6 form-group',
            'readonly' => true
        ],
    ],
    'data_config' => [
        'id_param' => 'id',
        'data_source' => 'log',
        'method_name' => 'get_by_id'
    ],
    'layout' => [
        'form_row_class' => 'row',
        'button_row_class' => 'row',
        'separator' => '',
        'main_column_class' => 'col-md-12'
    ]
];

include_once __DIR__ . '/page.php';
