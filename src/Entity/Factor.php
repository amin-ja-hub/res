<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: "Factor")]
class Factor
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: "AUTO")]
    #[ORM\Column(type: "integer")]
    private ?int $id;

    #[ORM\Column(type: "datetime", nullable: true)]
    private ?\DateTimeInterface $cdate;

    #[ORM\ManyToOne(targetEntity: "Sefareshat")]
    #[ORM\JoinColumn(name: "Sefareshat_id", referencedColumnName: "id", nullable: true)]
    private Sefareshat $sefareshat;

    #[ORM\Column(type: "integer", nullable: true)]
    private ?int $trackingCode;

    #[ORM\Column(type: "string", length: 256, nullable: true)]
    private ?string $amount;

    #[ORM\ManyToOne(targetEntity: "User")]
    #[ORM\JoinColumn(name: "User_id", referencedColumnName: "id", nullable: true)]
    private User $user;

    // Getters and Setters

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCdate(): ?\DateTimeInterface
    {
        return $this->cdate;
    }

    public function setCdate(?\DateTimeInterface $cdate): static
    {
        $this->cdate = $cdate;

        return $this;
    }

    public function getTrackingCode(): ?int
    {
        return $this->trackingCode;
    }

    public function setTrackingCode(?int $trackingCode): static
    {
        $this->trackingCode = $trackingCode;

        return $this;
    }

    public function getAmount(): ?string
    {
        return $this->amount;
    }

    public function setAmount(?string $amount): static
    {
        $this->amount = $amount;

        return $this;
    }

    public function getSefareshat(): ?Sefareshat
    {
        return $this->sefareshat;
    }

    public function setSefareshat(?Sefareshat $sefareshat): static
    {
        $this->sefareshat = $sefareshat;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;

        return $this;
    }
}
