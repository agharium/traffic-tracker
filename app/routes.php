<?php
use App\Controllers\HomeController;
use App\Controllers\DashboardController;
use App\Controllers\AuthController;
use App\Middleware\AuthMiddleware;
use App\Controllers\TrackingController;
use App\Middleware\CorsMiddleware;
use App\Controllers\WebsiteController;

// Start session for authentication
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Global CORS handler for all API routes
Flight::route('OPTIONS /api/*', function() {
    CorsMiddleware::handle();
});

// Authentication routes (guest only)
Flight::route('GET /login', [AuthController::class, 'showLogin']);
Flight::route('POST /login', [AuthController::class, 'login']);
Flight::route('GET /register', [AuthController::class, 'showRegister']);
Flight::route('POST /register', [AuthController::class, 'register']);
Flight::route('POST /logout', [AuthController::class, 'logout']);

// Public routes
Flight::route('GET /', [HomeController::class, 'index']);

// Protected routes (require authentication)
Flight::route('GET /dashboard', function() {
    AuthMiddleware::requireAuth();
    $controller = new DashboardController();
    $controller->index();
});

// Website management routes
Flight::route('GET /websites', function() {
    AuthMiddleware::requireAuth();
    $controller = new WebsiteController();
    $controller->index();
});

Flight::route('POST /websites', function() {
    AuthMiddleware::requireAuth();
    $controller = new WebsiteController();
    $controller->store();
});

Flight::route('POST /websites/regenerate-key', function() {
    AuthMiddleware::requireAuth();
    $controller = new WebsiteController();
    $controller->regenerateApiKey();
});

Flight::route('POST /websites/delete', function() {
    AuthMiddleware::requireAuth();
    $controller = new WebsiteController();
    $controller->delete();
});

Flight::route('GET /dashboard/chart', function() {
    AuthMiddleware::requireAuth();
    $controller = new DashboardController();
    $controller->chart();
});

Flight::route('GET /dashboard/table', function() {
    AuthMiddleware::requireAuth();
    $controller = new DashboardController();
    $controller->table();
});

Flight::route('GET /dashboard/stats', function() {
    AuthMiddleware::requireAuth();
    $controller = new DashboardController();
    $controller->stats();
});

// Public API routes (for tracking)

// API routes with CORS support
Flight::route('POST /api/track', function() {
    CorsMiddleware::handle();
    $controller = new TrackingController();
    $controller->track();
});

Flight::route('GET /api/tracking-script', function() {
    CorsMiddleware::handle();
    $controller = new TrackingController();
    $controller->trackingScript();
});
