<?php

namespace App\Controllers;

use App\Repositories\TrafficLogRepository;
use DateTime;

class TrackingController
{
    private TrafficLogRepository $trafficRepo;

    public function __construct()
    {
        $em = em();
        $this->trafficRepo = new TrafficLogRepository($em);
    }

    /**
     * API endpoint to track visits
     * POST /api/track
     */
    public function track()
    {
        // CORS is handled by middleware
        header('Content-Type: application/json');

        try {
            // Get data from request
            $input = json_decode(file_get_contents('php://input'), true);
            
            $pageUrl = $input['page_url'] ?? $_SERVER['REQUEST_URI'] ?? '/';
            $clientId = $input['client_id'] ?? null;
            $websiteDomain = $input['website_domain'] ?? $_SERVER['HTTP_HOST'] ?? null;
            $apiKey = $input['api_key'] ?? $_GET['key'] ?? null;
            
            // Get client info
            $ipAddress = $this->getRealIpAddr();
            $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? null;
            $referer = $_SERVER['HTTP_REFERER'] ?? $input['referer'] ?? null;

            // Log every visit without uniqueness check
            $visit = $this->trafficRepo->logAllVisits(
                $ipAddress,
                $pageUrl,
                $userAgent,
                $referer,
                $clientId,
                $websiteDomain,
                $apiKey
            );

            echo json_encode([
                'success' => true,
                'visit_id' => $visit->getId(),
                'message' => 'Visit logged'
            ]);

        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'error' => 'Failed to log visit'
            ]);
        }
    }

    /**
     * Generate tracking script for clients
     * GET /api/tracking-script?key=API_KEY
     */
    public function trackingScript()
    {
        // CORS is handled by middleware
        header('Content-Type: application/javascript');

        $apiKey = $_GET['key'] ?? '';
        $baseUrl = 'http' . (isset($_SERVER['HTTPS']) ? 's' : '') . '://' . $_SERVER['HTTP_HOST'];

        echo "
            (function() {
                // Traffic Tracker Script
                var tracker = {
                    baseUrl: '{$baseUrl}',
                    apiKey: '{$apiKey}',
                    clientId: null,
                    
                    init: function() {
                        this.clientId = this.getOrCreateClientId();
                        this.trackPageView();
                        this.bindEvents();
                    },
                    
                    getOrCreateClientId: function() {
                        var clientId = localStorage.getItem('tracker_client_id');
                        if (!clientId) {
                            clientId = 'client_' + Math.random().toString(36).substr(2, 9) + '_' + Date.now();
                            localStorage.setItem('tracker_client_id', clientId);
                        }
                        return clientId;
                    },
                    
                    trackPageView: function() {
                        this.track({
                            page_url: window.location.pathname + window.location.search,
                            referer: document.referrer,
                            website_domain: window.location.hostname
                        });
                    },
                    
                    track: function(data) {
                        data.client_id = this.clientId;
                        data.api_key = this.apiKey;
                        
                        fetch(this.baseUrl + '/api/track', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                            },
                            body: JSON.stringify(data)
                        }).catch(function(error) {
                            console.warn('Traffic tracking failed:', error);
                        });
                    },
                    
                    bindEvents: function() {
                        var self = this;
                        
                        // Track hash changes (SPA navigation)
                        window.addEventListener('hashchange', function() {
                            self.trackPageView();
                        });
                        
                        // Track history API navigation
                        var originalPushState = history.pushState;
                        var originalReplaceState = history.replaceState;
                        
                        history.pushState = function() {
                            originalPushState.apply(history, arguments);
                            setTimeout(function() { self.trackPageView(); }, 100);
                        };
                        
                        history.replaceState = function() {
                            originalReplaceState.apply(history, arguments);
                            setTimeout(function() { self.trackPageView(); }, 100);
                        };
                    }
                };
                
                // Initialize when DOM is ready
                if (document.readyState === 'loading') {
                    document.addEventListener('DOMContentLoaded', function() {
                        tracker.init();
                    });
                } else {
                    tracker.init();
                }
            })();
        ";
    }

    /**
     * Get real IP address (handles proxies)
     */
    private function getRealIpAddr(): string
    {
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            return $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            return $_SERVER['HTTP_X_FORWARDED_FOR'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED'])) {
            return $_SERVER['HTTP_X_FORWARDED'];
        } elseif (!empty($_SERVER['HTTP_FORWARDED_FOR'])) {
            return $_SERVER['HTTP_FORWARDED_FOR'];
        } elseif (!empty($_SERVER['HTTP_FORWARDED'])) {
            return $_SERVER['HTTP_FORWARDED'];
        } elseif (!empty($_SERVER['REMOTE_ADDR'])) {
            return $_SERVER['REMOTE_ADDR'];
        }
        
        return '0.0.0.0';
    }
}
