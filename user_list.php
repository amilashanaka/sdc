<?php
include_once './header.php';
$form_config = [
    'heading' => 'Client List',
    'title' => 'list',
    'new' => 'user',
    'table' => [
        'th' => ['Email','Client Name','Date of Birth','Contact Number','Action'],
        'fields' => ['f1', 'f4', 'f5' , 'f3'], // Fields to display in each column
        'id_field' => 'id', // Field used for ID
        'link_field' => 'f1', // Field to use as link text
        'action_width' => '3%', // Width of action column
        'action_align' => 'center' // Alignment of action column
    ],
    'db_table' => 'users',
    'redirect' => 'user_list',
    'buttons' => [
        'add' => [
            'text' => 'Add New',
            'icon' => 'fas fa-file',
            'class' => 'btn btn-app'
        ],
        'delete' => [
            'class' => 'btn btn-block btn-outline-danger btn-flat',
            'icon' => 'fa fa-times'
        ],
        'activate' => [
            'class' => 'btn btn-block btn-outline-success btn-flat',
            'icon' => 'fa fa-check'
        ]
    ],
    'levels' => [
        1 => 'New',
        2 => 'Middle',
        3 => 'Top'
    ]
];
    if (isset($_GET['level'])) {
        $level =$_GET['level'];
    } else {
        $level = 1;
    }
    if ($user->get_user_by_level($level)['error'] == null) {
        $list = $user->get_user_by_level($level)['data'];
    } else {
        $list = $user->get_all_active()['data'];
    }
    ?>
<?php include_once './navbar.php'; ?>
<?php include_once './sidebar.php'; ?>
 <!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
     <!-- Content Header (Page header) -->
     <?php
        // Set page title based on level
        $level_prefix = isset($form_config['levels'][$level]) ? $form_config['levels'][$level] : '';
        $page_title = $level_prefix ? $level_prefix . ' ' . $form_config['title'] : $form_config['title'];
        
        $heading = $form_config['heading'];
        include_once './page_header.php';
        ?>
     <!-- Main content -->
     <section class="content">
         <div class="row">
             <div class="col-12">
                 <!-- /.card -->
                 <div class="card">
                     <?php if ($_SESSION['role'] < 3) { ?>
                         <div class="card-header">
                             <h3 class="card-title">
                             <button type="button" class="<?= $form_config['buttons']['add']['class'] ?>" onclick="location.href ='<?= $form_config['new'] ?>.php?level=<?= base64_encode($level) ?>'">
                                    <i class="<?= $form_config['buttons']['add']['icon'] ?>"></i><?= $form_config['buttons']['add']['text'] ?>
                                </button>
                             </h3>
                         </div>
                     <?php } ?>
                     <!-- /.card-header -->
                     <div class="card-body">
                     <table id="example23" class="display nowrap table table-hover table-striped table-bordered" cellspacing="0" width="100%">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <?php foreach ($form_config['table']['th'] as $header) {
                                        echo $header === 'Action' ?
                                           '<th style="width:' . $form_config['table']['action_width'] . '; text-align: ' . $form_config['table']['action_align'] . ';">' . $header . '</th>' :
                                           '<th>' . $header . '</th>';
                                    } ?>
                                </tr>
                            </thead>
                            <tfoot>
                                <tr>
                                    <th>#</th>
                                    <?php foreach ($form_config['table']['th'] as $header) {
                                        echo $header === 'Action' ?
                                           '<th style="width:' . $form_config['table']['action_width'] . '; text-align: ' . $form_config['table']['action_align'] . ';">' . $header . '</th>' :
                                           '<th>' . $header . '</th>';
                                    } ?>
                                </tr>
                            </tfoot>
                            <tbody>
                                <?php if ($list) {
                                    $i = 1;
                                    foreach ($list as $row) { ?>
                                       <tr>
                                           <td><?= $i++; ?></td>
                                           <?php
                                           // First column is a link to the detail page
                                           $id_field = $form_config['table']['id_field'];
                                           $link_field = $form_config['table']['link_field'];
                                           echo '<td><a href="' . $form_config['new'] . '?id=' . base64_encode($row[$id_field]) . '">' .
                                               htmlspecialchars($row[$link_field]) . '</a></td>';
                                           
                                           // Display the rest of the fields
                                           foreach (array_slice($form_config['table']['fields'], 1) as $field) {
                                               echo '<td>' . htmlspecialchars($row[$field] ?? '') . '</td>';
                                           }
                                           ?>
                                           <td>
                                               <?php if ($row['status'] == '1') { ?>
                                                   <button type="button" class="<?= $form_config['buttons']['delete']['class'] ?>"
                                                           onclick="delete_record('<?= $row[$id_field]; ?>', '<?= $form_config['db_table']; ?>', '<?= $id_field ?>', '<?= $form_config['redirect']; ?>');">
                                                       <i class="<?= $form_config['buttons']['delete']['icon'] ?>"></i>
                                                   </button>
                                               <?php } else { ?>
                                                   <button type="button" class="<?= $form_config['buttons']['activate']['class'] ?>"
                                                           onclick="activate_record('<?= $row[$id_field]; ?>', '<?= $form_config['db_table']; ?>', '<?= $id_field ?>', '<?= $form_config['redirect']; ?>');">
                                                       <i class="<?= $form_config['buttons']['activate']['icon'] ?>"></i>
                                                   </button>
                                               <?php } ?>
                                           </td>
                                       </tr>
                                <?php } } ?>
                            </tbody>
                        </table>
                     </div>
                     <!-- /.card-body -->
                 </div>
                 <!-- /.card -->
             </div>
             <!-- /.col -->
         </div>
         <!-- /.row -->
     </section>
     <!-- /.content -->
</div>
<?php include_once './footer.php'; ?>
</body>
</html>