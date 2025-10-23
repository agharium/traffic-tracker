<?php

namespace App\Repositories;

use App\Entities\Website;
use App\Entities\User;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\EntityManagerInterface;

class WebsiteRepository extends EntityRepository
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $em)
    {
        $this->entityManager = $em;
        parent::__construct($em, $em->getClassMetadata(Website::class));
    }

    /**
     * Get the EntityManager
     */
    public function getEntityManager(): EntityManagerInterface
    {
        return $this->entityManager;
    }

    /**
     * Find website by API key
     */
    public function findByApiKey(string $apiKey): ?Website
    {
        return $this->createQueryBuilder('w')
            ->where('w.api_key = :apiKey')
            ->andWhere('w.is_active = true')
            ->setParameter('apiKey', $apiKey)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Get all websites for a user
     */
    public function findByUser(User $user): array
    {
        return $this->createQueryBuilder('w')
            ->where('w.user = :user')
            ->orderBy('w.created_at', 'DESC')
            ->setParameter('user', $user)
            ->getQuery()
            ->getResult();
    }

    /**
     * Check if domain is already taken by another user OR by the same user
     */
    public function isDomainTaken(string $domain, User $currentUser): bool
    {
        $website = $this->createQueryBuilder('w')
            ->where('w.domain = :domain')
            ->setParameter('domain', $domain)
            ->getQuery()
            ->getOneOrNullResult();

        return $website !== null;
    }

    /**
     * Create a new website for user
     */
    public function createWebsite(User $user, string $name, string $domain): Website
    {
        $website = new Website();
        $website->setUser($user)
               ->setName($name)
               ->setDomain($domain);

        $this->entityManager->persist($website);
        $this->entityManager->flush();

        return $website;
    }
}
