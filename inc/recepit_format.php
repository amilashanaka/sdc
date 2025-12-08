<?php

if (isset($_GET['in_id'])) {
    $in_id = base64_decode($_GET['in_id']);
} else {
    $in_id = 0;
}

$invoice_details = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM invoice WHERE in_id = '$in_id'"));
$u_id = $invoice_details['u_id'];
$u_name = (mysqli_fetch_assoc(mysqli_query($conn, "select u_name from users where `u_id`=$u_id"))["u_name"]);
$v_id = $invoice_details['v_id'];
$v_number = mysqli_fetch_assoc(mysqli_query($conn, "select * FROM vehicles WHERE v_id='$v_id'"));

$sql = "select * from invoice_item WHERE in_id=$in_id";
$result = mysqli_query($conn, $sql);

//$table = "";
$tot = 0;

while ($row = mysqli_fetch_assoc($result)) {
    $qty = $row['int_qty'];
    if ($row['p_id'] > 0) {
        $name = get_product_name($row['p_id'], $conn);
    } else {
        $name = get_service_nsme($row['s_id'], $conn);
    }
    $amt = $row['int_amount'];
    $sub_amt = $amt * $qty;
    $tot = $tot + $sub_amt;
    $table = ' <tr>
                            <td class="quantity">' . $qty . '</td>
                            <td class="description">' . $name . '</td>
                            <td class="price">' . number_format($amt, 2, '.', '') . '</td>
                            <td class="price">' . number_format($sub_amt, 2, '.', '') . '</td>
                        </tr>
                    ';
//    $date = get_invoice_Date($row['in_id'], $conn);
//    $invoiceNo = get_invoice_number($row['in_id'], $conn);
//    $u_name = get_username($in_id, $conn);
//    $v_number = get_vehicle_no($row['in_id'], $conn);
//    $v_mileage = get_vehicle_mileage($row['in_id'], $conn);
//    $pay_type = get_payment_type($row['in_id'], $conn);
}


$total_line = ' <tr>
                    <td class="total" colspan="3" >Total Grand</td>
                    <td class="price" >' . number_format($tot, 2, '.', '') . '</td>
                </tr>
                <tr>
                    <td class="total"  colspan="3" >Total Tendered</td>
                    <td class="price" >' . number_format($tot, 2, '.', '') . '</td>
                </tr>
                    ';

$html = '
<style>
* {
    font-size: 12px;
    font-family: \'Times New Roman\';
}


th,
tr,
table {
    border-top: 1px solid black;
    border-collapse: collapse;
}

td{
    height:30px;
    border-top: 1px dotted black;
    border-collapse: collapse;
}

td.description,
th.description {
    width: 95px;
    max-width: 95px;
    padding-left:10px;
    text-align:left;
}

td.quantity,
th.quantity {
    width: 40px;
    max-width: 40px;
    word-break: break-all;
    text-align:center;
}

td.price,
th.price {
    width: 60px;
    max-width: 60px;
    word-break: break-all;
    text-align:right;
}

.centered {
    text-align: center;
    align-content: center;
}

.ticket {
    width: 305px;
    max-width: 305px;
}

.center {
    margin-left: auto;
    margin-right: auto;
  }

.total{
     padding-left:50px;
     font-style:bold;
}

.double_line {
    border-bottom: 4px double #333;
    padding: 10px 0;
}

.line{
    margin-top: -10px;
}

.logo{
    height:70px;
    width:200px;
    margin-left:50px;
}

@media print {
    .hidden-print,
    .hidden-print * {
        display: none !important;
    }
}
</style>
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta http-equiv="X-UA-Compatible" content="ie=edge">
        <link rel="stylesheet" href="style.css">
        <title>Receipt example</title>
    </head>
    <body>
 
        <div class="ticket">
        <img src="https://baywash.com.my/admin/admin/img/logo.png" class="logo" alt="Logo"/>
            
        <p class="centered">BAYWASH AUTO-DETAILING SPECIALIST<br>CENTRE DA MEN SUBANG JAYA<br></p>
            <table style="width:100%;">              
                <tbody>
                    <tr>
                        <td style="width:50%;border: 1px solid white; height:5%;" class="aab">Date</td>
                        <td style="text-align:right; border: 1px solid white;">' . $invoice_details['in_date'] . '</td>
                    </tr>
                    <tr>
                        <td style="border: 1px solid white;">BAY</td>
                        <td style="text-align:right; border: 1px solid white;">17</td>
                    </tr>
                    <tr>
                        <td style="border: 1px solid white;">Opened By</td>
                        <td style="text-align:right; border: 1px solid white;">' . $u_name . '</td>
                    </tr>
                    <tr>
                        <td style="border: 1px solid white;">Payment Type</td>
                        <td style="text-align:right; border: 1px solid white;">' . $invoice_details['pay_type'] . '</td>
                    </tr>
                    <tr>
                        <td style="border: 1px solid white;">Reg No</td>
                        <td style="text-align:right; border: 1px solid white;">' . $v_number['v_number'] . '</td>
                    </tr>
                    <tr>
                        <td style="border: 1px solid white;">Mileage</td>
                        <td style="text-align:right; border: 1px solid white;">' . $v_number['v_mileage'] . ' km</td>
                    </tr>
                </tbody>
                
            </table>
            <p class="centered line" style="font-size: 16px; font-style: bold;">Invoice</p>
            <p class="centered line">(Receipt No: ' . $in_id . ')</p>
            <table class="center">
                <thead>
                    <tr>
                        <th class="quantity">QTY</th>
                        <th class="description">ITEM</th>
                        <th class="price">PRICE<br>(RM)</th>
                        <th class="price">TOTAL<br>(RM)</th>
                    </tr>
                </thead>                
                <tbody>' . $table . $total_line . '</tbody>
                
            </table>
            <br>
            <div class="double_line"></div>
            <br><br>
            
            <p class="centered">THANK YOU! See you again!..
                <br>Please like us on Facebook & Instagram 
                <br>#baywashmalaysia
                <br>HOTLINE: 010 200 4286 </p>
        </div>
   
    </body>
';
