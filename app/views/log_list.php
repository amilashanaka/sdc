<?php
include_once './header.php';

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
            ['name' => '#', 'link' => true],                     // Row counter (linked)
            ['name' => 'f1', 'link' => true],  // Host name (linked)              
            ['name' => 'f2', 'link' => false], // IP address (not linked)          
            ['name' => 'f3', 'link' => false], // Firmware version (not linked)
            ['name' => 'created_date', 'link' => false, 'format' => 'datetime'],

        ],
        'link_base'         => 'log',                             // Base URL for links
        'table_id'          => 'example23',
        'table_classes'     => 'display nowrap table table-hover table-striped table-bordered',
        'table_attributes'  => 'cellspacing="0" width="100%"',
        'card_classes'      => 'card',
        'card_body_classes' => 'card-body',
        'card_header_classes' => 'card-header'
    ],
    'db_table'   => 'logs',                  // Actual database table name
    'redirect'   => 'log_list',                // Redirect page after actions
    'buttons'    => [
        'add_new' => [
            'text'  => 'Add New',
            'class' => 'btn btn-app',
            'icon'  => 'fas fa-file'
        ],
        'delete' => [
            'class'    => 'btn btn-block btn-outline-danger btn-flat',
            'icon'     => 'fa-trash',
            'function' => 'delete_record'
        ],
        'activate' => [
            'class'    => 'btn btn-block btn-outline-success btn-flat',
            'icon'     => 'fa-check',
            'function' => 'activate_record'
        ]
    ],
    'permission' => [
        'add_new' => $_SESSION['role'] < 3
    ],
    'layout' => [
        'content_wrapper_class' => 'content-wrapper',
        'section_class'         => 'content',
        'row_class'             => 'row',
        'col_class'             => 'col-12',
        'card_title_class'      => 'card-title'
    ],
    'status' => [
        'active'   => '1',
        'inactive' => '0',
        'column'   => 'status'
    ]
];

include_once './page_list.php';
