<?php

// Set environment for production
$_ENV['APP_DEBUG'] = 'false';

require __DIR__ . '/bootstrap.php';

try {
    echo "Starting proxy generation...\n";
    
    $entityManager = em();
    $proxyDir = $entityManager->getConfiguration()->getProxyDir();
    
    echo "Proxy directory: $proxyDir\n";
    
    // Ensure directory exists and is writable
    if (!is_dir($proxyDir)) {
        mkdir($proxyDir, 0777, true);
        echo "Created proxy directory\n";
    }
    
    if (!is_writable($proxyDir)) {
        chmod($proxyDir, 0777);
        echo "Made proxy directory writable\n";
    }
    
    // Get all metadata
    $metadatas = $entityManager->getMetadataFactory()->getAllMetadata();
    echo "Found " . count($metadatas) . " entities\n";
    
    // Generate proxies
    echo "Generating proxies...\n";
    $proxyFactory = $entityManager->getProxyFactory();
    $proxyFactory->generateProxyClasses($metadatas);
    
    echo "Proxies generated successfully in: $proxyDir\n";
    
    // List generated files
    if (is_dir($proxyDir)) {
        $files = scandir($proxyDir);
        echo "Generated proxy files:\n";
        foreach ($files as $file) {
            if ($file !== '.' && $file !== '..' && is_file($proxyDir . '/' . $file)) {
                $size = filesize($proxyDir . '/' . $file);
                echo "- $file ($size bytes)\n";
            }
        }
    }
    
    echo "Proxy generation completed successfully!\n";
    
} catch (Exception $e) {
    echo "Error generating proxies: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
    exit(1);
}
