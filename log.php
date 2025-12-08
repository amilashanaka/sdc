<?php include_once './header.php';
include_once './controllers/index.php';
$form_config = $log_page_elements;


if (isset($_GET['id'])) {
    $id = base64_decode($_GET['id']);
    $row = $log->get_by_id($id)['data'];
} else {
    $id = 0;
    $row = null;
}


if (isset($_GET['u_id'])) {
    $client = base64_decode($_GET['u_id']);
 
} else {
    $client = 0;
  
}



 

?>

<?php include_once './navbar.php'; ?>

<?php include_once './sidebar.php'; ?>
<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <?php
    $t1 =  $form_config['heading'];
    $t2 = 'Details';
    if ($id == 0) {
        $t2 = 'New' . " " . $t1;
    } else {

        $t2 = 'Update' . " " . $t1;
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
                                <form action="<?= $form_config['form_action'] ?>" class="templatemo-login-form" method="post" enctype="multipart/form-data" name="update_vehicles">
                                    <?php

                                    if ($id == 0) {

                                     
                                        echo '<input type="hidden" name="user" value="' . $client . '">';
                                    } else {

                                      
                                        echo '<input type="hidden" name="user" value="' . $client . '">';
                                    }
                                    ?>
     

                                    <?php include_once '../inc/input_generate.php';?>
                                    

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