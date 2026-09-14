<?php
include_once './header.php';



$form_config = [
    'heading' => 'Admin List',
    'title' => 'list',
    'new' => 'admin',
    'table' => ['th' => ['User Name','Name', 'e-mail', 'Address','Mobile number','Join Date','Action']],
    'tbl' => 'admins',
    'redirect' => 'admin_list?role=Mg==',


];

if (isset($_GET['role'])) {
    $role = base64_decode($_GET['role']);
   
    if ($admin->get_admins_by_role($role)['error'] == null) {
       
        $list = $admin->get_admins_by_role($role)['admin'];
    } else {
        $list = null;
    }
} else {
    $role = 0;
    $list = null;
}

 $role= base64_encode($role);
 $role_path = "?role=$role";
?>


<?php include_once './navbar.php'; ?>

<?php include_once './sidebar.php'; ?>
<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <?php
        $heading = $form_config['heading'];
        $page_title = $form_config['title'];
        include_once './page_header.php';
    ?>
    <!-- /.content-header -->

    <!-- Main content -->
    <section class="content">
        <div class="row">
            <div class="col-12"> 
                <!-- /.card -->            

                <div class="card">
                    <?php // if(abs($a_type-$_SESSION['login_type'])==1){ 
                    ?>
                    <div class="card-header">
                        <h3 class="card-title">
                        <button type="button" class="btn btn-app" onclick="location.href ='<?= $form_config['new']. $role_path ?>'">
                                    <i class="fas fa-file"></i>Add New
                            </button>
                        </h3>
                    </div>
                    <?php //}
                    ?>
                    <!-- /.card-header -->
                    <div class="card-body">
                    <?php
                        if ($form_config['table'] != null) {
                        ?>

                            <table id="example23" class="display nowrap table table-hover table-striped table-bordered" cellspacing="0" width="100%">
                                <thead>
                                    <tr>

                                        <th>#</th>
                                        <?php foreach ($form_config['table']['th'] as $header) {
                                        echo $header === 'Action' ? '<th style="width:3%; text-align: center;">' . $header . '</th>' : '<th>' . $header . '</th>';
                                    } ?>


                                    </tr>
                                </thead>
                                <tfoot>
                                    <tr>
                                         <th>#</th>
                                        <?php foreach ($form_config['table']['th'] as $header) {
                                        echo $header === 'Action' ? '<th style="width:3%; text-align: center;">' . $header . '</th>' : '<th>' . $header . '</th>';
                                         } ?>
                                    </tr>
                                 </tfoot>
                                
                                <tbody>

                                    <?php
                                    $i = 1;
                                    if ($list != null) {
                                        foreach ($list as $row) {
                                    ?>
                                            <tr>
                                                <td><?php echo $i++; ?></td>
                                                <td><a href="<?= $form_config['new'] ?>?id=<?= base64_encode($row['id']); ?>"><?= $row['f2']; ?></a></td>
                                                <td><?= $row['f6']; ?></td>
                                                <td><?= $row['f9']; ?></td>
                                                <td><?= $row['f10']; ?></td>
                                                <td><?= $row['f8']; ?></td>
                                                <td><?= printDate($row['created_date']); ?></td>

                                               
                                            <td> 
                                                <?php if ($row['status'] == '1') { ?>
                                                    <button type="button" class="btn btn-block btn-outline-danger btn-flat" onclick="delete_record('<?= $row['id']; ?>', '<?= $form_config['tbl']; ?>', 'id', '<?= $form_config['redirect']; ?>');">
                                                        <i class="fa fa-times"></i>
                                                    </button>
                                                <?php } else { ?>
                                                    <button type="button" class="btn btn-block btn-outline-success btn-flat" onclick="activate_record('<?= $row['id']; ?>', '<?= $form_config['tbl']; ?>', 'id', '<?= $form_config['redirect']; ?>');">
                                                        <i class="fa fa-check"></i>
                                                    </button>
                                                <?php } ?>

                                            </tr>
                                    <?php }
                                    } ?>

                                </tbody>
                            </table>


                        <?php

                        }

                        ?>
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