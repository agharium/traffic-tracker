<?php

use Doctrine\DBAL\DriverManager;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\ORMSetup;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;

function createEntityManager(): EntityManager
{
    // Cache configuration - use ArrayAdapter for both dev and prod to avoid file issues
    $cache = new ArrayAdapter();
    
    // Entity configuration
    $paths = [__DIR__ . '/../app/Entities'];
    
    // Force dev mode to prevent proxy generation
    $isDevMode = true;
    $config = ORMSetup::createAttributeMetadataConfiguration($paths, $isDevMode, null, $cache);
    
    // Explicitly disable proxy generation
    $config->setAutoGenerateProxyClasses(false);
    $config->setProxyNamespace('DoctrineProxies');
    
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
