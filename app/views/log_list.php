<?php
include_once __DIR__ . '/header.php';
include_once __DIR__ . '/sidebar.php';
include_once __DIR__ . '/navbar.php';

// Determine view mode (active or archived)
$view = $_GET['view'] ?? 'active';
$statusFilter = ($view === 'archived') ? 0 : 1;
$toggleView = ($view === 'archived') ? 'active' : 'archived';
$toggleText = ($view === 'archived') ? 'Show Active' : 'Show Archived';
$toggleIcon = ($view === 'archived') ? 'fas fa-list' : 'fas fa-archive';
$toggleClass = ($view === 'archived') ? 'btn btn-light btn-sm' : 'btn btn-outline-light btn-sm';

$form_config = [
    'heading' => 'Log List' . ($view === 'archived' ? ' - Archived' : ''),
    'title'   => 'list',
    'new'     => '',
    'model'   => 'Log',
    'method'  => 'get_all',
    'method_params' => [$statusFilter],
    'table'   => [
        'th'             => ['#', 'Time Stamp', 'Priority', 'Message', 'Module', 'Error Code', 'Action'],
        'action_style'   => 'width:3%; text-align: center;',
        'id_column'      => 'id',
        'columns'        => [
            ['name' => '#', 'link' => false],
            ['name' => 'created_date', 'link' => true, 'format' => 'datetime'],
            ['name' => 'f1', 'link' => false, 'type' => 'priority_badge'],
            ['name' => 'f2', 'link' => false],
        
            [
                'name'  => 'module',
                'link'  => false,
                'fk'    => true,
                'model' => 'module',
                'show'  => 'f1'
            ],
            ['name' => 'error', 'link' => false],
        ],
        'link_base'         => 'log',
        'table_id'          => 'logListTable',
        'table_classes'     => 'display nowrap table table-hover table-striped table-bordered',
        'table_attributes'  => 'cellspacing="0" width="100%"',
        'card_classes'      => 'card',
        'card_body_classes' => 'card-body'
    ],
    'db_table'   => 'logs',
    'redirect'   => 'log_list',
    'buttons'    => [
        'archive' => [
            'show' => true,
        ]
    ],
    'custom_buttons' => [
        'toggle_view' => [
            'header' => true,
            'url' => "log_list?view={$toggleView}",
            'class' => ($view === 'archived') ? 'btn btn-success btn-sm' : 'btn btn-warning btn-sm',
            'icon' => $toggleIcon,
            'text' => $toggleText
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
