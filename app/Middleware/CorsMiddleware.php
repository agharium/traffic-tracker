<?php

namespace App\Middleware;

class CorsMiddleware
{
    public static function handle()
    {
        // Get the origin from the request
        $origin = $_SERVER['HTTP_ORIGIN'] ?? '';
        
        // For tracking API, we need to be more permissive to allow external websites
        // But we'll validate the API key in the controller for security
        if (!empty($origin)) {
            // Always allow the specific origin for tracking
            header("Access-Control-Allow-Origin: {$origin}");
        } else {
            // Fallback to wildcard if no origin header
            header("Access-Control-Allow-Origin: *");
        }
        
        header('Access-Control-Allow-Credentials: false'); // Set to false for wildcard compatibility
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
