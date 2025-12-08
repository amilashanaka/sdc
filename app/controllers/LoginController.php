<?php
class LoginController {
    public function index() {
        // Check if already logged in
        if (isset($_SESSION['user_id'])) {
            $this->redirect('/dashboard');
        }

        $error = '';
        
        // Handle login
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $username = $_POST['username'] ?? '';
            $password = $_POST['password'] ?? '';
            
            $user = new User();
            $loggedInUser = $user->login($username, $password);
            
            if ($loggedInUser) {
                $_SESSION['user_id'] = $loggedInUser->id;
                $_SESSION['username'] = $loggedInUser->username;
                $this->redirect('/dashboard');
            } else {
                $error = 'Invalid credentials';
            }
        }
        
        $this->view('login', ['error' => $error]);
    }

    public function logout() {
        session_destroy();
        $this->redirect('/login');
    }

    private function view($view, $data = []) {
        extract($data);
        require VIEWS . "/$view.php";
    }

    private function redirect($url) {
        header('Location: ' . BASE_URL . $url);
        exit;
    }
}