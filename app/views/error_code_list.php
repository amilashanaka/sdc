<?php
include_once __DIR__ . '/header.php';
include_once __DIR__ . '/sidebar.php';
include_once __DIR__ . '/navbar.php';

$form_config = [
    'heading' => 'Error Code List',
    'title' => 'list',
    'new' => 'error_code',
    'model' => 'ErrorCode',
    'method' => 'get_all',
    'table' => [
        'th' => ['#', 'Code', 'Message', 'Action'],
        'action_style' => 'width:3%; text-align: center;',
        'id_column' => 'id',
        'columns' => [
            ['name' => '#', 'link' => false],
            ['name' => 'code', 'link' => true],
            ['name' => 'message', 'link' => false],
        ],
        'link_base' => 'error_code',
        'table_id' => 'errorCodeListTable',
        'table_classes' => 'display nowrap table table-hover table-striped table-bordered',
        'table_attributes' => 'cellspacing="0" width="100%"',
        'card_classes' => 'card',
        'card_body_classes' => 'card-body'
    ],
    'db_table' => 'error_codes',
    'redirect' => 'error_code_list',
    'buttons' => [
        'add_new' => [
            'show' => true,
            'class' => 'btn btn-sm btn-primary',
            'icon' => 'fas fa-plus',
            'text' => 'Add New'
        ]
    ],
    'custom_buttons' => [
        'view' => [
            'url' => 'error_code?id={id}',
            'class' => 'btn btn-sm btn-info',
            'icon' => 'fas fa-eye',
            'title' => 'View'
        ],
        'edit' => [
            'url' => 'error_code?id={id}',
            'class' => 'btn btn-sm btn-warning',
            'icon' => 'fas fa-edit',
            'title' => 'Edit'
        ],
        'delete' => [
            'url' => 'error_code/delete/{id}',
            'class' => 'btn btn-sm btn-danger delete-btn',
            'icon' => 'fas fa-trash',
            'title' => 'Delete',
            'confirm' => true
        ]
    ],
    'exports' => [
        'csv' => [
            'className' => 'btn btn-secondary btn-sm',
            'text' => '<i class="fas fa-file-csv"></i> CSV',
            'title' => 'Error Code List Export'
        ],
        'pdf' => [
            'className' => 'btn btn-secondary btn-sm',
            'text' => '<i class="fas fa-file-pdf"></i> PDF',
            'title' => 'Error Code List Report',
            'orientation' => 'landscape',
            'pageSize' => 'A4'
        ],
        'print' => [
            'className' => 'btn btn-secondary btn-sm',
            'text' => '<i class="fas fa-print"></i> Print',
            'title' => 'Error Code List'
        ]
    ],
    'layout' => [
        'content_wrapper_class' => 'content-wrapper',
        'section_class' => 'content',
        'row_class' => 'row',
        'col_class' => 'col-12',
        'card_title_class' => 'card-title',
        'card_classes' => 'card',
        'card_body_classes' => 'card-body',
        'card_header_classes' => 'card-header'
    ]
];

include_once __DIR__ . '/page_list.php';
