<?php
 include_once 'header.php';
 include_once 'loader.php';
  include_once 'sidebar.php';
  include_once 'navbar.php';

$form_config = [
    'heading' => 'Product List',
    'title' => 'Products',
    'new' => 'Product',                    // for "Add New" button link
    'model' => 'product',                  // Model class name
    'method' => 'all',                  // Method name in Product model → $Product->all()
    
    'db_table' => 'Products',
    'redirect' => 'Product_list',          // after delete/restore etc.

    'table' => [
        'th' => ['#', 'Name', 'Email', 'Phone', 'Status', 'Action'],
        
        'action_style' => 'width: 120px; text-align: center;',
        'id_column' => 'id',
        
        'columns' => [
            ['name' => '#',         'link' => false],
            ['name' => 'f2',        'link' => false],     // Name
            ['name' => 'f1',        'link' => true],      // Email - clickable
            ['name' => 'f3',        'link' => false],     // Phone
            ['name' => 'status',    'link' => false, 'type' => 'status_badge'], // special rendering
        ],
        
        'link_base' => 'Product',                        // links → Product/5 , Product/10/edit etc.
        'table_id' => 'ProductsTable',
        'table_classes' => 'display nowrap table table-hover table-striped table-bordered',
        'table_attributes' => 'cellspacing="0" width="100%"',
    ],

    'buttons' => [
        'add_new' => [
            'text' => 'Add New Product',
            'class' => 'btn btn-primary',
            'icon' => 'fas fa-plus'
        ],
        'view' => [
            'class' => 'btn btn-sm btn-info',
            'icon' => 'fas fa-eye',
            'function' => 'view_record'   // optional - can be handled in page_list.php
        ],
        'edit' => [
            'class' => 'btn btn-sm btn-warning',
            'icon' => 'fas fa-edit',
        ],
        'delete' => [
            'class' => 'btn btn-sm btn-danger',
            'icon' => 'fas fa-trash',
            'function' => 'delete_record'
        ],
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
        'card_header_classes' => 'card-header bg-gradient-primary text-white',
        'card_body_classes' => 'card-body'
    ]
];

// Optional: You can override default card title if needed
$form_config['table']['card_title'] = 'All Registered Products';

include_once 'page_list.php';