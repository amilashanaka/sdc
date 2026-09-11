function delete_record(...args) {

  var id = args[0] !== undefined ? args[0] : 0;
  var tbl = args[1] !== undefined ? args[1] : "";
  var id_name = args[2] !== undefined ? args[2] : "";
  var href_ = args[3] !== undefined ? args[3] : "/";
  var re_direct_para = args[4] !== undefined ? args[4] : ""


  showConfirm("Are you sure? Do you want to delete this record?", {
    icon: '<i class="fas fa-trash-alt text-danger"></i>',
    confirmClass: 'btn-danger',
    confirmText: 'Yes, Delete it!',
    title: 'Delete Record'
  }).then(function (isConfirm) {
    if (isConfirm) {
      var data = {
        id: id,
        tbl: tbl,
        id_name: id_name,
        action: "delete",
      };

      $.ajax({
        type: "POST",
        url: "./data/act_deact.php",
        dataType: "json",
        data: data,

        success: function (response) {
          if (response.error || (response.status && response.status !== 'success' && response.status !== 1)) {
            toastr.error(response.message || response.error || 'Unknown error');
            return;
          }

          toastr.success("Successfully Deleted");

          setTimeout(function() {
            if (re_direct_para != "") {
              window.location.href = href_ + "?" + re_direct_para;
            } else {
              window.location.href = href_;
            }
          }, 1500);

        },
      });
    }
  });
}

function deactivate_record(...args) {

  var id = args[0] !== undefined ? args[0] : 0;
  var tbl = args[1] !== undefined ? args[1] : "";
  var id_name = args[2] !== undefined ? args[2] : "";
  var href_ = args[3] !== undefined ? args[3] : "/";
  var re_direct_para = args[4] !== undefined ? args[4] : ""


  showConfirm("Are you sure? Do you want to deactivate this record?", {
    icon: '<i class="fas fa-times-circle text-warning"></i>',
    confirmClass: 'btn-warning',
    confirmText: 'Yes, Deactivate!',
    title: 'Deactivate Record'
  }).then(function (isConfirm) {
    if (isConfirm) {
      var data = {
        id: id,
        tbl: tbl,
        id_name: id_name,
        action: "deactivate",
      };

      $.ajax({
        type: "POST",
        url: "./data/act_deact.php",
        dataType: "json",
        data: data,

        success: function (response) {
          if (response.error || (response.status && response.status !== 'success' && response.status !== 1)) {
            toastr.error(response.message || response.error || 'Unknown error');
            return;
          }

          toastr.success("Successfully Deactivated");

          setTimeout(function() {
            if (re_direct_para != "") {
              window.location.href = href_ + "?" + re_direct_para;
            } else {
              window.location.href = href_;
            }
          }, 1500);

        },
      });
    }
  });
}

function activate_record(...args) {
  var id = args[0] !== undefined ? args[0] : 0;
  var tbl = args[1] !== undefined ? args[1] : "";
  var id_name = args[2] !== undefined ? args[2] : "";
  var href_ = args[3] !== undefined ? args[3] : "/"; 
  var re_direct_para = args[4] !== undefined ? args[4] : ""


  showConfirm("Are you sure? Do you want to activate this record?", {
    icon: '<i class="fas fa-check-circle text-success"></i>',
    confirmClass: 'btn-success',
    confirmText: 'Yes, Activate!',
    title: 'Activate Record'
  }).then(function (isConfirm) {
    if (isConfirm) {
      var data = {
        id: id,
        tbl: tbl,
        id_name: id_name,
        action: "activate",
      };

      $.ajax({
        type: "POST",
        url: "./data/act_deact.php",
        dataType: "json",
        data: data,
        success: function (response) {
          if (response.error || (response.status && response.status !== 'success' && response.status !== 1)) {
            toastr.error(response.message || response.error || 'Unknown error');
            return;
          }

          toastr.success("Successfully Activated");

          setTimeout(function() {
            if (re_direct_para != "") {
              window.location.href = href_ + "?" + re_direct_para;
            } else {
              window.location.href = href_;
            }
          }, 1500);

        },
      });
    }
  });
}


function add_item(inv_id, item_id, qty, type) {
  showConfirm("Are you sure? Do you want to add this item?", {
    icon: '<i class="fas fa-plus-circle text-success"></i>',
    confirmClass: 'btn-success',
    confirmText: 'Yes, Add!',
    title: 'Add Item'
  }).then(function (isConfirm) {
    if (isConfirm) {
      var data = {
        inv_id: inv_id,
        item_id: item_id,
        qty: qty,
        type: type,
      };

      $.ajax({
        type: "POST",
        url: "./data/addon.php",
        dataType: "json",
        data: data,

        success: function (data) {
          toastr.success("Successfully Added");
          $("#example23").load(location.href + " #example23");
        },
      });
    }
  });
}

function tesst_function(data){
  $.ajax({
    type: "POST",
    url: "./data/filter_data.php",
    dataType: "json",
    data: data,

    success: function (response) {
      console.log(response);
    },
  });
}

function add_service(inv_id, s_id, qty, type) {
  showConfirm("Are you sure? Do you want to add this item?", {
    icon: '<i class="fas fa-plus-circle text-success"></i>',
    confirmClass: 'btn-success',
    confirmText: 'Yes, Add!',
    title: 'Add Service'
  }).then(function (isConfirm) {
    if (isConfirm) {
      var data = {
        inv_id: inv_id,
        s_id: s_id,
        qty: qty,
        type: type,
      };

      $.ajax({
        type: "POST",
        url: "./data/addon.php",
        dataType: "json",
        data: data,

        success: function (data) {
          toastr.success("Successfully Added");
          $("#example23").load(location.href + " #example23");
        },
      });
    }
  });
}