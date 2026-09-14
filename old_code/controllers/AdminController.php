<?php

class AdminController 
{
    private $conn;
    private $table;

    public function __construct(Database $database)
    {
        $this->conn = $database->get_connection();
        $this->table = "admins";
    }

    public function getAdminById($id): array
    {
        $result = array();

        $query = "SELECT * FROM $this->table WHERE id='$id'";
        $smt = $this->conn->query($query);
        $admin = $smt->fetch(PDO::FETCH_ASSOC);

        if ($admin == false) {

            $result['error'] = "user not found";
        } else {
            unset($admin['a_password']);
            $result['admin'] = $admin;
            $result['error'] = null;
        }
        return $result;
    }

    public function get_all_admin_users(): array
    {

        $result = array();

        $query = "SELECT * FROM $this->table WHERE id !=1";
        $smt = $this->conn->query($query);
        $admin = $smt->fetchAll(PDO::FETCH_ASSOC);

        if ($admin == false) {

            $result['error'] = "no user found";
        } else {

            $result['admin'] = $admin;
            $result['error'] = null;
        }
        return $result;
    }

    public function get_all_Carers(): array
    {
        $result = array(
            'data' => [],
            'errors' => null,
        );

        $query = "SELECT * FROM $this->table WHERE f1 =3";
        $smt = $this->conn->query($query);
        $data = $smt->fetchAll(PDO::FETCH_ASSOC);

        if ($data) {
            $result['data'] = $data;
            $result['error'] = null;
        } else {
            $result['error'] = "No data found";
        }
        return $result;
    }


    public function get_admins_by_role($role): array
    {
        $result = array();

        $query = "SELECT * FROM $this->table WHERE id !=1 and f1= $role";
        $smt = $this->conn->query($query);
        $admin = $smt->fetchAll(PDO::FETCH_ASSOC);

        if ($admin == false) {

            $result['error'] = "no user found";
        } else {

            $result['admin'] = $admin;
            $result['error'] = null;
        }

        return $result;
    }

    public function register($role, $username, $password, $name): array
    {
        $result = array();




        $hashed_password = password_hash($password, PASSWORD_DEFAULT);


        if ($this->check_username($username) || $this->check_staff_id($username)) {
            $result['error'] = "User already registered";
            $result['code'] = 400;
        } else {

            if ($role == 2) {
                $query = "INSERT INTO $this->table (f1, f2, f3, f6) VALUES (2,'$username', '$hashed_password','$name')";
                $smt = $this->conn->query($query);
            } else if ($role == 3) {
                $query = "INSERT INTO $this->table (f1, f4, f5, f6) VALUES (3,'$username', '$password','$name')";
                $smt = $this->conn->query($query);
            } else {

                $result['role'] = "Role not defiend";
            }



            if ($smt) {
                $result['message'] = "User registered successfully";
                $result['code'] = 200;
            } else {
                $result['error'] = "Registration failed";
                $result['code'] = 400;
            }
        }

        return $result;
    }


    public function check_username($username): bool
    {

        $query = "SELECT * FROM $this->table WHERE f2='$username'";
        $smt = $this->conn->query($query);

        $user = $smt->fetch(PDO::FETCH_ASSOC);

        if ($user == false) {
            return false;
        } else {
            return true;
        }
    }


    public function check_staff_id($username): bool
    {

        $query = "SELECT * FROM $this->table WHERE f4='$username'";
        $smt = $this->conn->query($query);

        $user = $smt->fetch(PDO::FETCH_ASSOC);

        if ($user == false) {
            return false;
        } else {
            return true;
        }
    }


    public function update($data): array
    {
        $result = array();
        $out = array();

        $id = $data['id'] ?? 0;




        if ($id > 0) {
            unset($data['id']);
            unset($data['f3']);
            unset($data['f2']);
            unset($data['f1']);
            unset($data['f4']);
            unset($data['f5']);

            $result = $this->getAdminById($id);




            if ($result['error'] == null) {

                $result = $result['admin'];

                unset($result['id']);
                unset($result['f1']);
                unset($result['f3']);
                unset($result['f2']);
                unset($result['f4']);
                unset($result['f5']);


                $update_fields = '';
                foreach ($data as $field => $value) {



                    if (array_key_exists($field, $result)) {

                        if ($result[$field] != $value) {
                            $update_fields .= " $field = '$value',";
                            $msg = "successfully updated";
                        } else {
                            $msg = "not change";
                        }

                        array_push($out, $field, $msg);
                    } else {

                        array_push($out, $field, "incorect input");
                        $result['error'] = "incorect input";
                    }
                }




                $update_fields = rtrim($update_fields, ',');
                $status = 0;
                $result['error'] = null;

                if (!empty($update_fields)) {

                    try {

                        $query = "UPDATE $this->table SET $update_fields WHERE id = '$id'";
                        $rows = $this->conn->query($query);
                        $status = $rows->rowCount();
                    } catch (PDOException $e) {
                        $result['error'] = $e->getMessage();
                    }
                }

                $result['message'] = $out;
                $result['status'] = $status;
            }
        }

        return $result;
    }


    public function get_name_by_id($id): string
    {
        if ($id > 0) {

            $query = "SELECT f6 FROM $this->table where id='$id'";
            $smt = $this->conn->query($query);

            $name = $smt->fetch(PDO::FETCH_ASSOC);


            return $name['f6'];
        } else {
            return "";
        }
    }

    function reset_passeord($role, $id, $password)
    {
        $result = array();
        $out = array();
        $msg_ = "";
       

        if ($role == 2) {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $query = "UPDATE $this->table SET f3 = '$hashed_password' WHERE id = '$id'";
            $msg_ = 'Password';
        } else if ($role == 3) {
            $pin = $password;
            $query = "UPDATE $this->table SET f5 = '$pin' WHERE id = '$id'";
            $msg_ = 'Pin';
        }


        try {
            $rows = $this->conn->query($query);
            $status = $rows->rowCount();

           
            array_push($out, $msg_, 'successfully updated');

            $result['message'] = $out;
            $result['status'] = $status;
            $result['error'] = null;
        } catch (PDOException $e) {
            $result['error'] = $e->getMessage();
            $result['message'] = null;
            $result['status'] = null;
        }

        return $result;
    }

    public function get_count(): int
    {
         

        $query = "SELECT COUNT(*) as tot FROM $this->table";
        $smt = $this->conn->query($query);
        $count = $smt->fetch(PDO::FETCH_ASSOC);

        if ($count) {
            return  $count['tot'];
            
        } else {
            return 0;
        }

 
    }
}
