<?php
use eftec\bladeone\BladeOne;
use Dotenv\Dotenv;
use Doctrine\ORM\EntityManager;

// Composer autoload
require __DIR__ . '/vendor/autoload.php';

// Load environment
if (file_exists(__DIR__ . '/.env')) {
    $dotenv = Dotenv::createImmutable(__DIR__);
    $dotenv->load();
}

// Basic settings
date_default_timezone_set($_ENV['APP_TIMEZONE'] ?? 'America/Sao_Paulo');

// Handle CORS for API routes before any routing
if (str_contains($_SERVER['REQUEST_URI'] ?? '', '/api/')) {
    $origin = $_SERVER['HTTP_ORIGIN'] ?? '';
    
    if ($origin !== '') {
        header("Access-Control-Allow-Origin: {$origin}");
    } else {
        header("Access-Control-Allow-Origin: *");
    }
    
    header('Access-Control-Allow-Credentials: false');
    header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With, Accept, Origin');
    header('Access-Control-Max-Age: 86400');
    
    // Handle preflight OPTIONS requests immediately
    if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
        http_response_code(204);
        header('Content-Length: 0');
        exit();
    }
}

// BladeOne setup
$views = __DIR__ . '/app/Views';
$cache = __DIR__ . '/storage/cache';

if (!is_dir($cache)) { @mkdir($cache, 0777, true); }

$blade = new BladeOne($views, $cache, BladeOne::MODE_AUTO);

// Doctrine EntityManager
require __DIR__ . '/config/doctrine.php';

try {
    $entityManager = createEntityManager();
} catch (Exception $e) {
    die('Doctrine connection failed: ' . $e->getMessage());
}

// Register in Flight container
Flight::set('blade', $blade);
Flight::set('entityManager', $entityManager);

// Helpers
function em(): EntityManager { return Flight::get('entityManager'); }

function view(string $template, array $data = []) {
    // Add auth user data to all views
    $auth = new \App\Services\AuthService();
    if ($auth->isAuthenticated()) {
        $data['user'] = $auth->getUserData();
    }
    
    $blade = Flight::get('blade');
    echo $blade->run($template, $data);
}

function is_hx(): bool {
    return isset($_SERVER['HTTP_HX_REQUEST']) && $_SERVER['HTTP_HX_REQUEST'] === 'true';
}
