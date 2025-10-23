<?php

namespace App\Middleware;

class CorsMiddleware
{
    public static function handle()
    {
        // Get the origin from the request
        $origin = $_SERVER['HTTP_ORIGIN'] ?? '';
        
        // Allow specific origins or all origins for development
        $allowedOrigins = [
            'http://localhost:8080',
            'http://localhost:3000',
            'http://localhost:8000',
            'https://traffic-tracker-t18u.onrender.com'
        ];
        
        if (in_array($origin, $allowedOrigins) || strpos($origin, 'localhost') !== false) {
            header("Access-Control-Allow-Origin: {$origin}");
        } else {
            header("Access-Control-Allow-Origin: *");
        }
        
        header('Access-Control-Allow-Credentials: true');
        header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With, Accept, Origin');
        header('Access-Control-Max-Age: 86400'); // Cache preflight for 24 hours
        
        // Handle preflight requests immediately
        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            // Ensure we don't redirect on OPTIONS
            http_response_code(204); // No Content
            header('Content-Length: 0');
            exit();
        }
    }
}
