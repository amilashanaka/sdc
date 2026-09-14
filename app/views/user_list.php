<?php
include_once 'header.php';
include_once 'loader.php';
include_once 'sidebar.php';
include_once 'navbar.php';

$form_config = [
    'heading' => 'User Management',
    'title' => 'Users',
    'new' => 'user',
    'model' => 'user',
    'method' => 'all',
    
    'db_table' => 'users',
    'redirect' => 'user_list',

    'table' => [
        'th' => ['#', 'Username', 'Email', 'Phone', 'Status', 'Actions'],
        
        'columns' => [
            ['name' => '#', 'link' => false],
            ['name' => 'f1', 'link' => true],
            ['name' => 'f2', 'link' => false],
            ['name' => 'f6', 'link' => false],
            ['name' => 'status', 'link' => false, 'type' => 'status_badge'],
        ],
        
        'link_base' => 'user',
        'id_column' => 'id',
        'table_id' => 'usersTable',
        'table_classes' => 'table table-hover table-striped table-bordered mt-3',
        'action_style' => 'width: 150px;',
    ],

    'buttons' => [
        'add_new' => [
            'text' => 'Add New User',
            'class' => 'btn btn-primary btn-sm',
            'icon' => 'fas fa-plus'
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
    ],

    'exports' => [
        'copy' => [
            'className' => 'btn btn-secondary btn-sm',
            'text' => '<i class="fas fa-copy"></i> Copy'
        ],
        'csv' => [
            'className' => 'btn btn-secondary btn-sm',
            'text' => '<i class="fas fa-file-csv"></i> CSV'
        ],
        'excel' => [
            'className' => 'btn btn-secondary btn-sm',
            'text' => '<i class="fas fa-file-excel"></i> Excel',
            'title' => 'User List Export'
        ],
        'pdf' => [
            'className' => 'btn btn-secondary btn-sm',
            'text' => '<i class="fas fa-file-pdf"></i> PDF',
            'orientation' => 'landscape',
            'pageSize' => 'A4',
            'title' => 'User List Report'
        ],
        'print' => [
            'className' => 'btn btn-secondary btn-sm',
            'text' => '<i class="fas fa-print"></i> Print'
        ],
        'colvis' => [
            'className' => 'btn btn-secondary btn-sm',
            'text' => '<i class="fas fa-columns"></i> Columns'
        ]
    ],

    'status' => [
        'active' => '1',
        'inactive' => '0',
        'column' => 'status',
        'badge_active' => 'bg-success',
        'badge_inactive' => 'bg-danger',
        'text_active' => 'Active',
        'text_inactive' => 'Inactive'
    ],

    'layout' => [
        'content_wrapper_class' => 'content-wrapper',
        'section_class' => 'content',
        'row_class' => 'row',
        'col_class' => 'col-12',
        'card_classes' => 'card shadow-sm',
        'card_header_classes' => 'card-header ',
        'card_body_classes' => 'card-body'
    ]
];

include_once 'page_list.php';