<?php
include_once __DIR__ . '/header.php';
include_once __DIR__ . '/sidebar.php';
include_once __DIR__ . '/navbar.php';

$form_config = [
    'heading' => 'Device Type List',
    'title'   => 'list',
    'new'     => 'device_type',
    'model'   => 'DeviceType',         // Model/class name for data operations
    'method'  => 'get_all',     // Method to call on the model
    'table'   => [
        'th'             => ['#', 'Name',  'Action'],
        'action_style'   => 'width:3%; text-align: center;',
        'id_column'      => 'id',
        'columns'        => [
            ['name' => '#', 'link' => false],                    // Row counter
            ['name' => 'name', 'link' => true],                    // Device name

        ],
        'link_base'         => 'device_type',                             // Base URL for links
        'table_id'          => 'example23',
        'table_classes'     => 'display nowrap table table-hover table-striped table-bordered',
        'table_attributes'  => 'cellspacing="0" width="100%"',
        'card_classes'      => 'card',
        'card_body_classes' => 'card-body'
    ],
    'db_table'   => 'device_types',                  // Actual database table name
    'redirect'   => 'device_type_list',                // Redirect page after actions
    'buttons'    => [
        'add_new' => [
            'show' => true,
            'class' => 'btn btn-sm btn-primary',
            'icon' => 'fas fa-plus',
            'text' => 'Add New'
        ],
        'view' => [
            'show' => true,
            'class' => 'btn btn-sm btn-info',
            'icon' => 'fas fa-eye'
        ],
        'edit' => [
            'show' => true,
            'class' => 'btn btn-sm btn-warning',
            'icon' => 'fas fa-edit'
        ],
        'delete' => [
            'show' => true,
            'class' => 'btn btn-sm btn-danger',
            'icon' => 'fas fa-trash'
        ],
        'toggle_status' => [
            'show' => true
        ]
    ],
    'exports' => [
        'csv' => [
            'className' => 'btn btn-secondary btn-sm',
            'text' => '<i class="fas fa-file-csv"></i> CSV',
            'title' => 'Log List Export'
        ],
        'pdf' => [
            'className' => 'btn btn-secondary btn-sm',
            'text' => '<i class="fas fa-file-pdf"></i> PDF',
            'title' => 'Log List Report',
            'orientation' => 'landscape',
            'pageSize' => 'A4'
        ],
        'print' => [
            'className' => 'btn btn-secondary btn-sm',
            'text' => '<i class="fas fa-print"></i> Print',
            'title' => 'Log List'
        ]
    ],
    'layout' => [
        'content_wrapper_class' => 'content-wrapper',
        'section_class'         => 'content',
        'row_class'             => 'row',
        'col_class'             => 'col-12',
        'card_title_class'      => 'card-title',
        'card_classes'          => 'card',
        'card_body_classes'     => 'card-body',
        'card_header_classes'   => 'card-header'
    ],
    'status' => [
        'active'   => '1',
        'inactive' => '0',
        'column'   => 'status'
    ]
];

include_once __DIR__ . '/page_list.php';
