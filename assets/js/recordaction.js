function delete_record(...args) {

  var id = args[0] !== undefined ? args[0] : 0;
  var tbl = args[1] !== undefined ? args[1] : "";
  var id_name = args[2] !== undefined ? args[2] : "";
  var href_ = args[3] !== undefined ? args[3] : "/";
  var re_direct_para = args[4] !== undefined ? args[4] : ""


  swal({
    title: "Are you sure?",
    text: "Do You want Deactivate  this Record",
    icon: "warning",
    buttons: ["No, cancel it!", "Yes, I am sure!"],
    dangerMode: true,
  }).then(function (isConfirm) {
    if (isConfirm) {
      swal({
        title: "Deactivated",
        text: "Sucessfully deactivated",
        icon: "success",
      }).then(function () {
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
          
            if (re_direct_para != "") {
              window.location.href = href_ + "?" + re_direct_para;
            } else {
              window.location.href = href_ ;
            }
       
          },
        });
      });
    } else {
      swal("Cancelled", " Record Not Deactivated", "error");
    }
  });
}

function activate_record(...args) {
  var id = args[0] !== undefined ? args[0] : 0;
  var tbl = args[1] !== undefined ? args[1] : "";
  var id_name = args[2] !== undefined ? args[2] : "";
  var href_ = args[3] !== undefined ? args[3] : "/"; 
  var re_direct_para = args[4] !== undefined ? args[4] : ""


  swal({
    title: "Are you sure?",
    text: "Do You want Activate this Record",
    icon: "warning",
    buttons: ["No, cancel it!", "Yes, I am sure!"],
    dangerMode: true,
  }).then(function (isConfirm) {
    if (isConfirm) {
      swal({
        title: "Activated",
        text: "Successfully Activated",
        icon: "success",
      }).then(function () {
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
        
            if (re_direct_para != "") {
              window.location.href = href_ + "?" + re_direct_para;
            } else {
              window.location.href = href_ ;
            }
         
          },
        });
      });
    } else {
      swal("Cancelled", "User Not Activated", "error");
    }
  });
}


function add_item(inv_id, item_id, qty, type) {
  swal({
    title: "Are you sure?",
    text: "Do You want Add this item",
    icon: "warning",
    buttons: ["No, cancel it!", "Yes, I am sure!"],
    dangerMode: true,
  }).then(function (isConfirm) {
    if (isConfirm) {
      swal({
        title: "Add On ",
        text: "Successfully Added",
        icon: "success",
      }).then(function () {
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
            $("#example23").load(location.href + " #example23");
          },
        });
      });
    } else {
      swal("Cancelled", "User Not Deactivated", "error");
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
  swal({
    title: "Are you sure?",
    text: "Do You want Add this item",
    icon: "warning",
    buttons: ["No, cancel it!", "Yes, I am sure!"],

    dangerMode: true,
  }).then(function (isConfirm) {
    if (isConfirm) {
      swal({
        title: "Add On ",
        text: "Successfully Added",
        icon: "success",
      }).then(function () {
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
            $("#example23").load(location.href + " #example23");
          },
        });
      });
    } else {
      swal("Cancelled", "User Not Deactivated", "error");
    }
  });
}
