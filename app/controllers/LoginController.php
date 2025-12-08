<?php

class LoginController 
{
    private Auth $auth;

    public function __construct()
    {
        $this->auth = new Auth();
    }

    public function index()
    {
        // already logged?
        if (!empty($_SESSION['user_id'])) {
            return $this->redirect('/dashboard');
        }

        $error = '';

        // handle login POST
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {

            $username = trim($_POST['username'] ?? '');
            $password = $_POST['password'] ?? '';

            $user = $this->auth->login($username, $password);



            if ($user) {
                $_SESSION['user_id']  = $user->id;
                $_SESSION['username'] = $user->username;

                return $this->redirect('/dashboard');
            }

            $error = 'Invalid username or password';
        }

        return $this->view('login', compact('error'));
    }



    public function logout()
    {
        $_SESSION = [];
        session_destroy();
        return $this->redirect('/login');
    }



    /** simple view helper */
    private function view($file, $data = [])
    {
        extract($data);
        require VIEWS . "/{$file}.php";
    }

    /** simple redirect helper */
    private function redirect(string $url)
    {
        header("Location: " . BASE_URL . $url);
        exit;
    }
}
