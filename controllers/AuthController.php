<?php
class AuthController
{

    private $conn;


    public function __construct(Database $database)
    {
        $this->conn = $database->get_connection();
    }


    public function admin_login($username,  $password): array
    {
        $result = array();




        $query = "SELECT * FROM admins WHERE f2='$username'";
        $smt = $this->conn->query($query);

        $admin = $smt->fetch(PDO::FETCH_ASSOC);

        if ($admin == false) {

            $query = "SELECT * FROM admins WHERE f4='$username'";
            $smt = $this->conn->query($query);

            $admin = $smt->fetch(PDO::FETCH_ASSOC);

            if ($admin == false) {

                $result['error'] = "user not found";
                $result['code'] = 404;
            } else {

                if ($password == $admin['f5']) {
                    unset($admin['f5']);
                    $result['data'] = $admin;
                    $result['error'] = null;
                    $result['code'] = 200;
                } else {


                    $result['error'] = "wrong password";
                    $result['code'] = 401;
                }
            }
        } else {

            if (password_verify($password, $admin['f3'])) {
                unset($admin['f3']);

                $result['data'] = $admin;
                $result['error'] = null;
                $result['code'] = 200;
            } else {


                $result['error'] = "wrong password";
                $result['code'] = 401;
            }
        }

        return $result;
    }



    public function user_login($data)
    {

        $result = array();

        $device_id = $data[0];
        $user_mail = $data[1];


        $query = "SELECT * FROM  users  WHERE f1 ='$user_mail'";

        $smt = $this->conn->query($query);

        $user = $smt->fetch(PDO::FETCH_ASSOC);
 
        if ($user !==false) {

            $user_id =  $user['id'];
            $query = "SELECT f1 as device FROM  user_devices  WHERE user = '$user_id' and f1 = '$device_id'";
            $smt = $this->conn->query($query);
            $device = $smt->fetch(PDO::FETCH_ASSOC);

            if($device !== false) {
 
    
                    $result['user'] = $user;
                    $result['error'] = null;
                    $result['code'] = 200;
                    $result['device'] = $device['device'];
             
             
             

            }else{


                $result['user'] = $user;
                $result['code'] = 300;
                $result['error'] = "device not found";


            }


           
        }else{
            $result['user'] = null;
            $result['code'] = 400;
            $result['error'] = "user not found";
        }


        return $result;
    }
}
