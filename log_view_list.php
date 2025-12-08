<?php
include_once './header.php';
include_once './controllers/index.php';
if( $log->get_all()['error']==null){$list = $log->get_all_active()['data'];}else{$list =null;}


?>

<?php include_once './navbar.php'; ?>

<?php include_once './sidebar.php'; ?>
<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <!-- Content Header (Page header) -->

    <?php
    $t1 = 'Log';
    $t2 = 'List';
    
    // Set variables required by page_header.php
    $heading = $t1;
    $page_title = $t2;
    
    include_once './page_header.php';
    ?>
    <!-- Main content -->
    <section class="content">
        <div class="row">
            <div class="col-12">
                <!-- /.card -->
                <div class="card">
                    <?php if ($_SESSION['role'] >5) { ?>
                        <div class="card-header">
                            <h3 class="card-title">
                                <div class="row">
                                    <div class="col6">
                                        <button type="button" class="btn btn-app" onclick="location.href = 'log'"><i class="fas fa-file"></i><?= $sys['Add New'] ?></button>
                                    </div>
                                </div>
                            </h3>
                        </div>
                    <?php } ?>
                    <!-- /.card-header -->
                    <div class="card-body">
                        <table id="example23" class="display nowrap table table-hover table-striped table-bordered" cellspacing="0" width="100%">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Time Logged</th>
                                    <th>Title</th>
                                    <th>Client</th>
                                    <th>Log By</th>
                              
                              
                                    <th style="width:3%; text-align: center;">Change</th>
                                </tr>
                            </thead>
                            <tfoot>
                                <tr>
                                    <th>#</th>
                                    <th>Time Logged</th>
                                    <th>Title</th>
                                    <th>Client</th>
                                    <th>Log By</th>
                              

                                    <th style="width:3%; text-align: center;">Change</th>
                                </tr>
                            </tfoot>
                            <tbody>
                                <?php
                                $i = 1;
                                if($list!=null){
                                foreach ($list as $row) {
                                ?>
                                    <tr>
                                        <td><?= $i++; ?></td>
                                        <td><?= printDate($row['created_date']) ." ". printTime($row['created_date']) ?></td>
                                        <td><?= $row['f1'] ?></td>

                                        <td><?= $user->get_name_by_id ($row['user']) ?></td>
                                        <td><?= $admin->get_name_by_id ($row['staff']) ?></td>
                                    
                                        <td><?php if ($row['status'] == '1') { ?> <a href="log?id=<?= base64_encode($row['id']); ?>" type="button" class="btn btn-block btn-outline-success btn-flat"   ><i class="fa fa-edit"></i> </a> <?php } else { ?> <button type="button" id="btnm<?= $row['id']; ?>" class="btn btn-block btn-outline-danger btn-flat"  disabled><i class="fa fa-times"></i> </button> <?php } ?> </td>
                                    </tr>
                                <?php }} ?>
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