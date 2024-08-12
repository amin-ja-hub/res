<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: "Cart")]
class Cart
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: "AUTO")]
    #[ORM\Column(type: "integer")]
    private ?int $id;

    #[ORM\ManyToOne(targetEntity: "User")]
    #[ORM\JoinColumn(name: "User_id", referencedColumnName: "id", nullable: true)]
    private User $user;

    #[ORM\Column(type: "string", length: 45, nullable: true)]
    private ?string $cdate;

    // Getters and Setters

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCdate(): ?string
    {
        return $this->cdate;
    }

    public function setCdate(?string $cdate): static
    {
        $this->cdate = $cdate;

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
