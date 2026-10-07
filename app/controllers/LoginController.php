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

                // Web session started: ask mode_manager.py to stop the RUN
                // services and start server.py (DEBUG). Only the flag is
                // written; a DB failure must never block the login.
                (new Flag())->setRunMode(Flag::DEBUG);

                return $this->redirect('/dashboard');
            }

            $error = 'Invalid username or password';
        }

        return $this->view('login', compact('error'));
    }



    public function logout()
    {
        // Web session ended: hand the device back to RUN mode.
        if (!empty($_SESSION['user_id'])) {
            (new Flag())->setRunMode(Flag::RUN);
        }

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
