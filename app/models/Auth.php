<?php

require_once 'User.php';

class Auth {

    public function login($username, $password) {
        
        $User = new User();

        // f1 = login
        $record = $User->whereFirst('f1', $username);

        if(!$record){
            return false;
        }

        // f3 = password
        if(!password_verify($password, $record->f3)) {
            return false;
        }

        $_SESSION['user_id'] = $record->id;

        return $record;
    }

    public function user(){
        if(!isset($_SESSION['user_id'])) return null;
        return (new User())->find_by_id($_SESSION['user_id']);
    }

    public function check(){
        return isset($_SESSION['user_id']);
    }

    public function logout(){
        unset($_SESSION['user_id']);
    }
}
