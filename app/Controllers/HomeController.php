<?php
namespace App\Controllers;

class HomeController {
    public function index()
    {
        $view = is_hx() ? 'partials.home' : 'home';
        view($view, ['title' => 'Home']);
    }
}
