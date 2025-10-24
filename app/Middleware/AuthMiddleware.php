<?php

namespace App\Middleware;

use App\Services\AuthService;
use Flight;

/**
 * Middleware to handle authentication checks
 */
class AuthMiddleware
{
    /**
     * Require the user to be authenticated
     */
    public static function requireAuth(): void
    {
        $auth = new AuthService();
        
        if (!$auth->isAuthenticated()) {
            // If it's an HTMX request, redirect via HTMX
            if (is_hx()) {
                header('HX-Redirect: /login');
                exit;
            }
            
            // Regular redirect
            Flight::redirect('/login');
        }
    }

    /**
     * Require the user to be a guest (not authenticated)
     */
    public static function requireGuest(): void
    {
        $auth = new AuthService();
        
        if ($auth->isAuthenticated()) {
            // If it's an HTMX request, redirect via HTMX
            if (is_hx()) {
                header('HX-Redirect: /dashboard');
                exit;
            }
            
            // Regular redirect
            Flight::redirect('/dashboard');
        }
    }
}
