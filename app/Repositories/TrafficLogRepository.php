<?php

namespace App\Repositories;

use App\Entities\TrafficLog;
use App\Entities\Website;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use DateTime;

class TrafficLogRepository extends EntityRepository
{
    public function __construct(EntityManagerInterface $em)
    {
        parent::__construct($em, $em->getClassMetadata(TrafficLog::class));
    }

    /**
     * Log a new visit (only if unique for this session/day)
     */
    public function logVisit(Website $website, string $ipAddress, string $pageUrl, ?string $userAgent = null, ?string $referer = null, ?string $clientId = null): bool
    {
        // Generate session hash for uniqueness detection (use stable hash for mobile devices)
        $sessionHash = $this->generateSessionHash($ipAddress, $userAgent);
        
        // Check if this unique visitor already visited this page today
        $today = new DateTime();
        $todayStart = new DateTime($today->format('Y-m-d') . ' 00:00:00');
        $todayEnd = new DateTime($today->format('Y-m-d') . ' 23:59:59');
        
        $existingVisit = $this->createQueryBuilder('tl')
            ->where('tl.session_hash = :sessionHash')
            ->andWhere('tl.page_url = :pageUrl')
            ->andWhere('tl.website = :website')
            ->andWhere('tl.visited_at >= :todayStart')
            ->andWhere('tl.visited_at <= :todayEnd')
            ->setParameter('sessionHash', $sessionHash)
            ->setParameter('pageUrl', $pageUrl)
            ->setParameter('website', $website)
            ->setParameter('todayStart', $todayStart)
            ->setParameter('todayEnd', $todayEnd)
            ->getQuery()
            ->getOneOrNullResult();

        if ($existingVisit) {
            return false; // Already counted today
        }

        // Create new visit log
        $log = new TrafficLog();
        $log->setWebsite($website)
            ->setIpAddress($ipAddress)
            ->setPageUrl($pageUrl)
            ->setUserAgent($userAgent)
            ->setReferer($referer)
            ->setClientId($clientId)
            ->setWebsiteDomain($website->getDomain());

        // Manually set the session hash since we already calculated it
        $reflection = new \ReflectionClass($log);
        $sessionHashProperty = $reflection->getProperty('session_hash');
        $sessionHashProperty->setAccessible(true);
        $sessionHashProperty->setValue($log, $sessionHash);

        $this->getEntityManager()->persist($log);
        $this->getEntityManager()->flush();

        return true; // New unique visit logged
    }

    /**
     * Log all visits without uniqueness checks (new approach)
     */
    public function logAllVisits(string $ipAddress, string $pageUrl, ?string $userAgent = null, ?string $referer = null, ?string $clientId = null, ?string $websiteDomain = null, ?string $apiKey = null): TrafficLog
    {
        // Find website by API key if provided
        $website = null;
        if ($apiKey) {
            $websiteRepo = $this->getEntityManager()->getRepository(Website::class);
            $website = $websiteRepo->findOneBy(['api_key' => $apiKey]);
        }
        
        // If no website found by API key, try to find by domain
        if (!$website && $websiteDomain) {
            $websiteRepo = $this->getEntityManager()->getRepository(Website::class);
            $website = $websiteRepo->findOneBy(['domain' => $websiteDomain]);
        }
        
        // If still no website found, try to get the first available website as fallback
        if (!$website) {
            $websiteRepo = $this->getEntityManager()->getRepository(Website::class);
            $website = $websiteRepo->findOneBy([]);
        }
        
        // If still no website found, skip logging
        if (!$website) {
            throw new \Exception('No website found for tracking');
        }

        // Generate session hash for uniqueness calculation later (use stable hash for mobile devices)
        $sessionHash = $this->generateSessionHash($ipAddress, $userAgent);
        
        // Create new visit log - log every visit
        $log = new TrafficLog();
        $log->setWebsite($website)
            ->setIpAddress($ipAddress)
            ->setPageUrl($pageUrl)
            ->setUserAgent($userAgent)
            ->setReferer($referer)
            ->setClientId($clientId)
            ->setWebsiteDomain($websiteDomain ?? $website->getDomain());

        // Manually set the session hash
        $reflection = new \ReflectionClass($log);
        $sessionHashProperty = $reflection->getProperty('session_hash');
        $sessionHashProperty->setAccessible(true);
        $sessionHashProperty->setValue($log, $sessionHash);

        $this->getEntityManager()->persist($log);
        $this->getEntityManager()->flush();

        return $log;
    }

