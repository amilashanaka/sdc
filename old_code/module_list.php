<?php
include_once './header.php';

$form_config = [
    'heading' => 'Keys List',
    'title'   => 'list',
    'new'     => 'key',
    'model'   => 'key',                     // Model/class name for data operations
    'method'  => 'get_all_with_delete',     // Method to call on the model
    'table'   => [
        'th'             => ['#', 'client',  'Issue Date', 'Status', 'Action'],
        'action_style'   => 'width:3%; text-align: center;',
        'id_column'      => 'id',
        'columns'        => [
            ['name' => '#', 'link' => true],                     // Row counter (linked)
            [
                'name'  => 'f2',                                 // Database column: user ID
                'link'  => true,                                 // Link to detail page
                'fk'    => true,                                 // Treat as foreign key
                'model' => 'user',                               // Related model name
                'show'  => 'f2'                                  // Field to display from user model (email)
            ],


                  // Image column (not linked)
            ['name' => 'created_date', 'link' => false, 'format' => 'date'], // Date column
            [
                'name' => 'status',
                'custom' => true,
                'type' => 'status_badge'
            ]
        ],
        'link_base'         => 'key',                             // Base URL for links
        'table_id'          => 'example23',
        'table_classes'     => 'display nowrap table table-hover table-striped table-bordered',
        'table_attributes'  => 'cellspacing="0" width="100%"',
        'card_classes'      => 'card',
        'card_body_classes' => 'card-body',
        'card_header_classes' => 'card-header'
    ],
    'db_table'   => 'skeys',                  // Actual database table name
    'redirect'   => 'key_list',                // Redirect page after actions
    'buttons'    => [
        'add_new' => [
            'text'  => 'Add New',
            'class' => 'btn btn-app',
            'icon'  => 'fas fa-file'
        ],
        'delete' => [
            'class'    => 'btn btn-block btn-outline-danger btn-flat',
            'icon'     => 'fa-times',
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
?>