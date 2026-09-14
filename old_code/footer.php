<!-- Main Footer -->



<footer class="main-footer"> <strong>Copyright &copy; <?= date("Y") ?> <a href="https://tenxanalytix.com/"><?=  $setting->getSettings('f2') ?></a>.</strong> All rights reserved.
  <div class="float-right d-none d-sm-inline-block"> <b>Version</b> 3.0.2 </div>
</footer>
</div>

<!-- j querry  -->
<script src="assets/plugins/jquery/jquery.min.js"></script>



<!-- Bootstrap -->
<script src="assets/plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="assets/js/bootstrap.bundle.min.js"></script>

<!-- admin lte -->
<script src="assets/js/adminlte.js"></script>
<script src="assets/plugins/overlayScrollbars/js/jquery.overlayScrollbars.min.js"></script>
<script src="assets/js/dashboard2.js"></script>

<!-- chart -->
<script src="assets/plugins/chart.js/Chart.min.js"></script>

<!-- inputmask -->


<!-- validation -->

<script src="assets/js/validation.js" type="text/javascript"></script>


<!-- custom -->

<script src="assets/js/recordaction.js" type="text/javascript"></script>
<script src="assets/js/custom.min.js" type="text/javascript"></script>
<script src="assets/js/custom_admin.js" type="text/javascript"></script>


<!-- data table -->

<script src="assets/plugins/datatables/jquery.dataTables.min.js"></script>
<script src="assets/plugins/datatables-bs4/js/dataTables.bootstrap4.min.js"></script>
<script src="assets/plugins/datatables-responsive/js/dataTables.responsive.min.js"></script>
<script src="assets/plugins/datatables-responsive/js/responsive.bootstrap4.min.js"></script>
<script src="assets/plugins/datatables-buttons/js/dataTables.buttons.min.js"></script>
<script src="assets/plugins/datatables-buttons/js/buttons.bootstrap4.min.js"></script>
<script src="assets/plugins/jszip/jszip.min.js"></script>
<script src="assets/plugins/pdfmake/pdfmake.min.js"></script>
<script src="assets/plugins/pdfmake/vfs_fonts.js"></script>
<script src="assets/plugins/datatables-buttons/js/buttons.html5.min.js"></script>
<script src="assets/plugins/datatables-buttons/js/buttons.print.min.js"></script>
<script src="assets/plugins/datatables-buttons/js/buttons.colVis.min.js"></script>


<!-- Bootstrap Switch -->
<script src="assets/plugins/bootstrap-switch/js/bootstrap-switch.min.js"></script>

<!-- Select2 -->
<script src="assets/plugins/select2/js/select2.full.min.js"></script>

<!-- filepond -->


<script src="assets/plugins/filepond/filepond.min.js"></script>
<script src="assets/plugins/filepond/FilePondPluginFileValidateType.min.js"></script>
<script src="assets/plugins/filepond/FilePondPluginImageExifOrientation.min.js"></script>
<script src="assets/plugins/filepond/FilePondPluginImagePreview.min.js"></script>
<script src="assets/plugins/filepond/FilePondPluginImageCrop.min.js"></script>
<script src="assets/plugins/filepond/FilePondPluginImageResize.min.js"></script>
<script src="assets/plugins/filepond/FilePondPluginImageTransform.min.js"></script>
<script src="assets/plugins/filepond/filepondPluginFileValidateSize.min.js"></script>




 <!-- jQuery UI -->
<script src="assets/plugins/jquery-ui/jquery-ui.min.js"></script>

<!-- fullCalendar 2.2.5 -->
<script src="assets/plugins/moment/moment.min.js"></script>
<script src="assets/plugins/fullcalendar/main.js"></script>


<!-- summer note -->
<script src="assets/plugins/summernote/summernote-bs4.min.js"></script>

<script src="assets/plugins//country-code/js/intlTelInput-jquery.min.js"></script>

<script type="text/javascript">
  $(document).ready(function() {
    $('.summernote').summernote();



  });

  $("input[data-bootstrap-switch]").each(function() {
    $(this).bootstrapSwitch('state', $(this).prop('checked'));
  })
</script>

<script>
  $(function() {
    //Initialize Select2 Elements
    $('.select2').select2()

    //Initialize Select2 Elements
    $('.select2bs4').select2({
      theme: 'bootstrap4'
    })




  })
</script>

<script>
  $(function() {
    $("#example23").DataTable({
      "responsive": true,
      "lengthChange": false,
      "autoWidth": true,
      "buttons": [{
          extend: 'csv',
          className: 'btn-csv'
        },
        {
          extend: 'excel',
          className: 'btn-excel'
        },
        {
          extend: 'pdf',
          className: 'btn-pdf'
        },

        {
          extend: 'colvis',
          className: 'btn-colvis'
        }


      ]
    }).buttons().container().appendTo('#example23_wrapper .col-md-6:eq(0)');
    $('#example2').DataTable({
      "responsive": true,
      "lengthChange": false,
      "autoWidth": true,
      "info": true,
    });
  });
</script>

<script>
  $(document).ready(function() {
    const notificationIcons = <?php echo $notification_icons_json; ?>;
    // Function to fetch notifications
    function fetchNotifications() {


      $.ajax({
        url: './data/get_notifications.php',
        type: 'GET',
        dataType: 'json',
        success: function(response) {


          $('#msg-count').text(response['count'] + ' Notifications');
          $('#notfication-header').text(response['count']);


          // Clear existing notifications
          $('.dropdown-item.dynamic').remove();

          // Get the keys from the data object
          const keys = Object.keys(response['data']);
          $('.dropdown-item.dynamic').remove();
          $('.dropdown-divider.dynamic').remove();

          // Iterate over the keys to create notification items
          keys.forEach(key => {
            const count = response['data'][key].length;
            const iconClass = notificationIcons[key] || 'fas fa-info-circle';

            var notificationItem = $('<a>', {
              href: '#',
              class: 'dropdown-item dynamic',
              html: '<i class="' + iconClass + ' mr-2"></i>' + count + ' new ' + key + '<span class="float-right text-muted text-sm">just now</span>'
            });

            var divider = $('<div>', {
              class: 'dropdown-divider dynamic'
            });

            // Append the notification item and the divider to the dropdown menu
            $('#notification-aria').append(notificationItem).append(divider);
            console.log(key, response['data'][key]);
          });

        },
        error: function(xhr, status, error) {
          //  console.error('Error fetching notifications:', error);
        }
      });
    }


    // Fetch notifications initially
    fetchNotifications();

    // Fetch notifications every 30 seconds
    setInterval(fetchNotifications, 300000); // Adjust interval as needed
  });
</script>


<script>

function previewImage(formConfig) {
    // Loop through the inputs in formConfig
    for (const name in formConfig.inputs) {
        const input = formConfig.inputs[name];

        // Check if the input is a file type with image preview
        if (input.type === 'file' && input.accept.includes('image/')) {
            const fileInput = document.getElementById(name);
            const previewDiv = document.getElementById(`preview_${name}`);

            if (fileInput && previewDiv) {
                // Add change event listener to file input
                fileInput.addEventListener('change', function (event) {
                    const file = event.target.files[0];
                    if (file) {
                        const reader = new FileReader();
                        reader.onload = function (e) {
                            const previewImg = previewDiv.querySelector('img');
                            if (previewImg) {
                                previewImg.src = e.target.result; // Set preview image source
                            }
                        };
                        reader.readAsDataURL(file); // Read file as DataURL
                    }
                });
            }
        }
    }
}


</script>

