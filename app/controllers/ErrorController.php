<?php

class ErrorController extends BaseController {
    public function notFound() {
        http_response_code(404); // Set HTTP status code
        $this->view('404'); // Or 'errors/404' if using a subfolder
    }
}