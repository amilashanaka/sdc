<?php
include_once '../../../conn.php';
include_once '../../../inc/functions.php';

if (isset($_GET["pk_id"])) {
    $pk_id = intval($_GET["pk_id"]);
    $i = 1;
    $result = mysqli_query($conn, "SELECT * FROM `package_item` WHERE `pk_id`=$pk_id");
    while ($row = mysqli_fetch_array($result)) {
        if ($row['p_id'] > 0) {
            $name = get_product_name($row['p_id'], $conn);
            $item_code = get_product_code($row['p_id'], $conn);
        } else {
            $name = get_service_nsme($row['s_id'], $conn);
            $item_code = get_service_code($row['s_id'], $conn);
        }
        ?>
        <tr id="r_<?= $row["pkt_id"] ?>">
            <td><?= $i++ ?></td>
            <td><?= $item_code ?></td>
            <td><?= $name ?></td>
            <td><?= $row['int_qty'] ?></td>
            <td>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group row">
                                <input type="text" class="form-control" id=""  name="qty_new" placeholder="<?= $row['int_qty'] ?>" value="<?= $row['int_qty'] ?>">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group row">
                                <button type="button" class="btn btn-block btn-outline-primary btn-flat"> <i class="fa fa-upload" aria-hidden="true"></i></button>
                            </div>
                        </div>
                    </div>
            </td>
            <td><button type="button" class="btn btn-block btn-outline-danger btn-flat" onclick="deleteRow('<?= $row["pkt_id"] ?>')"> <i class="fa fa-trash" aria-hidden="true"></i></button>
            </td>
        </tr>
        <?php
    }
}