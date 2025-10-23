<?php

namespace App\Entities;

use Doctrine\ORM\Mapping as ORM;
use DateTime;

#[ORM\Entity]
#[ORM\Table(name: 'traffic_logs')]
class TrafficLog
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 45)]
    private string $ip_address;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $user_agent = null;

    #[ORM\Column(type: 'string', length: 500)]
    private string $page_url;

    #[ORM\Column(type: 'string', length: 500, nullable: true)]
    private ?string $referer = null;

    #[ORM\Column(type: 'string', length: 100, nullable: true)]
    private ?string $client_id = null;

    #[ORM\ManyToOne(targetEntity: Website::class, inversedBy: 'traffic_logs')]
    #[ORM\JoinColumn(name: 'website_id', referencedColumnName: 'id', nullable: false)]
    private Website $website;

    #[ORM\Column(type: 'string', length: 64)]
    private string $session_hash;

    #[ORM\Column(type: 'datetime')]
    private DateTime $visited_at;

    #[ORM\Column(type: 'string', length: 100, nullable: true)]
    private ?string $website_domain = null;

    public function __construct()
    {
        $this->visited_at = new DateTime();
        // Don't generate session hash here - will be done when properties are set
    }

    private function generateSessionHash(): void
    {
        // Only generate if we have the required data
        if (isset($this->ip_address)) {
            $this->session_hash = hash('sha256', 
                $this->ip_address . 
                ($this->user_agent ?? '') . 
                date('Y-m-d')
            );
        }
    }

    // Getters and Setters
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getIpAddress(): string
    {
        return $this->ip_address;
    }

    public function setIpAddress(string $ip_address): self
    {
        $this->ip_address = $ip_address;
        $this->generateSessionHash();
        return $this;
    }

    public function getUserAgent(): ?string
    {
        return $this->user_agent;
    }

    public function setUserAgent(?string $user_agent): self
    {
        $this->user_agent = $user_agent;
        $this->generateSessionHash();
        return $this;
    }

    public function getPageUrl(): string
    {
        return $this->page_url;
    }

    public function setPageUrl(string $page_url): self
    {
        $this->page_url = $page_url;
        return $this;
    }

    public function getReferer(): ?string
    {
        return $this->referer;
    }

    public function setReferer(?string $referer): self
    {
        $this->referer = $referer;
        return $this;
    }

    public function getClientId(): ?string
    {
        return $this->client_id;
    }

    public function setClientId(?string $client_id): self
    {
        $this->client_id = $client_id;
        return $this;
    }

    public function getWebsite(): Website
    {
        return $this->website;
    }

    public function setWebsite(Website $website): self
    {
        $this->website = $website;
        return $this;
    }

    public function getSessionHash(): string
    {
        return $this->session_hash;
    }

    public function getVisitedAt(): DateTime
    {
        return $this->visited_at;
    }

    public function setVisitedAt(DateTime $visited_at): self
    {
        $this->visited_at = $visited_at;
        return $this;
    }

    public function getWebsiteDomain(): ?string
    {
        return $this->website_domain;
    }

    public function setWebsiteDomain(?string $website_domain): self
    {
        $this->website_domain = $website_domain;
        return $this;
    }
}
