<?php
include_once __DIR__ . '/header.php';
include_once __DIR__ . '/sidebar.php';
include_once __DIR__ . '/navbar.php';

$form_config = [
    'heading' => 'Module List',
    'title'   => 'list',
    'new'     => 'module',
    'model'   => 'Module',         // Model/class name for data operations
    'method'  => 'get_all',     // Method to call on the model
    'table'   => [
        'th'             => ['#', 'Icon', 'Name', 'Sequence', 'Base Address', 'Action'],
        'action_style'   => 'width:3%; text-align: center;',
        'id_column'      => 'id',
        'columns'        => [
            ['name' => '#', 'link' => false],                    // Row counter
            ['name' => 'f6', 'link' => false, 'type' => 'icon'], // Icon
            ['name' => 'f1', 'link' => true],                    // Module name
            ['name' => 'f2', 'link' => false],                   // Sequence
            ['name' => 'f5', 'link' => false],                   // Base Address
        ],
        'link_base'         => 'module',                             // Base URL for links
        'table_id'          => 'example23',
        'table_classes'     => 'display nowrap table table-hover table-striped table-bordered',
        'table_attributes'  => 'cellspacing="0" width="100%"',
        'card_classes'      => 'card',
        'card_body_classes' => 'card-body'
    ],
    'db_table'   => 'modules',                  // Actual database table name
    'redirect'   => 'module_list',                // Redirect page after actions
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
        ]
    ],
    'exports' => [
        'csv' => [
            'className' => 'btn btn-secondary btn-sm',
            'text' => '<i class="fas fa-file-csv"></i> CSV',
            'title' => 'Module List Export'
        ],
        'pdf' => [
            'className' => 'btn btn-secondary btn-sm',
            'text' => '<i class="fas fa-file-pdf"></i> PDF',
            'title' => 'Module List Report',
            'orientation' => 'landscape',
            'pageSize' => 'A4'
        ],
        'print' => [
            'className' => 'btn btn-secondary btn-sm',
            'text' => '<i class="fas fa-print"></i> Print',
            'title' => 'Module List'
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