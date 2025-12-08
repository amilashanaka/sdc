<?php include_once './header.php';

if (isset($_GET['id'])) {
    $id = base64_decode($_GET['id']);
} else {
    $id = 0;
}



if ($id > 0) {


    $sql = "select * from documents  where id='" . $id . "'";

    $result = mysqli_query($conn, $sql);
    $row = mysqli_fetch_assoc($result);
}
?>

<?php include_once './navbar.php'; ?>

<?php include_once './sidebar.php'; ?>
<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <?php
    $t1 =  'Blog';
    $t2 = 'Details';
    if ($id == 0) {
        $t2 = 'New' . " " . $t1;
    } else {

        $t2 = 'Update Blog';
    }
    include_once './page_header.php';
    ?>
    <!-- /.content-header -->

    <!-- Main content -->
    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-12">
                    <div class="card">


                        <div class="card-body">
                            <div>
                                <form action="data/register_blog.php" class="templatemo-login-form" method="post" enctype="multipart/form-data" name="update_vehicles">
                                    <?php
                                    if ($id == 0) {

                                        echo '<input type="hidden" name="action" value="register">';
                                        echo '<input type="hidden" name="created_dt" value="' . $today . '">';
                                        echo '<input type="hidden" name="created_by" value="' . $user_act . '">';
                                    } else {

                                        echo ' <input type="hidden" name="action" value="update">';
                                        echo ' <input type="hidden" name="id" value="' . $id . '">';
                                        echo '<input type="hidden" name="updated_dt" value="' . $today . '">';
                                        echo '<input type="hidden" name="updated_by" value="' . $user_act . '">';
                                    }
                                    ?>


                                    <div class="col-lg-12 col-md-12 form-group">
                                        <div class="row">
                                            <div class="col-lg-6 col-md-6">
                                                <div class="row form-group">
                                                    <div class="form-group">
                                                        <div class="user_image">
                                                            <?php if ($row['img'] == '') { ?>
                                                                <img name="img" id="img" src="./assets/img/photo1.png" class="bg-transparent profile_image" style="max-height:150px;width:auto">
                                                            <?php } else { ?>
                                                                <img name="img" id="img" src="../<?= $row['img']; ?>" class="transparent profile_image" style="max-height:150px;width:auto">
                                                            <?php } ?>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="input-group">
                                                    <input type="file" name="img_file" id="img_file" class="form-control" aria-describedby="inputGroupPrepend" style="display: none;align-content: center" />
                                                    <input type="button" style="width: 30%;" value="Browse" id="browse_image" class="btn btn-block btn-success" />
                                                </div>
                                            </div>
                                            <div class="col-lg-6 col-md-6 ">

                                                <div class="row form-group">
                                                    <div class="form-group">
                                                        <div class="user_image">
                                                            <?php if ($row['icon'] == '') { ?>
                                                                <img name="icon" id="icon" src="./assets/img/blogicon.png" class="bg-transparent profile_image" style="max-height:150px;width:auto">
                                                            <?php } else { ?>
                                                                <img name="icon" id="icon" src="../<?= $row['icon']; ?>" class="bg-transparent profile_image" style="max-height:150px;width:auto">
                                                            <?php } ?>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="input-group">
                                                    <input type="file" name="icon_file" id="icon_file" class="form-control" aria-describedby="inputGroupPrepend" style="display: none;align-content: center" />
                                                    <input type="button" style="width: 30%;" value="Browse" id="browse_icon" class="btn btn-block btn-success" />
                                                </div>

                                            </div>
                                        </div>
                                    </div>


                                    <div class="row form-group">

                                        <div class="col-lg-12 col-md-12 form-group">
                                            <label>Title</label>
                                            <input type="text" class="form-control" id="title" name="title" value="<?= $row['title']; ?>" required>
                                        </div>


                                    </div>

                                    <div class="row form-group">

                                        <div class="col-lg-12 col-md-12 form-group">
                                            <label>Sub Title</label>
                                            <input type="text" class="form-control"   name="subTitle" value="<?= $row['subTitle']; ?>" required>
                                        </div>


                                    </div>





                                    <h5 class="text-divider"> Content</span></h5>
                                    <div class="row form-group">
                                        <div class="col-lg-12 col-md-12 form-group">

                                            <textarea type="text" class="form-control  summernote" id="content" name="content"><?= $row['content']; ?></textarea>
                                        </div>



                                    </div>

                                    <hr>



                                    <div class="row form-group">
                                        <div class="col-lg-2 col-md-2 form-group">


                                            <?php
                                            if ($id > 0) {


                                                echo '<button type="submit" class="btn btn-block btn-outline-success">Update Now</button>';
                                            } else {


                                                echo '<button type="submit" class="btn btn-block btn-outline-secondary">ADD New</button>';
                                            }
                                            ?>



                                        </div>
                                        <div class="col-lg-2 col-md-2 form-group">
                                            <button type="reset" class="btn btn-block btn-outline-warning">Reset</button>
                                        </div>


                                    </div>

                                </form>
                            </div>
                            <!-- /.tab-content -->
                        </div><!-- /.card-body -->
                    </div>






                </div>

            </div>
            <!-- /.row -->
        </div><!-- /.container-fluid -->
    </section>
    <!-- /.content -->
</div>
<?php include_once './footer.php'; ?>


<script>
    $('#browse_image').on('click', function(e) {

        $('#img_file').click();
    });
    $('#img_file').on('change', function(e) {
        var fileInput = this;
        if (fileInput.files[0]) {
            var reader = new FileReader();
            reader.onload = function(e) {
                $('#img').attr('src', e.target.result);
            };
            reader.readAsDataURL(fileInput.files[0]);
        }
    });
</script>


<script>
    $('#browse_icon').on('click', function(e) {



        $('#icon_file').click();
    });
    $('#icon_file').on('change', function(e) {
        var fileInput = this;
        if (fileInput.files[0]) {
            var reader = new FileReader();
            reader.onload = function(e) {
                $('#icon').attr('src', e.target.result);
            };
            reader.readAsDataURL(fileInput.files[0]);
        }
    });
</script>



</body>

</html>