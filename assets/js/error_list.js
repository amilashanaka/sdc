/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

function error_by_code(id, $message = null, $topic = null, $type = 0) {



  switch (id) {
    case 0:
      $m_type = "error";
      switch ($type) {
        case 0:
          $m_type = "error";
          break;
        case 1:
          $m_type = "warning";
          break;
        case 2:
          $m_type = "success";
          break;
        case 3:
          $m_type = "info";
          break;
      }

       toastr[$m_type]($message, $topic);
       break;
    case 1:
      toastr.success($message || 'Successfully updated', $topic || 'Update');

      break;
    case 2:
      toastr.warning("Password Miss Match", "Please check");

      break;
    case 3:
      toastr.error("Something Went Wrong", "Please check");

      break;
    case 4:
      toastr.success("Successfully Added", "Please check");
      break;

    case 5:
      toastr.warning("User Name Already Taken", "Please check");

      break;

    case 6:
      toastr.success("Bank Details Updated", "Please check");
      break;

    case 7:
      toastr.success("Password Updated", "Please check");
      break;
    case 8:
      toastr.error("Currency Name Already Exist ", "Please check");
      break;

    case 9:
      toastr.success("Successfully Transfer", "Sucess");
      break;
    case 10:
      toastr.error("Lotto Draw Number Exist", "Please check");
      break;
    case 11:
      toastr.warning("insufficient Balance", "please Request Credit");
      break;
    case 12:
      toastr.success("Successfully Transfer", "Please View Statements");
      break;

    case 13:
      toastr.success("Successfully Send", "Please View Statements");
      break;

    case 14:
      toastr.warning("insufficient Balance", "please Enter Less Amount");
      break;

    case 15:
      toastr.warning("Limit Exceeded", "please Enter Less Amount");
      break;
  }
}

function error_custome(id, $message, $topic) {

}

function update_message(body_txt) {
  toastr.success(body_txt, 'Successfully updated');
}
