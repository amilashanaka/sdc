<?php
include_once './header.php';

$list = $product->get_all_active()['data'];


?>

<?php include_once './navbar.php'; ?>

<?php include_once './sidebar.php'; ?>
<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <!-- Content Header (Page header) -->

    <?php
     $heading = 'Products';
    $page_title = 'List';

    include_once './page_header.php';
    ?>
    <!-- Main content -->
    <section class="content">
        <div class="row">
            <div class="col-12">
                <!-- /.card -->
                <div class="card">
                   
                        <div class="card-header">
                            <h3 class="card-title">
                                <div class="row">
                                    <div class="col6">
                                        <button type="button" class="btn btn-app" onclick="location.href = 'product'"><i class="fas fa-file"></i>New Product</button>
                                    </div>
                                </div>
                            </h3>
                        </div>
                
                    <!-- /.card-header -->
                    <div class="card-body">
                        <table id="example23" class="display nowrap table table-hover table-striped table-bordered" cellspacing="0" width="100%">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th><?= $sys['prod-f1'] ?></th>
                                    <th><?= $sys['prod-f2'] ?></th>
                                    <th>Created Date</th>


                                    <th style="width:3%; text-align: center;">Action</th>
                                </tr>
                            </thead>
                            <tfoot>
                                <tr>
                                    <th>#</th>
                                    <th><?= $sys['prod-f1'] ?></th>
                                    <th><?= $sys['prod-f2'] ?></th>
                                    <th>Created Date</th>


                                    <th style="width:3%; text-align: center;">Action</th>
                                </tr>
                            </tfoot>
                            <tbody>
                                <?php
                                $i = 1;
                                if ($list != null) {
                                    foreach ($list as $row) {
                                ?>
                                        <tr>
                                            <td><?= $i++; ?></td>
                                            <td><a href="product?id=<?= base64_encode($row['id']); ?>"><?= $row['f1'] ?></a></td>
                                            <td><?= $row['f2']; ?></td>

                                            <td><?= printTime($row['created_date']); ?></td>

                                            <td> <?php if ($row['status'] == '1') { ?><button type="button" id="btnm<?php echo $row['id']; ?>" class="btn btn-block btn-outline-danger btn-flat" onclick="delete_record('<?php echo $row['id']; ?>', 'product', 'id', 'blog_list');"><i class="fa fa-times" aria-hidden="true"></i></button> <?php } else { ?> <button type="button" id="btnm<?php echo $row['id']; ?>" class="btn btn-block btn-outline-success btn-flat" onclick="activate_record('<?php echo $row['id']; ?>', 'blogs', 'id', 'blog_list');"><i class="fa fa-check "></i></button> <?php } ?> </td>
                                        </tr>
                                <?php }
                                } ?>
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