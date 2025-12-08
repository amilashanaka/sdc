<?php
include_once './header.php';
include_once './controllers/index.php';
if( $user->get_all_users()['error']==null){$list = $user->get_all_users()['data'];}else{$list =null;}


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
                                    <th>Name</th>
                                    <th>Date Of Birth</th>
                                    <th>Status</th>
                              
                              
                                    <th style="width:3%; text-align: center;">Log Me</th>
                                </tr>
                            </thead>
                            <tfoot>
                                <tr>
                                    <th>#</th>
                                    <th>Name</th>
                                    <th>Date Of Birth</th>
                                    <th>Status</th>

                                    <th style="width:3%; text-align: center;">Log Me</th>
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
                                        <td><?= $row['f1'] ?></td>

                                        <td><?= printDate($row['f2']) ?></td>
                                        <td><?= $row['status'] ?></td>
                                    
                                        <td><?php if ($row['status'] == '1') { ?> <a href="log?u_id=<?= base64_encode($row['id']); ?>" type="button" class="btn btn-block btn-outline-success btn-flat"   ><i class="fa fa-plus"></i> </a> <?php } else { ?> <button type="button" id="btnm<?= $row['id']; ?>" class="btn btn-block btn-outline-danger btn-flat"  disabled><i class="fa fa-times"></i> </button> <?php } ?> </td>
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