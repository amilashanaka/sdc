
function viewImg(sr) {
  
    $('#mimg').attr('src', sr);
    $('#myModal').modal('show');
}

function searchTrading() 
{
    
    var pdrawno = $("#drawno").val();
   $("#pdrawno").val(pdrawno);
   

    $("#winloto_search").submit();

}

function payTradingSelected(st) {

    if ($('#pay_trading input[type=checkbox]:checked').length) {

        var pstatus;
        pstatus = 1;


        if (pstatus !== null) {
            $("#pay_trading").submit();
        }

    } else {

        alert("Select at least one payout");
        return false;

    }

}


function toggle(source) {
    checkboxes = document.getElementsByName('trade[]');
    for (var i = 0, n = checkboxes.length; i < n; i++) {
        checkboxes[i].checked = source.checked;
    }
}


function logout() {
	showConfirm("Are you sure? Logging Out", {
		icon: '<i class="fas fa-sign-out-alt text-warning"></i>',
		confirmClass: 'btn-warning',
		confirmText: 'Yes, Logout!',
		title: 'Logout'
	}).then(function(isConfirm) {
		if(isConfirm) {
			window.location = 'data/logout.php';
		}
	});
}





 
