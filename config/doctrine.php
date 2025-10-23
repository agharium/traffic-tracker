<?php

use Doctrine\DBAL\DriverManager;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\ORMSetup;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;

function createEntityManager(): EntityManager
{
    // Cache configuration
    $isDevMode = ($_ENV['APP_DEBUG'] ?? 'true') === 'true';
    $cache = $isDevMode ? new ArrayAdapter() : new FilesystemAdapter('', 0, __DIR__ . '/../storage/doctrine');
    
    // Entity configuration
    $paths = [__DIR__ . '/../app/Entities'];
    $config = ORMSetup::createAttributeMetadataConfiguration($paths, $isDevMode, null, $cache);
    
    // Disable proxy generation in production to avoid file system issues
    $config->setAutoGenerateProxyClasses(false);
    
    // Connection configuration
    $connectionParams = [
        'dbname' => $_ENV['DB_NAME'] ?? 'tracker',
        'user' => $_ENV['DB_USER'] ?? 'root',
        'password' => $_ENV['DB_PASS'] ?? '',
        'host' => $_ENV['DB_HOST'] ?? '127.0.0.1',
        'port' => $_ENV['DB_PORT'] ?? '5432',
        'driver' => 'pdo_pgsql',
        'charset' => 'utf8',
        'sslmode' => 'require',
    ];
    
    $connection = DriverManager::getConnection($connectionParams, $config);
    
    return new EntityManager($connection, $config);
}
