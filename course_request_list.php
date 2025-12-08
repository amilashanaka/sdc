<?php
include_once './header.php';

$form_config = [
    'heading' => 'Course Request List',
    'title' => 'list',
    'new' => 'payment',
    'model' => 'payment', // Model/class name for data operations
    'method' => 'get_all_with_delete', // Method to call on the model
    'table' => [
        'th' => ['#' , 'User','Amount', 'Package', 'Email', 'Action'],
        'action_style' => 'width:3%; text-align: center;',
        'id_column' => 'id',
        'columns' => [
            ['name' => '#', 'link' => true],  // ID column (counter)
            ['name' => 'f1','link' => true], // Amount column
            ['name' => 'f6',  'fk' => false ], 
            ['name' => 'f5',  'fk' => false ],
            ['name' => 'f2',  'fk' => false ]
        ],
        'link_base' => 'payment', // Base URL for links
        'table_id' => 'example23',
        'table_classes' => 'display nowrap table table-hover table-striped table-bordered',
        'table_attributes' => 'cellspacing="0" width="100%"',
        'card_classes' => 'card',
        'card_body_classes' => 'card-body',
        'card_header_classes' => 'card-header'
    ],
    'db_table' => 'payments',
    'redirect' => 'payment_list',
    'buttons' => [
        'add_new' => [
            'text' => 'Add New',
            'class' => 'btn btn-app',
            'icon' => 'fas fa-file'
        ],
        'delete' => [
            'class' => 'btn btn-block btn-outline-danger btn-flat',
            'icon' => 'fa-times',
            'function' => 'delete_record'
        ],
        'activate' => [
            'class' => 'btn btn-block btn-outline-success btn-flat',
            'icon' => 'fa-check',
            'function' => 'activate_record'
        ]
    ],
    'permission' => [
        'add_new' => $_SESSION['role'] < 3
    ],
    'layout' => [
        'content_wrapper_class' => 'content-wrapper',
        'section_class' => 'content',
        'row_class' => 'row',
        'col_class' => 'col-12',
        'card_title_class' => 'card-title'
    ],
    'status' => [
        'active' => '1',
        'inactive' => '0',
        'column' => 'status'
    ]
];
include_once './page_list.php';
?>