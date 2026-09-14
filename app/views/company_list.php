
<?php
include_once __DIR__ . '/header.php';
include_once __DIR__ . '/sidebar.php';
include_once __DIR__ . '/navbar.php';

$form_config = [
    'heading' => 'Company List',
    'title' => 'list',
    'new' => 'company',
    'model' => 'company', // Model/class name for data operations
    'method' => 'get_all', // Method to call on the model
    'table' => [
        'th' => ['#', 'Company Name', 'website', 'Action'],
        'action_style' => 'width:3%; text-align: center;',
        'id_column' => 'id',
        'columns' => [
            ['name' => '#', 'link' => false],  // ID column (counter)
            ['name' => 'f1', 'link' => true], // company Title column
            ['name' => 'f2', 'link' => false], // company website column


 
        ],
        'link_base' => 'company', // Base URL for links
        'table_id' => 'example23',
        'table_classes' => 'display nowrap table table-hover table-striped table-bordered',
        'table_attributes' => 'cellspacing="0" width="100%"',
        'card_classes' => 'card',
        'card_body_classes' => 'card-body',
        'card_header_classes' => 'card-header'
    ],
    'db_table' => 'companies',
    'redirect' => 'company_list',
    'buttons' => [
        'add_new' => [
            'text' => 'Add New',
            'class' => 'btn btn-app',
            'icon' => 'fas fa-file'
        ],
        'view' => ['show' => true],
        'edit' => ['show' => true],
        'delete' => ['show' => true]
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

include_once __DIR__ . '/page_list.php';

?>