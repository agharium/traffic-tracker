<?php

require __DIR__ . '/bootstrap.php';

use Doctrine\ORM\Tools\Console\ConsoleRunner;
use Doctrine\ORM\Tools\Console\EntityManagerProvider\SingleManagerProvider;

try {
    $entityManager = em();
    
    echo "Generating Doctrine proxies...\n";
    
    // Get all metadata
    $metadatas = $entityManager->getMetadataFactory()->getAllMetadata();
    
    // Generate proxies
    $proxyFactory = $entityManager->getProxyFactory();
    $proxyFactory->generateProxyClasses($metadatas);
    
    echo "Proxies generated successfully in: " . $entityManager->getConfiguration()->getProxyDir() . "\n";
    
    // List generated files
    $proxyDir = $entityManager->getConfiguration()->getProxyDir();
    if (is_dir($proxyDir)) {
        $files = scandir($proxyDir);
        echo "Generated proxy files:\n";
        foreach ($files as $file) {
            if ($file !== '.' && $file !== '..') {
                echo "- $file\n";
            }
        }
    }
    
} catch (Exception $e) {
    echo "Error generating proxies: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
}
