
<?php
include_once './header.php';
 include_once './sidebar.php'; 

$form_config = [
    'heading' => 'Office List',
    'title' => 'list',
    'new' => 'office',
    'model' => 'office', // Model/class name for data operations
    'method' => 'get_all', // Method to call on the model
    'table' => [
        'th' => ['#', 'Office Name', 'Email', 'Country' ,'Company', 'Action'],
        'action_style' => 'width:3%; text-align: center;',
        'id_column' => 'id',
        'columns' => [
            ['name' => '#', 'link' => false],  // ID column (counter)
            ['name' => 'f1', 'link' => true], // Office Name column
            ['name' => 'f2', 'link' => false], // Email column
            ['name' => 'f5', 'link' => false], // Country column
            ['name' => 'company', 'link' => false, 'fk' => true, 'model' => 'company', 'show' => 'f1'], // Company Name (foreign key)        

 
        ],
        'link_base' => 'office', // Base URL for links
        'table_id' => 'example23',
        'table_classes' => 'display nowrap table table-hover table-striped table-bordered',
        'table_attributes' => 'cellspacing="0" width="100%"',
        'card_classes' => 'card',
        'card_body_classes' => 'card-body',
        'card_header_classes' => 'card-header'
    ],
    'db_table' => 'offices',
    'redirect' => 'office_list',
    'buttons' => [
        'add_new' => [
            'text' => 'Add New',
            'class' => 'btn btn-app',
            'icon' => 'fas fa-file'
        ],
        'delete' => [
            'class' => 'btn btn-block btn-outline-danger btn-flat',
            'icon' => 'fa-trash',
            'function' => 'delete_record'
        ],
        'activate' => [
            'class' => 'btn btn-block btn-outline-success btn-flat',
            'icon' => 'fa-check',
            'function' => 'activate_record'
        ]
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