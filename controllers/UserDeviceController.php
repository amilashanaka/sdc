<?php

class UserDeviceController extends TableController
{



    public function __construct(Database $database)
    {
        $this->conn = $database->get_connection();
        $this->table = "user_devices";
        parent::__construct($database, $this->table);
    }

    public function getclientById($id): array
    {
        $result = $this->get_by_id($id);
        return $result;
    }


    public function getDeviceListByUserId($u_id): array
    {
        $result = array();

        $query = "SELECT * FROM $this->table WHERE user='$u_id'";
        $smt = $this->conn->query($query);

        $devices = $smt->fetch(PDO::FETCH_ASSOC);

        if ($devices == false) {

            $result['error'] = "device not found";
        } else {

            $result['devoices'] = $devices;
            $result['error'] = null;
        }

        $_SESSION['error'] = $result['error'];

        return $result;
    }
}
