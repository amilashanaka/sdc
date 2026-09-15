<?php
/**
 * BaseController
 * Parent controller with common functionality for all controllers
 */
class BaseController 
{
    /**
     * Load a view file with data
     * 
     * @param string $view - View name (without .php extension)
     * @param array $data - Associative array of data to pass to view
     * @return void
     */
    protected function view($view, $data = []) {
        // Extract data array to variables
        extract($data);
        
        // Build the view path
        $viewPath = VIEWS . "/$view.php";
        
        // Check if view file exists
        if (file_exists($viewPath)) {
            require $viewPath;
        } else {
            // View not found - show error
            die("View not found: $viewPath");
        }
    }
    
    /**
     * Check if user is authenticated
     * Redirect to login if not authenticated
     * 
     * @return void
     */
    protected function checkAuth() {
        if (!isset($_SESSION['user_id'])) {
            header('Location: ' . BASE_URL . '/login');
            exit;
        }
    }
    
    /**
     * Redirect to a specific URL
     * 
     * @param string $path - Path to redirect to
     * @return void
     */
    protected function redirect($path) {
        header('Location: ' . BASE_URL . '/' . ltrim($path, '/'));
        exit;
    }
    
    /**
     * Return JSON response
     * 
     * @param array $data - Data to encode as JSON
     * @param int $statusCode - HTTP status code
     * @return void
     */
    protected function json($data, $statusCode = 200) {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }
    
    /**
     * Get POST data
     * 
     * @param string $key - Key to get from POST
     * @param mixed $default - Default value if key doesn't exist
     * @return mixed
     */
    protected function post($key = null, $default = null) {
        if ($key === null) {
            return $_POST;
        }
        return $_POST[$key] ?? $default;
    }
    
    /**
     * Get GET data
     * 
     * @param string $key - Key to get from GET
     * @param mixed $default - Default value if key doesn't exist
     * @return mixed
     */
    protected function get($key = null, $default = null) {
        if ($key === null) {
            return $_GET;
        }
        return $_GET[$key] ?? $default;
    }
    
    /**
     * Sanitize input data
     * 
     * @param mixed $data - Data to sanitize
     * @return mixed
     */
    protected function sanitize($data) {
        if (is_array($data)) {
            return array_map([$this, 'sanitize'], $data);
        }
        return htmlspecialchars(strip_tags(trim($data)), ENT_QUOTES, 'UTF-8');
    }
    
    /**
     * Check if request is POST
     * 
     * @return bool
     */
    protected function isPost() {
        return $_SERVER['REQUEST_METHOD'] === 'POST';
    }
    
    /**
     * Check if request is GET
     * 
     * @return bool
     */
    protected function isGet() {
        return $_SERVER['REQUEST_METHOD'] === 'GET';
    }
    
    /**
     * Check if request is AJAX
     * 
     * @return bool
     */
    protected function isAjax() {
        return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
               strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    }
    
    /**
     * Get current user data
     * 
     * @return object|null
     */
    protected function getCurrentUser() {
        if (!isset($_SESSION['user_id'])) {
            return null;
        }
        
        $user = new User();
        return $user->find_by_id($_SESSION['user_id']);
    }
    
    /**
     * Set flash message
     * 
     * @param string $key - Message key (success, error, info, warning)
     * @param string $message - Message text
     * @return void
     */
    protected function setFlash($key, $message) {
        $_SESSION['flash'][$key] = $message;
    }
    
    /**
     * Get flash message and remove it
     * 
     * @param string $key - Message key
     * @return string|null
     */
    protected function getFlash($key) {
        if (isset($_SESSION['flash'][$key])) {
            $message = $_SESSION['flash'][$key];
            unset($_SESSION['flash'][$key]);
            return $message;
        }
        return null;
    }
}
