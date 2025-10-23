<?php
use App\Controllers\HomeController;
use App\Controllers\DashboardController;
use App\Controllers\AuthController;
use App\Middleware\AuthMiddleware;

// Start session for authentication
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

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
    $controller = new \App\Controllers\WebsiteController();
    $controller->index();
});

Flight::route('POST /websites', function() {
    AuthMiddleware::requireAuth();
    $controller = new \App\Controllers\WebsiteController();
    $controller->store();
});

Flight::route('POST /websites/regenerate-key', function() {
    AuthMiddleware::requireAuth();
    $controller = new \App\Controllers\WebsiteController();
    $controller->regenerateApiKey();
});

Flight::route('POST /websites/delete', function() {
    AuthMiddleware::requireAuth();
    $controller = new \App\Controllers\WebsiteController();
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
use App\Controllers\TrackingController;
use App\Middleware\CorsMiddleware;

// Handle CORS preflight for specific API routes
Flight::route('OPTIONS /api/track', function() {
    CorsMiddleware::handle();
});

Flight::route('OPTIONS /api/tracking-script', function() {
    CorsMiddleware::handle();
});

// Handle CORS preflight for all other API routes
Flight::route('OPTIONS /api/*', function() {
    CorsMiddleware::handle();
});

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

// Generate sample data route (for testing)
Flight::route('GET /generate-sample-data', function() {
    AuthMiddleware::requireAuth();
    
    $em = em();
    $repo = new \App\Repositories\TrafficLogRepository($em);
    
    // Generate sample data for the last 7 days
    $ips = ['192.168.1.1', '10.0.0.1', '172.16.0.1', '203.0.113.1', '198.51.100.1'];
    $pages = ['/', '/about', '/contact', '/products', '/blog'];
    $userAgents = [
        'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
        'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36',
        'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36'
    ];
    
    $count = 0;
    for ($day = 6; $day >= 0; $day--) {
        $date = date('Y-m-d', strtotime("-{$day} days"));
        $visitsPerDay = rand(5, 20);
        
        for ($i = 0; $i < $visitsPerDay; $i++) {
            $ip = $ips[array_rand($ips)];
            $page = $pages[array_rand($pages)];
            $userAgent = $userAgents[array_rand($userAgents)];
            $clientId = 'client_' . rand(1000, 9999);
            
            // Create a unique session hash for this combination
            $sessionHash = hash('sha256', $ip . $userAgent . $date);
            
            // Create the log entry manually
            $log = new \App\Entities\TrafficLog();
            $log->setIpAddress($ip)
                ->setPageUrl($page)
                ->setUserAgent($userAgent)
                ->setReferer('https://google.com')
                ->setClientId($clientId)
                ->setWebsiteDomain('localhost:8080');
            
            // Set a custom visited_at time for this day
            $visitTime = new DateTime($date . ' ' . rand(8, 22) . ':' . rand(0, 59) . ':' . rand(0, 59));
            $reflection = new \ReflectionClass($log);
            $visitedAtProperty = $reflection->getProperty('visited_at');
            $visitedAtProperty->setAccessible(true);
            $visitedAtProperty->setValue($log, $visitTime);
            
            // Set the session hash
            $sessionHashProperty = $reflection->getProperty('session_hash');
            $sessionHashProperty->setAccessible(true);
            $sessionHashProperty->setValue($log, $sessionHash);
            
            $em->persist($log);
            $count++;
        }
    }
    
    $em->flush();
    
    echo "Generated {$count} sample traffic log entries!";
});