    /**
     * Get unique visits per page for a time period
     */
    public function getUniqueVisitsByPage(DateTime $startDate, DateTime $endDate, ?Website $website = null): array
    {
        $qb = $this->createQueryBuilder('tl')
            ->select('tl.page_url, COUNT(DISTINCT tl.ip_address) as unique_visits')
            ->where('tl.visited_at >= :startDate')
            ->andWhere('tl.visited_at <= :endDate')
            ->setParameter('startDate', $startDate)
            ->setParameter('endDate', $endDate)
            ->groupBy('tl.page_url')
            ->orderBy('unique_visits', 'DESC');

        if ($website) {
            $qb->andWhere('tl.website = :website')
               ->setParameter('website', $website);
        }

        return $qb->getQuery()->getArrayResult();
    }

    /**
     * Get unique visits by day for charts
     */
    public function getUniqueVisitsByDay(DateTime $startDate, DateTime $endDate, ?Website $website = null): array
    {
        // First get all visits in the date range
        $qb = $this->createQueryBuilder('tl')
            ->select('tl.visited_at, tl.ip_address')
            ->where('tl.visited_at >= :startDate')
            ->andWhere('tl.visited_at <= :endDate')
            ->setParameter('startDate', $startDate)
            ->setParameter('endDate', $endDate);

        if ($website) {
            $qb->andWhere('tl.website = :website')
               ->setParameter('website', $website);
        }

        $visits = $qb->getQuery()->getArrayResult();
        
        // Group by date and count unique IP addresses
        $groupedVisits = [];
        foreach ($visits as $visit) {
            $date = $visit['visited_at']->format('Y-m-d');
            if (!array_key_exists($date, $groupedVisits)) {
                $groupedVisits[$date] = [];
            }
            $groupedVisits[$date][$visit['ip_address']] = true;
        }
        
        // Convert to the expected format
        $result = [];
        foreach ($groupedVisits as $date => $ipAddresses) {
            $result[] = [
                'visit_date' => $date,
                'unique_visits' => count($ipAddresses)
            ];
        }
        
        // Sort by date
        usort($result, function($a, $b) {
            return strcmp($a['visit_date'], $b['visit_date']);
        });
        
        return $result;
    }

    /**
     * Get total unique visitors for a period
     */
    public function getTotalUniqueVisitors(DateTime $startDate, DateTime $endDate, ?Website $website = null): int
    {
        $qb = $this->createQueryBuilder('tl')
            ->select('COUNT(DISTINCT tl.ip_address)')
            ->where('tl.visited_at >= :startDate')
            ->andWhere('tl.visited_at <= :endDate')
            ->setParameter('startDate', $startDate)
            ->setParameter('endDate', $endDate);

        if ($website) {
            $qb->andWhere('tl.website = :website')
               ->setParameter('website', $website);
        }

        return (int) $qb->getQuery()->getSingleScalarResult();
    }

    /**
     * Get top referrers
     */
    public function getTopReferrers(DateTime $startDate, DateTime $endDate, int $limit = 10, ?Website $website = null): array
    {
        $qb = $this->createQueryBuilder('tl')
            ->select('tl.referer, COUNT(DISTINCT tl.ip_address) as unique_visits')
            ->where('tl.visited_at >= :startDate')
            ->andWhere('tl.visited_at <= :endDate')
            ->andWhere('tl.referer IS NOT NULL')
            ->andWhere('tl.referer != :empty')
            ->setParameter('startDate', $startDate)
            ->setParameter('endDate', $endDate)
            ->setParameter('empty', '')
            ->groupBy('tl.referer')
            ->orderBy('unique_visits', 'DESC')
            ->setMaxResults($limit);

        if ($website) {
            $qb->andWhere('tl.website = :website')
               ->setParameter('website', $website);
        }

        return $qb->getQuery()->getArrayResult();
    }

    /**
     * Get total visits count (all visits, not unique)
     */
    public function getTotalVisits(DateTime $startDate, DateTime $endDate, ?Website $website = null): int
    {
        $qb = $this->createQueryBuilder('tl')
            ->select('COUNT(tl.id)')
            ->where('tl.visited_at >= :startDate')
            ->andWhere('tl.visited_at <= :endDate')
            ->setParameter('startDate', $startDate)
            ->setParameter('endDate', $endDate);

        if ($website) {
            $qb->andWhere('tl.website = :website')
               ->setParameter('website', $website);
        }

        return (int) $qb->getQuery()->getSingleScalarResult();
    }

    /**
     * Get visits per page with both total and unique counts
     */
    public function getVisitsByPageWithStats(DateTime $startDate, DateTime $endDate, ?Website $website = null): array
    {
        $qb = $this->createQueryBuilder('tl')
            ->select('tl.page_url, COUNT(tl.id) as total_visits, COUNT(DISTINCT tl.ip_address) as unique_visits')
            ->where('tl.visited_at >= :startDate')
            ->andWhere('tl.visited_at <= :endDate')
            ->setParameter('startDate', $startDate)
            ->setParameter('endDate', $endDate)
            ->groupBy('tl.page_url')
            ->orderBy('total_visits', 'DESC');

        if ($website) {
            $qb->andWhere('tl.website = :website')
               ->setParameter('website', $website);
        }

        return $qb->getQuery()->getArrayResult();
    }

