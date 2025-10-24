<?php
require_once 'bootstrap.php';
$em = em();

// Get today's start and end
$today = new DateTime('today');
$tomorrow = new DateTime('tomorrow');

echo 'Today range: ' . $today->format('Y-m-d H:i:s') . ' to ' . $tomorrow->format('Y-m-d H:i:s') . PHP_EOL;

// Count total visits for today
$totalQuery = $em->createQuery('
    SELECT COUNT(tl.id) 
    FROM App\Entities\TrafficLog tl 
    WHERE tl.visited_at >= :start 
    AND tl.visited_at < :end
');
$totalQuery->setParameter('start', $today);
$totalQuery->setParameter('end', $tomorrow);
$totalCount = $totalQuery->getSingleScalarResult();

echo 'Total visits today: ' . $totalCount . PHP_EOL;

// Count unique visitors using client_id
$uniqueQuery = $em->createQuery('
    SELECT COUNT(DISTINCT COALESCE(tl.client_id, tl.session_hash)) 
    FROM App\Entities\TrafficLog tl 
    WHERE tl.visited_at >= :start 
    AND tl.visited_at < :end
');
$uniqueQuery->setParameter('start', $today);
$uniqueQuery->setParameter('end', $tomorrow);
$uniqueCount = $uniqueQuery->getSingleScalarResult();

echo 'Unique visitors today: ' . $uniqueCount . PHP_EOL;

// Show breakdown by client_id
$breakdownQuery = $em->createQuery('
    SELECT tl.client_id, COUNT(tl.id) as visit_count
    FROM App\Entities\TrafficLog tl 
    WHERE tl.visited_at >= :start 
    AND tl.visited_at < :end
    GROUP BY tl.client_id
    ORDER BY visit_count DESC
');
$breakdownQuery->setParameter('start', $today);
$breakdownQuery->setParameter('end', $tomorrow);
$breakdown = $breakdownQuery->getArrayResult();

echo PHP_EOL . 'Breakdown by client_id:' . PHP_EOL;
foreach ($breakdown as $row) {
    echo '  ' . ($row['client_id'] ?: 'NULL') . ': ' . $row['visit_count'] . ' visits' . PHP_EOL;
}

// Show recent visits
$recentQuery = $em->createQuery('
    SELECT tl.id, tl.page_url, tl.client_id, tl.visited_at
    FROM App\Entities\TrafficLog tl 
    WHERE tl.visited_at >= :start 
    AND tl.visited_at < :end
    ORDER BY tl.visited_at DESC
');
$recentQuery->setParameter('start', $today);
$recentQuery->setParameter('end', $tomorrow);
$recentQuery->setMaxResults(20);
$recent = $recentQuery->getArrayResult();

echo PHP_EOL . 'Recent 20 visits today:' . PHP_EOL;
foreach ($recent as $visit) {
    echo '  ID: ' . $visit['id'] . ', Page: ' . $visit['page_url'] . ', Client: ' . ($visit['client_id'] ?: 'NULL') . ', Time: ' . $visit['visited_at']->format('H:i:s') . PHP_EOL;
}
