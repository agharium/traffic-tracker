<?php

require __DIR__ . '/bootstrap.php';

use Doctrine\ORM\Tools\SchemaTool;

try {
    $entityManager = Flight::get('entityManager');
    $schemaTool = new SchemaTool($entityManager);
    
    // Get metadata from all entities
    $classes = $entityManager->getMetadataFactory()->getAllMetadata();
    
    // Generate SQL to create tables
    $sqls = $schemaTool->getCreateSchemaSql($classes);
    
    echo "SQL to create schema:\n\n";
    foreach ($sqls as $sql) {
        echo $sql . ";\n";
    }
    
    echo "\n\nExecuting schema creation...\n";
    
    // Create tables
    $schemaTool->createSchema($classes);
    
    echo "Schema created successfully!\n";
    
    // Create default admin user
    echo "\nCreating default administrator user...\n";
    
    $userRepo = new \App\Repositories\UserRepository($entityManager);
    try {
        $userRepo->createUser('admin@example.com', 'admin', 'admin', 'Administrator');
        echo "Admin user created successfully!\n";
        echo "Email: admin@example.com\n";
        echo "Password: admin\n";
    } catch (Exception $e) {
        echo "Error creating admin user (probably already exists): " . $e->getMessage() . "\n";
    }
    
} catch (Exception $e) {
    echo "Error creating schema: " . $e->getMessage() . "\n";
    exit(1);
}
