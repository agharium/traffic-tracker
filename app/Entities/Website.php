<?php

namespace App\Entities;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use DateTime;

/**
 * Entity representing a website
 */

#[ORM\Entity]
#[ORM\Table(name: 'websites')]
class Website
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private int $id;

    #[ORM\Column(type: 'string', length: 255)]
    private string $name;

    #[ORM\Column(type: 'string', length: 255)]
    private string $domain;

    #[ORM\Column(type: 'string', length: 64, unique: true)]
    private string $api_key;

    #[ORM\Column(type: 'datetime')]
    private DateTime $created_at;

    #[ORM\ManyToOne(targetEntity: User::class, fetch: 'EAGER')]
    #[ORM\JoinColumn(name: 'user_id', referencedColumnName: 'id', nullable: false)]
    private User $user;

    #[ORM\OneToMany(mappedBy: 'website', targetEntity: TrafficLog::class, cascade: ['remove'], fetch: 'EXTRA_LAZY')]
    private Collection $traffic_logs;

    #[ORM\Column(type: 'boolean')]
    private bool $is_active = true;

    public function __construct()
    {
        $this->traffic_logs = new ArrayCollection();
        $this->created_at = new DateTime();
        $this->generateApiKey();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    public function getDomain(): string
    {
        return $this->domain;
    }

    public function setDomain(string $domain): self
    {
        $this->domain = $domain;
        return $this;
    }

    public function getApiKey(): string
    {
        return $this->api_key;
    }

    public function regenerateApiKey(): self
    {
        $this->generateApiKey();
        return $this;
    }

    private function generateApiKey(): void
    {
        $this->api_key = 'tk_' . bin2hex(random_bytes(28)); // tk_[56 chars]
    }

    public function getCreatedAt(): DateTime
    {
        return $this->created_at;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function setUser(User $user): self
    {
        $this->user = $user;
        return $this;
    }

    public function getTrafficLogs(): Collection
    {
        return $this->traffic_logs;
    }

    public function isActive(): bool
    {
        return $this->is_active;
    }

    public function setActive(bool $is_active): self
    {
        $this->is_active = $is_active;
        return $this;
    }
}
