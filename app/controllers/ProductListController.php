<?php

class ProductListController
{
    public function __construct()
    {
        $this->checkAuth();
    }

    public function index()
    {
        $product = new Product();

        // Get all products (uses Base::all())
        $products = $product->all();

        // Load view
        $this->view('product_list', [
            'products' => $products,
            'title' => 'Product List'
        ]);
    }

    /**
     * Auth protection (same logic as DashboardController)
     */
    private function checkAuth()
    {
        if (!isset($_SESSION['user_id'])) {
            header('Location: /login');
            exit;
        }
    }

    /**
     * View loader (same as DashboardController)
     */
    private function view($view, $data = [])
    {
        extract($data);
        require VIEWS . "/$view.php";
    }
}