    /**
     * Get visits by day with both total and unique counts
     */
    public function getVisitsByDayWithStats(DateTime $startDate, DateTime $endDate, ?Website $website = null): array
    {
        // Get all visits with date formatting
        $qb = $this->createQueryBuilder('tl')
            ->select('tl.visited_at, tl.ip_address')
            ->where('tl.visited_at >= :startDate')
            ->andWhere('tl.visited_at <= :endDate')
            ->setParameter('startDate', $startDate)
            ->setParameter('endDate', $endDate)
            ->orderBy('tl.visited_at', 'ASC');

        if ($website) {
            $qb->andWhere('tl.website = :website')
               ->setParameter('website', $website);
        }

        $results = $qb->getQuery()->getArrayResult();
        
        // Group by date in PHP
        $groupedResults = [];
        foreach ($results as $result) {
            $date = $result['visited_at']->format('Y-m-d');
            
            if (!array_key_exists($date, $groupedResults)) {
                $groupedResults[$date] = [
                    'visit_date' => $date,
                    'total_visits' => 0,
                    'unique_ips' => []
                ];
            }
            
            $groupedResults[$date]['total_visits']++;
            $groupedResults[$date]['unique_ips'][$result['ip_address']] = true;
        }
        
        // Convert to final format
        $finalResults = [];
        foreach ($groupedResults as $data) {
            $finalResults[] = [
                'visit_date' => $data['visit_date'],
                'total_visits' => $data['total_visits'],
                'unique_visits' => count($data['unique_ips'])
            ];
        }
        
        return $finalResults;
    }

    /**
     * Get visits by hour with both total and unique counts (for single day)
     */
    public function getVisitsByHourWithStats(DateTime $startDate, DateTime $endDate, ?Website $website = null): array
    {
        // Get all visits with datetime
        $qb = $this->createQueryBuilder('tl')
            ->select('tl.visited_at, tl.ip_address')
            ->where('tl.visited_at >= :startDate')
            ->andWhere('tl.visited_at <= :endDate')
            ->setParameter('startDate', $startDate)
            ->setParameter('endDate', $endDate)
            ->orderBy('tl.visited_at', 'ASC');

        if ($website) {
            $qb->andWhere('tl.website = :website')
               ->setParameter('website', $website);
        }

        $results = $qb->getQuery()->getArrayResult();
        
        // Group by hour in PHP
        $groupedResults = [];
        foreach ($results as $result) {
            $hour = $result['visited_at']->format('Y-m-d H');
            
            if (!array_key_exists($hour, $groupedResults)) {
                $groupedResults[$hour] = [
                    'visit_hour' => $hour,
                    'total_visits' => 0,
                    'unique_ips' => []
                ];
            }
            
            $groupedResults[$hour]['total_visits']++;
            $groupedResults[$hour]['unique_ips'][$result['ip_address']] = true;
        }
        
        // Convert to final format
        $finalResults = [];
        foreach ($groupedResults as $data) {
            $finalResults[] = [
                'visit_hour' => $data['visit_hour'],
                'total_visits' => $data['total_visits'],
                'unique_visits' => count($data['unique_ips'])
            ];
        }
        
        return $finalResults;
    }

    /**
     * Extract the first IP from a comma-separated IP list (for proxy chains like Cloudflare)
     */
    private function extractFirstIp(string $ipAddress): string
    {
        if (strpos($ipAddress, ',') !== false) {
            return trim(explode(',', $ipAddress)[0]);
        }
        return $ipAddress;
    }

    /**
     * Generate a stable session hash that handles mobile network switching
     */
    private function generateSessionHash(string $ipAddress, ?string $userAgent): string
    {
        $cleanIp = $this->extractFirstIp($ipAddress);
        $date = date('Y-m-d');
        
        // For mobile devices, use a more stable identifier
        if ($userAgent && $this->isMobileDevice($userAgent)) {
            // For mobile: use only user agent + date (ignore IP completely)
            // Mobile devices often switch IPs frequently due to network changes
            return hash('sha256', $userAgent . $date . 'mobile');
        }
        
        // For desktop: use exact IP as before
        return hash('sha256', $cleanIp . ($userAgent ?? '') . $date);
    }

    /**
     * Check if user agent indicates a mobile device
     */
    private function isMobileDevice(?string $userAgent): bool
    {
        if (!$userAgent) {
            return false;
        }
        
        $mobileIndicators = ['iPhone', 'iPad', 'Android', 'Mobile', 'Tablet', 'BlackBerry', 'Windows Phone'];
        
        foreach ($mobileIndicators as $indicator) {
            if (stripos($userAgent, $indicator) !== false) {
                return true;
            }
        }
        
        return false;
    }
}
