<?php include_once './header.php';
include_once './controllers/index.php';
$form_config = $document_page_element;


if (isset($_GET['file'])) {
    $file_path = base64_decode($_GET['file']);  
    $file_type = mime_content_type($file_path);
    $file_url =  'http://localhost/chms_admin/' . str_replace('../', '', $file_path);  // need to  hosting the document on a server that can be accessed from the internet
} else {
    $file_path = '';
}



?>

<?php include_once './navbar.php'; ?>

<?php include_once './sidebar.php'; ?>
<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <?php
    $t1 =  'File';
    $t2 =  'File';

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
                                <?php if ($file_path) : ?>
                                    <?php if (strpos($file_type, 'image') !== false) : ?>
                                        <img src="<?php echo $file_url; ?>" style="width: 100%; height: auto;" alt="Image">
                                    <?php elseif ($file_type == 'application/pdf') : ?>
                                        <iframe id="content-iframe" src="<?php echo $file_url; ?>" style="width: 100%; height: calc(100vh - 100px); border: none;"></iframe>
                                    <?php elseif (in_array($file_type, [
                                        'application/msword',
                                        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                                        'application/vnd.ms-excel',
                                        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
                                    ])) : ?>
                                        <iframe id="content-iframe" src="https://view.officeapps.live.com/op/embed.aspx?src=<?php echo urlencode($file_url); ?>" style="width: 100%; height: calc(100vh - 100px); border: none;"></iframe>
                                    <?php else : ?>
                                        <p>Unsupported file type.</p>
                                    <?php endif; ?>
                                <?php else : ?>
                                    <p>No file selected or invalid file path.</p>
                                <?php endif; ?>
                            
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



</body>

</html>