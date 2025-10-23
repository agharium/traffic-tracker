<?php
namespace App\Controllers;

use App\Repositories\TrafficLogRepository;
use DateTime;

class DashboardController {
    private TrafficLogRepository $trafficRepo;

    public function __construct()
    {
        $em = em();
        $this->trafficRepo = new TrafficLogRepository($em);
    }

    public function index() {
        // Load all data at once
        $days = $_GET['days'] ?? 30; // Get from URL parameter
        $endDate = new DateTime();
        $startDate = new DateTime("-{$days} days");
        
        // Get all stats with both total and unique counts
        $totalVisits = $this->trafficRepo->getTotalVisits($startDate, $endDate);
        $totalUniqueVisitors = $this->trafficRepo->getTotalUniqueVisitors($startDate, $endDate);
        $topPages = $this->trafficRepo->getVisitsByPageWithStats($startDate, $endDate);
        $topReferrers = $this->trafficRepo->getTopReferrers($startDate, $endDate, 5);
        
        // Get chart data with both total and unique visits
        $chartData = $this->trafficRepo->getVisitsByDayWithStats($startDate, $endDate);
        $labels = [];
        $totalValues = [];
        $uniqueValues = [];
        for ($i = $days - 1; $i >= 0; $i--) {
            $date = new DateTime("-{$i} days");
            $dateStr = $date->format('Y-m-d');
            $labels[] = $date->format('M j');
            
            $totalVisits = 0;
            $uniqueVisits = 0;
            foreach ($chartData as $row) {
                if ($row['visit_date'] === $dateStr) {
                    $totalVisits = (int) $row['total_visits'];
                    $uniqueVisits = (int) $row['unique_visits'];
                    break;
                }
            }
            $totalValues[] = $totalVisits;
            $uniqueValues[] = $uniqueVisits;
        }
        
        view('dashboard', [
            'title' => 'Dashboard',
            'total_visits' => $totalVisits,
            'total_visitors' => $totalUniqueVisitors,
            'top_pages' => $topPages,
            'top_referrers' => $topReferrers,
            'chart_labels' => $labels,
            'chart_total_values' => $totalValues,
            'chart_unique_values' => $uniqueValues,
            'chart_values' => $uniqueValues, // Keep for backward compatibility
            'table_rows' => $topPages,
            'period' => $days,
            'selected_period' => $days
        ]);
    }
    
    public function chart() {
        // Get data for the selected period (default: last 7 days)
        $days = $_GET['days'] ?? 7;
        $endDate = new DateTime();
        $startDate = new DateTime("-{$days} days");
        
        $data = $this->trafficRepo->getUniqueVisitsByDay($startDate, $endDate);
        
        $labels = [];
        $values = [];
        
        // Create array for all days in the period (fill missing days with 0)
        for ($i = $days - 1; $i >= 0; $i--) {
            $date = new DateTime("-{$i} days");
            $dateStr = $date->format('Y-m-d');
            $labels[] = $date->format('M j');
            
            // Find visits for this date
            $visits = 0;
            foreach ($data as $row) {
                if ($row['visit_date'] === $dateStr) {
                    $visits = (int) $row['unique_visits'];
                    break;
                }
            }
            $values[] = $visits;
        }
        
        view('dashboard.chart', ['labels' => $labels, 'values' => $values]);
    }
    
    public function table() {
        // Get data for the selected time period (default: last 30 days)
        $days = $_GET['days'] ?? 30;
        $endDate = new DateTime();
        $startDate = new DateTime("-{$days} days");
        
        $rows = $this->trafficRepo->getUniqueVisitsByPage($startDate, $endDate);
        
        // If no data, show empty state
        if (empty($rows)) {
            $rows = [];
        }
        
        view('dashboard.table', ['rows' => $rows, 'period' => $days]);
    }

    public function stats() {
        // Get period from query params
        $days = $_GET['days'] ?? 30;
        $type = $_GET['type'] ?? 'all';
        $endDate = new DateTime();
        $startDate = new DateTime("-{$days} days");

        // Get statistics
        $totalVisitors = $this->trafficRepo->getTotalUniqueVisitors($startDate, $endDate);
        $topPages = $this->trafficRepo->getUniqueVisitsByPage($startDate, $endDate);
        $topReferrers = $this->trafficRepo->getTopReferrers($startDate, $endDate, 5);
        
        $data = [
            'total_visitors' => $totalVisitors,
            'top_pages' => $topPages,
            'top_referrers' => $topReferrers,
            'period' => $days
        ];

        // Return specific stat component based on type
        switch ($type) {
            case 'visitors':
                view('dashboard.visitors', $data);
                break;
            case 'popular':
                view('dashboard.popular', $data);
                break;
            case 'status':
                view('dashboard.status', $data);
                break;
            default:
                view('dashboard.stats', $data);
                break;
        }
    }
}
