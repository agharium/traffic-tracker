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
            // Step 1: Get request data
            error_log("STEP 1: Getting request data");
            $input = json_decode(file_get_contents('php://input'), true);
            error_log("Raw input: " . print_r($input, true));
            
            $pageUrl = $input['page_url'] ?? $_SERVER['REQUEST_URI'] ?? '/';
            $clientId = $input['client_id'] ?? null;
            $websiteDomain = $input['website_domain'] ?? $_SERVER['HTTP_HOST'] ?? null;
            $apiKey = $input['api_key'] ?? $_GET['key'] ?? null;
            
            // Debug logging
            error_log("STEP 2: Parsed data");
            error_log("API Key: " . ($apiKey ?? 'null'));
            error_log("Website Domain: " . ($websiteDomain ?? 'null'));
            error_log("Page URL: " . ($pageUrl ?? 'null'));
            error_log("Client ID: " . ($clientId ?? 'null'));
            
            // Step 3: Get client info
            error_log("STEP 3: Getting client info");
            $ipAddress = $this->getRealIpAddr();
            $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? null;
            $referer = $_SERVER['HTTP_REFERER'] ?? $input['referer'] ?? null;
            
            error_log("IP Address: " . $ipAddress);
            error_log("User Agent: " . ($userAgent ?? 'null'));
            error_log("Referer: " . ($referer ?? 'null'));

            // Step 4: Call repository
            error_log("STEP 4: Calling logAllVisits");
            $visit = $this->trafficRepo->logAllVisits(
                $ipAddress,
                $pageUrl,
                $userAgent,
                $referer,
                $clientId,
                $websiteDomain,
                $apiKey
            );
            
            error_log("STEP 5: Visit logged successfully with ID: " . $visit->getId());

            echo json_encode([
                'success' => true,
                'visit_id' => $visit->getId(),
                'message' => 'Visit logged'
            ]);

        } catch (\Exception $e) {
            error_log("ERROR at step: " . $e->getMessage());
            error_log("Error file: " . $e->getFile());
            error_log("Error line: " . $e->getLine());
            error_log("Stack trace: " . $e->getTraceAsString());
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'error' => 'Failed to log visit: ' . $e->getMessage(),
                'debug' => [
                    'message' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine()
                ]
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
        
        // Force HTTPS for production (Render always uses HTTPS)
        // Use current protocol for local development
        $isProduction = strpos($_SERVER['HTTP_HOST'], 'onrender.com') !== false;
        $protocol = $isProduction ? 'https' : 'http' . ((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 's' : '');
        $baseUrl = $protocol . '://' . $_SERVER['HTTP_HOST'];

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
                        
                        console.log('Tracking data:', data);
                        console.log('Sending to:', this.baseUrl + '/api/track');
                        
                        fetch(this.baseUrl + '/api/track', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                            },
                            body: JSON.stringify(data)
                        })
                        .then(response => response.json())
                        .then(result => {
                            console.log('Tracking response:', result);
                        })
                        .catch(function(error) {
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
     * Test endpoint to debug tracking issues
     * GET /api/test-track
     */
    public function testTrack()
    {
        header('Content-Type: application/json');
        
        try {
            $apiKey = 'tk_eb0aae23640af952f00018c4d438f9326aad9adb5c0069bfe43a4a42';
            
            echo json_encode([
                'success' => true,
                'message' => 'Test endpoint working',
                'api_key' => $apiKey,
                'timestamp' => date('Y-m-d H:i:s')
            ]);
            
        } catch (\Exception $e) {
            echo json_encode([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
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
