<?php

class App
{
    private $controller = 'LoginController';
    private $method = 'index';
    private $params = [];

    public function __construct()
    {
        $url = $this->parseUrl();

        // Get base name from URL
        $baseName = !empty($url[0]) ? $url[0] : 'login';

        // Convert kebab-case/snake_case → PascalCase
        $controllerName = str_replace(['-', '_'], ' ', $baseName);
        $controllerName = ucwords($controllerName);
        $controllerName = str_replace(' ', '', $controllerName);

        $controllerClass = $controllerName . 'Controller';
        $controllerFile = APP . '/controllers/' . $controllerClass . '.php';

        if (file_exists($controllerFile)) {
            require_once $controllerFile;
            $this->controller = new $controllerClass();
            unset($url[0]);
        } else {
            // NEW: Handle missing controller with 404
            require_once APP . '/controllers/ErrorController.php';
            $this->controller = new ErrorController();
            $this->method = 'notFound';
        }

        // Method / action
        if (!empty($url[1])) {
            $this->method = $url[1];
            unset($url[1]);
        }

        // NEW: Check if method exists, fallback to 404 if not
        if (!method_exists($this->controller, $this->method)) {
            require_once APP . '/controllers/ErrorController.php';
            $this->controller = new ErrorController();
            $this->method = 'notFound';
        }

        $this->params = $url ? array_values($url) : [];

        call_user_func_array([$this->controller, $this->method], $this->params);
    }

    private function parseUrl()
    {
        if (isset($_GET['url'])) {
            return explode('/', filter_var(rtrim($_GET['url'], '/'), FILTER_SANITIZE_URL));
        }
        return [];
    }
}