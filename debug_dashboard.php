<?php
require_once 'bootstrap.php';
use App\Repositories\TrafficLogRepository;
use App\Repositories\WebsiteRepository;

$em = em();
$trafficRepo = new TrafficLogRepository($em);
$websiteRepo = new WebsiteRepository($em);

// Simulate the same conditions as the dashboard
$selectedWebsite = $websiteRepo->find(1); // Assuming website ID 1 based on the URLs

// Get today's start and end (same as dashboard controller)
$days = 1;
$startDate = new DateTime('today');
$endDate = new DateTime('tomorrow');

echo 'Dashboard query conditions:' . PHP_EOL;
echo 'Start: ' . $startDate->format('Y-m-d H:i:s') . PHP_EOL;
echo 'End: ' . $endDate->format('Y-m-d H:i:s') . PHP_EOL;
echo 'Website: ' . ($selectedWebsite ? $selectedWebsite->getId() : 'null') . PHP_EOL;
echo PHP_EOL;

// Test the exact same methods the dashboard uses
$totalVisits = $trafficRepo->getTotalVisits($startDate, $endDate, $selectedWebsite);
$totalUniqueVisitors = $trafficRepo->getTotalUniqueVisitors($startDate, $endDate, $selectedWebsite);

echo 'Dashboard method results (with website filter):' . PHP_EOL;
echo 'Total visits: ' . $totalVisits . PHP_EOL;
echo 'Unique visitors: ' . $totalUniqueVisitors . PHP_EOL;
echo PHP_EOL;

// Test without website filter
$totalVisitsAll = $trafficRepo->getTotalVisits($startDate, $endDate, null);
$totalUniqueVisitorsAll = $trafficRepo->getTotalUniqueVisitors($startDate, $endDate, null);

echo 'Dashboard method results (without website filter):' . PHP_EOL;
echo 'Total visits: ' . $totalVisitsAll . PHP_EOL;
echo 'Unique visitors: ' . $totalUniqueVisitorsAll . PHP_EOL;
