<?php
include_once 'session.php';
include_once './inc/auth.php';
include_once './inc/functions.php';
include_once './controllers/index.php';
 

$favicon = isset($setting) && $setting->getSettings('img1') ? '../'.$setting->getSettings('img1') : 'assets/images/default-favicon.png';

 
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" href="<?= $favicon ?>" type="image/png">
    <title><?= $setting->getSettings('f1')?> </title>

    <!-- Theme style -->
    <link rel="stylesheet" href="assets/css/adminlte.css">
    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="assets/plugins/fontawesome/css/all.min.css">
    
    <!-- overlayScrollbars -->
    <link rel="stylesheet" href="assets/plugins/overlayScrollbars/css/OverlayScrollbars.min.css">


    <!--  Data Tables -->

    <link rel="stylesheet" href="assets/plugins/datatables-bs4/css/dataTables.bootstrap4.css">
    <link rel="stylesheet" href="assets/plugins/datatables-bs4/css/dataTables.bootstrap4.min.css">
    <link rel="stylesheet" href=".assets/plugins/datatables-responsive/css/responsive.bootstrap4.min.css">
    <link rel="stylesheet" href="assets/plugins/datatables-buttons/css/buttons.bootstrap4.min.css">


    <!-- fullCalendar -->
    <link rel="stylesheet" href="assets/plugins/fullcalendar/main.css">


    <!-- summer note -->
    <link href="assets/plugins/summernote/summernote-bs4.min.css" rel="stylesheet" type="text/css" />

    <!-- bootstrap -->
    <link href="assets/css/bootstrap.min.css" rel="stylesheet" type="text/css" />

    <!-- js -->

    <script src="assets/js/error_list.js" type="text/javascript"></script>

    <!-- Select2 -->
    <link rel="stylesheet" href="assets/plugins/select2/css/select2.min.css">
    <link rel="stylesheet" href="assets/plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css">


    <!--filepond -->
    <link rel="stylesheet" href="assets/plugins/filepond/filepond.min.css">
    <link rel="stylesheet" href="assets/plugins/filepond/FilePondPluginImagePreview.min.css">
    <link rel="stylesheet" href="assets/plugins/filepond/custom-filepond.css">
    <link rel="stylesheet" href="assets/css/show-password-toggle.css">


    <link rel="stylesheet" href="assets/plugins/country-code/css/intlTelInput.css">

    <link rel="stylesheet" href="assets/plugins/Country-Picker/niceCountryInput.css">
    <script src="assets/plugins/Country-Picker/niceCountryInput.js"></script>

    <!-- Toastr -->
    <link rel="stylesheet" href="assets/plugins/toastr/toastr.min.css">

    <link rel="stylesheet" href="assets/css/style.css">

 
</head>

<body class="hold-transition   sidebar-mini layout-fixed layout-navbar-fixed layout-footer-fixed">

    <?php




    if (!isset($_SESSION['login'])) {
        header('Location: index');
        exit();
    } else {

        $user_act =  $_SESSION['login'];
        $user_role = $_SESSION['role'];
        $today = date("Y-m-d H:i:s");


        $user_details = $admin->getAdminById($user_act);
 
    }


    ?>


    <?php
    if (isset($_GET['error'])) {
        $error = base64_decode($_GET['error']);
        echo '<script>
        function runToast() {
            if (typeof toastr !== "undefined") {
                error_by_code(' . $error . ');
            } else {
                setTimeout(runToast, 50);
            }
        }
        runToast();
        </script>';
    }

    if (isset($_GET['error_c'])) {
        $error_json = base64_decode(urldecode($_GET['error_c']));

        $error_data = json_decode($error_json, true);

        $id = $error_data['id'];
        $message = $error_data['message'];
        $topic = $error_data['topic'];
        $type = $error_data['type'];

        $js_message = json_encode($message);
        $js_topic = json_encode($topic);
        $js_type = json_encode($type);

        echo '<script>
        function runToast() {
            if (typeof toastr !== "undefined") {
                error_by_code(' . $id . ', ' . $js_message . ', ' . $js_topic . ', ' . $type . ');
            } else {
                setTimeout(runToast, 50);
            }
        }
        runToast();
        </script>';
    }


    if (isset($_GET['info'])) {

        $info = base64_decode($_GET['info']);

        echo '<script>
        function runToast() {
            if (typeof toastr !== "undefined") {
                update_message("' . $info . '");
            } else {
                setTimeout(runToast, 50);
            }
        }
        runToast();
        </script>';
    }
    ?>


    <div class="wrapper">

    <div class="modal fade" id="confirmModal" tabindex="-1" role="dialog" aria-labelledby="confirmModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="confirmModalLabel">Confirm</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <div class="modal-body text-center">
            <div id="confirmModalIcon" class="mb-3" style="font-size: 3rem;"></div>
            <p id="confirmModalBody">Are you sure?</p>
          </div>
          <div class="modal-footer justify-content-between">
            <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
            <button type="button" class="btn btn-danger" id="confirmModalOk">Yes, I am sure!</button>
          </div>
        </div>
      </div>
    </div>

    <style>
      #confirmModal .modal-dialog {
        transition: transform 0.25s ease-out, opacity 0.25s ease-out;
      }
      #confirmModal .modal-content {
        border-radius: 0.5rem;
        box-shadow: 0 10px 40px rgba(0,0,0,0.2);
      }
    </style>
