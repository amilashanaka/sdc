<?php

include_once '../../../conn.php';

if (isset($_GET["dataId"])) {
    $dataId = intval($_GET["dataId"]);
    if (mysqli_query($conn, "DELETE FROM `package_item` WHERE `pkt_id`=$dataId")) {
        echo 1;
    } else {
        echo 0;
    }
} else {
    echo 0;
}