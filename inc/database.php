<?php

/*
 * Database
 * 
 * The Database class is meant to simplify the task of accessing
 * information from the website's database.
 */

include_once '../controllers/config.php';  // Make sure this path is correct
include_once '../../phpmailer/PHPMailerAutoload.php';
include_once '../inc/functions.php';

class MySQLDB {

    var $connection;         // The MySQL database connection
    var $num_active_users;   // Number of active users viewing site
    var $num_active_guests;  // Number of active guests viewing site
    var $num_members;        // Number of signed-up users

    /* Class constructor */
    public function __construct() {
        // Make connection to database
        $this->connection = mysqli_connect(DB_NAME, DB_USER, DB_PASS);
        mysqli_select_db($this->connection, DB_NAME);
    }

    function query($query) {
        return mysqli_query($this->connection, $query);
    }

    function sp_query($query, $para) {
        return mysqli_query($this->connection, $query);  // Changed to mysqli_query for MySQL
    }

    function fetch_row($data) {
        return mysqli_fetch_assoc($data);
    }

    function fetch_set($data) {
        return mysqli_fetch_array($data, MYSQLI_ASSOC);
    }

    function num_rows($data) {
        return mysqli_num_rows($data);
    }

    function loadAllUsers($catky) {
        $arrtype = $this->query("SELECT u_id, u_name FROM users ORDER BY u_name");

        while ($typ = $this->fetch_set($arrtype)) {
            echo '<option value="' . $typ['u_id'] . '" ' . ($catky == $typ['u_id'] ? "selected" : "") . '>' . $typ['u_name'] . '</option>';
        }
    }

    function loadAllServices($catky) {
        $arrtype = $this->query("SELECT s_id, s_name FROM services ORDER BY s_name");

        while ($typ = $this->fetch_set($arrtype)) {
            echo '<option value="' . $typ['s_id'] . '" ' . ($catky == $typ['s_id'] ? "selected" : "") . '>' . $typ['s_name'] . '</option>';
        }
    }

    function loadAllProducts($catky) {
        $arrtype = $this->query("SELECT p_id, p_name FROM products ORDER BY p_name");

        while ($typ = $this->fetch_set($arrtype)) {
            echo '<option value="' . $typ['p_id'] . '" ' . ($catky == $typ['p_id'] ? "selected" : "") . '>' . $typ['p_name'] . '</option>';
        }
    }

    function loadVehicleByUser($catky) {
        $arrtype = $this->query("SELECT v_id, v_number FROM vehicles");

        while ($typ = $this->fetch_set($arrtype)) {
            echo '<option value="' . $typ['v_id'] . '" ' . ($catky == $typ['v_id'] ? "selected" : "") . '>' . $typ['v_number'] . '</option>';
        }
    }

    function loadAllCurrency($catky) {
        $arrtype = $this->query("SELECT cu_id, cu_name FROM currency WHERE cu_status=1 ORDER BY cu_name");

        while ($typ = $this->fetch_set($arrtype)) {
            echo '<option value="' . $typ['cu_id'] . '" ' . ($catky == $typ['cu_id'] ? "selected" : "") . '>' . $typ['cu_name'] . '</option>';
        }
    }

    function loadAdmins($catky) {
        $arrtype = $this->query("SELECT a_id, a_username FROM admins WHERE a_type > 1 ORDER BY a_id");

        while ($typ = $this->fetch_set($arrtype)) {
            echo '<option value="' . $typ['a_id'] . '" ' . ($catky == $typ['a_id'] ? "selected" : "") . '>' . $typ['a_username'] . '</option>';
        }
    }

    // function pkg_by_vehicles($u_id) {
    //     $arr_type = $this->query("SELECT * FROM pkg_sold WHERE u_id = '$u_id'");

    //     while ($typ = $this->fetch_set($arr_type)) {
    //         echo '<option value="' . $typ['pk_id'] . '" ' . ($u_id == $typ['pk_id'] ? "selected" : "") . '>' . get_package_name($typ['pk_id'], $this->connection) . '</option>';
    //     }
    // }
    // function get_package_name($package_id, $connection) {
    //     // Your query to get the package name from the database
    //     $query = "SELECT pk_name FROM packages WHERE pk_id = '$package_id'";
    //     $result = mysqli_query($connection, $query);
    //     $row = mysqli_fetch_assoc($result);
    //     return $row['pk_name'];  // Make sure this returns the package name
    // }

    function loaddepart() {
        $arrtype = $this->query("SELECT * FROM branch ORDER BY bra_name");

        while ($typ = $this->fetch_set($arrtype)) {
            echo '<option value="' . $typ['bra_id'] . '">' . $typ['bra_name'] . '</option>';
        }
    }

}

/* Create database connection */
$database = new MySQLDB;
?>