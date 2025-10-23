<?php

namespace App\Controllers;

use App\Repositories\WebsiteRepository;
use App\Repositories\UserRepository;

class WebsiteController
{
    private WebsiteRepository $websiteRepo;
    private UserRepository $userRepo;

    public function __construct()
    {
        $em = em();
        $this->websiteRepo = new WebsiteRepository($em);
        $this->userRepo = new UserRepository($em);
    }

    /**
     * Show websites management page
     */
    public function index()
    {
        $userId = $_SESSION['user_id'] ?? null;
        $user = $this->userRepo->find($userId);
        
        if (!$user || !$userId) {
            header('Location: /login');
            exit;
        }

        $websites = $this->websiteRepo->findByUser($user);
        
        view('websites', [
            'title' => 'Websites',
            'websites' => $websites,
            'user' => $user
        ]);
    }

    /**
     * Show form to create new website
     */
    public function create()
    {
        view('websites.create', [
            'title' => 'Add New Website'
        ]);
    }

    /**
     * Store new website
     */
    public function store()
    {
        $userId = $_SESSION['user_id'] ?? null;
        if (!$userId) {
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            return;
        }

        $user = $this->userRepo->find($userId);
        if (!$user) {
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'User not found']);
            return;
        }

        $name = trim($_POST['name'] ?? '');
        $domain = trim($_POST['domain'] ?? '');

        // Validation
        if (empty($name) || empty($domain)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Name and domain are required']);
            return;
        }

        // Clean domain (remove protocol, www, trailing slash)
        $domain = preg_replace('/^https?:\/\//', '', $domain);
        $domain = preg_replace('/^www\./', '', $domain);
        $domain = rtrim($domain, '/');

        // Check if domain is already taken
        if ($this->websiteRepo->isDomainTaken($domain, $user)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'This domain is already being tracked']);
            return;
        }

        try {
            $website = $this->websiteRepo->createWebsite($user, $name, $domain);
            
            if (isset($_POST['ajax'])) {
                echo json_encode([
                    'success' => true, 
                    'message' => 'Website added successfully',
                    'website' => [
                        'id' => $website->getId(),
                        'name' => $website->getName(),
                        'domain' => $website->getDomain(),
                        'api_key' => $website->getApiKey()
                    ]
                ]);
            } else {
                header('Location: /websites?success=1');
            }
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Failed to create website']);
        }
    }

    /**
     * Regenerate API key for website
     */
    public function regenerateApiKey()
    {
        $userId = $_SESSION['user_id'] ?? null;
        if (!$userId) {
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            return;
        }

        $websiteId = $_POST['website_id'] ?? null;
        if (!$websiteId) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Website ID required']);
            return;
        }

        $user = $this->userRepo->find($userId);
        $website = $this->websiteRepo->findOneBy(['id' => $websiteId]);

        if (!$website || $website->getUser()->getId() !== $user->getId()) {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'Website not found or access denied']);
            return;
        }

        try {
            $website->regenerateApiKey();
            $this->websiteRepo->getEntityManager()->flush();

            echo json_encode([
                'success' => true,
                'new_api_key' => $website->getApiKey(),
                'message' => 'API key regenerated successfully'
            ]);
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Failed to regenerate API key']);
        }
    }

    /**
     * Delete website
     */
    public function delete()
    {
        $userId = $_SESSION['user_id'] ?? null;
        if (!$userId) {
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            return;
        }

        $websiteId = $_POST['website_id'] ?? null;
        if (!$websiteId) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Website ID required']);
            return;
        }

        $user = $this->userRepo->find($userId);
        $website = $this->websiteRepo->findOneBy(['id' => $websiteId]);

        if (!$website || $website->getUser()->getId() !== $user->getId()) {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'Website not found or access denied']);
            return;
        }

        try {
            $this->websiteRepo->getEntityManager()->remove($website);
            $this->websiteRepo->getEntityManager()->flush();

            echo json_encode([
                'success' => true,
                'message' => 'Website deleted successfully'
            ]);
        } catch (\Exception $e) {
            error_log('Failed to delete website: ' . $e->getMessage());
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Failed to delete website: ' . $e->getMessage()]);
        }
    }
}
