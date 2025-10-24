<?php
require_once 'bootstrap.php';
$em = em();

// Get all visits for today and show the client_ids and user agents
$today = new DateTime('today');
$tomorrow = new DateTime('tomorrow');

$query = $em->createQuery('
    SELECT tl.id, tl.client_id, tl.user_agent, tl.ip_address, tl.visited_at, tl.page_url
    FROM App\Entities\TrafficLog tl 
    WHERE tl.visited_at >= :start 
    AND tl.visited_at < :end
    ORDER BY tl.visited_at DESC
');
$query->setParameter('start', $today);
$query->setParameter('end', $tomorrow);
$query->setMaxResults(50);
$visits = $query->getArrayResult();

echo "All visits today with client_id analysis:" . PHP_EOL;
echo "=========================================" . PHP_EOL;

$clientIds = [];
foreach ($visits as $visit) {
    $clientId = $visit['client_id'] ?: 'NULL';
    $userAgent = substr($visit['user_agent'], 0, 50) . '...';
    $time = $visit['visited_at']->format('H:i:s');
    
    if (!isset($clientIds[$clientId])) {
        $clientIds[$clientId] = [];
    }
    $clientIds[$clientId][] = $visit;
    
    echo "ID: {$visit['id']}, Time: {$time}, Client: {$clientId}, UA: {$userAgent}" . PHP_EOL;
}

echo PHP_EOL . "Summary by client_id:" . PHP_EOL;
echo "=====================" . PHP_EOL;
foreach ($clientIds as $clientId => $visits) {
    echo "Client ID: {$clientId}" . PHP_EOL;
    echo "  - Visits: " . count($visits) . PHP_EOL;
    echo "  - User Agent: " . substr($visits[0]['user_agent'], 0, 80) . "..." . PHP_EOL;
    echo "  - IP: " . $visits[0]['ip_address'] . PHP_EOL;
    echo PHP_EOL;
}
