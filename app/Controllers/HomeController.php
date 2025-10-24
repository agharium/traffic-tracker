<?php
namespace App\Controllers;

/**
 * Controller for handling home page requests
 */
class HomeController {
    /**
     * Show the home page
     */
    public function index()
    {
        $view = is_hx() ? 'partials.home' : 'home';
        view($view, ['title' => 'Home']);
    }
}
