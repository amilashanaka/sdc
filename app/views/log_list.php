<?php
include_once __DIR__ . '/header.php';
include_once __DIR__ . '/sidebar.php';
include_once __DIR__ . '/navbar.php';

$form_config = [
    'heading' => 'Log List',
    'title'   => 'list',
    'new'     => '',
    'model'   => 'log',         // Model/class name for data operations
    'method'  => 'get_all',     // Method to call on the model
    'table'   => [
        'th'             => ['#', 'Device Name', 'IP Address', 'Firmware Version', 'Date',  'Action'],
        'action_style'   => 'width:3%; text-align: center;',
        'id_column'      => 'id',
        'columns'        => [
            ['name' => '#', 'link' => false],                    // Row counter
            ['name' => 'f1', 'link' => true],                    // Device name
            ['name' => 'f2', 'link' => false],                   // IP address
            ['name' => 'f3', 'link' => false],                   // Firmware version
            ['name' => 'created_date', 'link' => false, 'format' => 'datetime'],

        ],
        'link_base'         => 'log',                             // Base URL for links
        'table_id'          => 'example23',
        'table_classes'     => 'display nowrap table table-hover table-striped table-bordered',
        'table_attributes'  => 'cellspacing="0" width="100%"',
        'card_classes'      => 'card',
        'card_body_classes' => 'card-body'
    ],
    'db_table'   => 'logs',                  // Actual database table name
    'redirect'   => 'log_list',                // Redirect page after actions
    'buttons'    => [
        'view' => [
            'show' => true,
            'class' => 'btn btn-sm btn-info',
            'icon' => 'fas fa-eye'
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
