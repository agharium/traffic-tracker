<?php

namespace App\Controllers;

use App\Services\AuthService;
use App\Middleware\AuthMiddleware;
use Flight;

class AuthController
{
    private AuthService $authService;

    public function __construct()
    {
        $this->authService = new AuthService();
    }

    public function showLogin()
    {
        AuthMiddleware::requireGuest();
        
        $view = is_hx() ? 'auth.partials.login' : 'auth.login';
        view($view, ['title' => 'Login']);
    }

    public function showRegister()
    {
        AuthMiddleware::requireGuest();
        
        $view = is_hx() ? 'auth.partials.register' : 'auth.register';
        view($view, ['title' => 'Register']);
    }

    public function login()
    {
        $loginField = $_POST['login'] ?? ''; // Can be username or email
        $password = $_POST['password'] ?? '';

        if (empty($loginField) || empty($password)) {
            $error = 'Username/email and password are required';
            $view = is_hx() ? 'auth.partials.login' : 'auth.login';
            view($view, ['title' => 'Login', 'error' => $error]);
            return;
        }

        if ($this->authService->login($loginField, $password)) {
            if (is_hx()) {
                header('HX-Redirect: /');
                exit;
            }
            Flight::redirect('/');
        } else {
            $error = 'Invalid username/email or password';
            $view = is_hx() ? 'auth.partials.login' : 'auth.login';
            view($view, ['title' => 'Login', 'error' => $error]);
        }
    }

    public function register()
    {
        $email = $_POST['email'] ?? '';
        $username = $_POST['username'] ?? '';
        $password = $_POST['password'] ?? '';
        $name = $_POST['name'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';

        $errors = [];

        if (empty($email)) $errors[] = 'Email is required';
        if (empty($username)) $errors[] = 'Username is required';
        if (empty($password)) $errors[] = 'Password is required';
        if (empty($name)) $errors[] = 'Name is required';
        if ($password !== $confirmPassword) $errors[] = 'Passwords do not match';
        if (strlen($password) < 6) $errors[] = 'Password must be at least 6 characters';
        if (strlen($username) < 3) $errors[] = 'Username must be at least 3 characters';

        if (!empty($errors)) {
            $view = is_hx() ? 'auth.partials.register' : 'auth.register';
            view($view, ['title' => 'Register', 'errors' => $errors]);
            return;
        }

        if ($this->authService->register($email, $username, $password, $name)) {
            if (is_hx()) {
                header('HX-Redirect: /');
                exit;
            }
            Flight::redirect('/');
        } else {
            $error = 'User with this email or username already exists';
            $view = is_hx() ? 'auth.partials.register' : 'auth.register';
            view($view, ['title' => 'Register', 'error' => $error]);
        }
    }

    public function logout()
    {
        $this->authService->logout();
        
        if (is_hx()) {
            header('HX-Redirect: /login');
            exit;
        }
        Flight::redirect('/login');
    }
}
