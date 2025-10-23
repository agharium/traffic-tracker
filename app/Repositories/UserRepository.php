<?php

namespace App\Repositories;

use App\Entities\User;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\EntityManagerInterface;

class UserRepository extends EntityRepository
{
    public function __construct(EntityManagerInterface $em)
    {
        parent::__construct($em, $em->getClassMetadata(User::class));
    }

    /**
     * Find user by email
     */
    public function findByEmail(string $email): ?User
    {
        return $this->findOneBy(['email' => $email]);
    }

    /**
     * Find user by username
     */
    public function findByUsername(string $username): ?User
    {
        return $this->findOneBy(['username' => $username]);
    }

    /**
     * Find user by username or email
     */
    public function findByUsernameOrEmail(string $loginField): ?User
    {
        // Try email first
        $user = $this->findByEmail($loginField);
        if ($user) {
            return $user;
        }
        
        // Try username
        return $this->findByUsername($loginField);
    }

    /**
     * Create a new user
     */
    public function createUser(string $email, string $username, string $password, string $name): User
    {
        // Check if user already exists by email or username
        if ($this->findByEmail($email)) {
            throw new \Exception('User with this email already exists');
        }
        
        if ($this->findByUsername($username)) {
            throw new \Exception('User with this username already exists');
        }

        $user = new User();
        $user->setEmail($email)
             ->setUsername($username)
             ->setPassword($password)
             ->setName($name);

        $this->getEntityManager()->persist($user);
        $this->getEntityManager()->flush();

        return $user;
    }

    /**
     * Authenticate user by username or email
     */
    public function authenticateByUsernameOrEmail(string $loginField, string $password): ?User
    {
        $user = $this->findByUsernameOrEmail($loginField);
        
        if ($user && $user->verifyPassword($password)) {
            return $user;
        }

        return null;
    }

    /**
     * Authenticate user by email (legacy method)
     */
    public function authenticate(string $email, string $password): ?User
    {
        $user = $this->findByEmail($email);
        
        if ($user && $user->verifyPassword($password)) {
            return $user;
        }

        return null;
    }
}
