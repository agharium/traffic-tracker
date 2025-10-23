<?php
namespace App\Controllers;

use App\Repositories\TrafficLogRepository;
use App\Repositories\WebsiteRepository;
use App\Repositories\UserRepository;
use DateTime;

class DashboardController {
    private TrafficLogRepository $trafficRepo;
    private WebsiteRepository $websiteRepo;
    private UserRepository $userRepo;

    public function __construct()
    {
        $em = em();
        $this->trafficRepo = new TrafficLogRepository($em);
        $this->websiteRepo = new WebsiteRepository($em);
        $this->userRepo = new UserRepository($em);
    }

    public function index() {
        $userId = $_SESSION['user_id'] ?? null;
        $user = $this->userRepo->find($userId);
        
        if (!$user || !$userId) {
            header('Location: /login');
            exit;
        }

        // Get user's websites
        $websites = $this->websiteRepo->findByUser($user);
        
        // Get selected website ID (default to first website or 'all')
        $selectedWebsiteId = $_GET['website_id'] ?? 'all';
        $selectedWebsite = null;
        
        if ($selectedWebsiteId !== 'all') {
            $selectedWebsite = $this->websiteRepo->find($selectedWebsiteId);
            // Ensure user owns this website
            if (!$selectedWebsite || $selectedWebsite->getUser()->getId() !== $userId) {
                $selectedWebsiteId = 'all';
                $selectedWebsite = null;
            }
        }
        
        // Load all data at once
        $days = $_GET['days'] ?? 30; // Get from URL parameter
        $endDate = new DateTime();
        $startDate = new DateTime("-{$days} days");
        
        // Get all stats with both total and unique counts (filtered by website if selected)
        $totalVisits = $this->trafficRepo->getTotalVisits($startDate, $endDate, $selectedWebsite);
        $totalUniqueVisitors = $this->trafficRepo->getTotalUniqueVisitors($startDate, $endDate, $selectedWebsite);
        $topPages = $this->trafficRepo->getVisitsByPageWithStats($startDate, $endDate, $selectedWebsite);
        $topReferrers = $this->trafficRepo->getTopReferrers($startDate, $endDate, 5, $selectedWebsite);
        
        // Get chart data with both total and unique visits
        $chartData = $this->trafficRepo->getVisitsByDayWithStats($startDate, $endDate, $selectedWebsite);
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
            'websites' => $websites,
            'selected_website_id' => $selectedWebsiteId,
            'selected_website' => $selectedWebsite,
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
        $userId = $_SESSION['user_id'] ?? null;
        $user = $this->userRepo->find($userId);
        
        if (!$user || !$userId) {
            header('Location: /login');
            exit;
        }

        // Get selected website if provided
        $selectedWebsiteId = $_GET['website_id'] ?? 'all';
        $selectedWebsite = null;
        
        if ($selectedWebsiteId !== 'all') {
            $selectedWebsite = $this->websiteRepo->find($selectedWebsiteId);
            // Ensure user owns this website
            if (!$selectedWebsite || $selectedWebsite->getUser()->getId() !== $userId) {
                $selectedWebsite = null;
            }
        }

        // Get data for the selected period (default: last 7 days)
        $days = $_GET['days'] ?? 7;
        $endDate = new DateTime();
        $startDate = new DateTime("-{$days} days");
        
        $data = $this->trafficRepo->getUniqueVisitsByDay($startDate, $endDate, $selectedWebsite);
        
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
        $userId = $_SESSION['user_id'] ?? null;
        $user = $this->userRepo->find($userId);
        
        if (!$user || !$userId) {
            header('Location: /login');
            exit;
        }

        // Get selected website if provided
        $selectedWebsiteId = $_GET['website_id'] ?? 'all';
        $selectedWebsite = null;
        
        if ($selectedWebsiteId !== 'all') {
            $selectedWebsite = $this->websiteRepo->find($selectedWebsiteId);
            // Ensure user owns this website
            if (!$selectedWebsite || $selectedWebsite->getUser()->getId() !== $userId) {
                $selectedWebsite = null;
            }
        }

        // Get data for the selected time period (default: last 30 days)
        $days = $_GET['days'] ?? 30;
        $endDate = new DateTime();
        $startDate = new DateTime("-{$days} days");
        
        $rows = $this->trafficRepo->getUniqueVisitsByPage($startDate, $endDate, $selectedWebsite);
        
        // If no data, show empty state
        if (empty($rows)) {
            $rows = [];
        }
        
        view('dashboard.table', ['rows' => $rows, 'period' => $days]);
    }

    public function stats() {
        $userId = $_SESSION['user_id'] ?? null;
        $user = $this->userRepo->find($userId);
        
        if (!$user || !$userId) {
            header('Location: /login');
            exit;
        }

        // Get selected website if provided
        $selectedWebsiteId = $_GET['website_id'] ?? 'all';
        $selectedWebsite = null;
        
        if ($selectedWebsiteId !== 'all') {
            $selectedWebsite = $this->websiteRepo->find($selectedWebsiteId);
            // Ensure user owns this website
            if (!$selectedWebsite || $selectedWebsite->getUser()->getId() !== $userId) {
                $selectedWebsite = null;
            }
        }

        // Get period from query params
        $days = $_GET['days'] ?? 30;
        $type = $_GET['type'] ?? 'all';
        $endDate = new DateTime();
        $startDate = new DateTime("-{$days} days");

        // Get statistics
        $totalVisitors = $this->trafficRepo->getTotalUniqueVisitors($startDate, $endDate, $selectedWebsite);
        $topPages = $this->trafficRepo->getUniqueVisitsByPage($startDate, $endDate, $selectedWebsite);
        $topReferrers = $this->trafficRepo->getTopReferrers($startDate, $endDate, 5, $selectedWebsite);
        
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
