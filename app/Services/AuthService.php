<?php

namespace App\Services;

use App\Entities\User;
use App\Repositories\UserRepository;

/**
 * Service for handling authentication
 */
class AuthService
{
    private UserRepository $userRepository;

    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        $em = em();
        $this->userRepository = new UserRepository($em);
    }

    /**
     * Login user with username or email
     */
    public function login(string $loginField, string $password): bool
    {
        $user = $this->userRepository->authenticateByUsernameOrEmail($loginField, $password);
        
        if ($user) {
            $_SESSION['user_id'] = $user->getId();
            $_SESSION['user_email'] = $user->getEmail();
            $_SESSION['user_name'] = $user->getName();
            $_SESSION['user_username'] = $user->getUsername();
            return true;
        }

        return false;
    }

    /**
     * Logout user
     */
    public function logout(): void
    {
        session_destroy();
        session_start();
    }

    /**
     * Check if user is authenticated
     */
    public function isAuthenticated(): bool
    {
        return isset($_SESSION['user_id']);
    }

    /**
     * Get current user
     */
    public function getCurrentUser(): ?User
    {
        if (!$this->isAuthenticated()) {
            return null;
        }

        return $this->userRepository->find($_SESSION['user_id']);
    }

    /**
     * Register new user
     */
    public function register(string $email, string $username, string $password, string $name): bool
    {
        try {
            $user = $this->userRepository->createUser($email, $username, $password, $name);
            
            // Auto-login after registration
            $_SESSION['user_id'] = $user->getId();
            $_SESSION['user_email'] = $user->getEmail();
            $_SESSION['user_name'] = $user->getName();
            $_SESSION['user_username'] = $user->getUsername();
            
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Get current user data for views
     */
    public function getUserData(): array
    {
        if (!$this->isAuthenticated()) {
            return [];
        }

        return [
            'id' => $_SESSION['user_id'] ?? null,
            'email' => $_SESSION['user_email'] ?? null,
            'name' => $_SESSION['user_name'] ?? null,
            'username' => $_SESSION['user_username'] ?? null,
        ];
    }
}
