<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: "UserInformation")]
class UserInformation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer")]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: "Product")]
    #[ORM\JoinColumn(name: "Product", referencedColumnName: "id", nullable: true)]
    private ?Product $product = null;

    #[ORM\Column(type: "string", length: 255, nullable: true)]
    private ?string $fullName;

    #[ORM\Column(type: "string", length: 255, nullable: true)]
    private ?string $name;

    #[ORM\Column(type: "string", length: 255, nullable: true)]
    private ?string $designation;

    #[ORM\Column(type: "string", length: 255, nullable: true)]
    private ?string $address;

    #[ORM\Column(type: "string", length: 50, nullable: true)]
    private ?string $phone;

    #[ORM\Column(type: "string", length: 255, nullable: true)]
    private ?string $email;

    #[ORM\ManyToOne(targetEntity: "File")]
    #[ORM\JoinColumn(name: "Image", referencedColumnName: "id", nullable: true)]
    private ?File $image = null;

    #[ORM\OneToMany(targetEntity: Section::class, mappedBy: "userInformation", cascade: ["persist", "remove"], orphanRemoval: true)]
    private ?Collection $sections = null;

    #[ORM\ManyToOne]
    private ?User $User = null;

    #[ORM\Column(type: "datetime", nullable: true)]
    private ?\DateTimeInterface $cdate = null;

    #[ORM\Column(type: "datetime", nullable: true)]
    private ?\DateTimeInterface $udate = null;

    public function __construct()
    {
        $this->sections = new ArrayCollection();
    }

    // Getters and setters...

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFullName(): ?string
    {
        return $this->fullName;
    }

    public function setFullName(string $fullName): static
    {
        $this->fullName = $fullName;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getDesignation(): ?string
    {
        return $this->designation;
    }

    public function setDesignation(string $designation): static
    {
        $this->designation = $designation;

        return $this;
    }

    public function getAddress(): ?string
    {
        return $this->address;
    }

    public function setAddress(string $address): static
    {
        $this->address = $address;

        return $this;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(string $phone): static
    {
        $this->phone = $phone;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    /**
     * @return Collection<int, Section>
     */
    public function getSections(): Collection
    {
        return $this->sections;
    }

    public function addSection(Section $section): static
    {
        if (!$this->sections->contains($section)) {
            $this->sections->add($section);
            $section->setUserInformation($this);
        }

        return $this;
    }

    public function removeSection(Section $section): static
    {
        if ($this->sections->removeElement($section)) {
            // set the owning side to null (unless already changed)
            if ($section->getUserInformation() === $this) {
                $section->setUserInformation(null);
            }
        }

        return $this;
    }

    public function getImage(): ?File
    {
        return $this->image;
    }

    public function setImage(?File $image): static
    {
        $this->image = $image;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->User;
    }

    public function setUser(?User $User): static
    {
        $this->User = $User;

        return $this;
    }

    public function getProduct(): ?Product
    {
        return $this->product;
    }

    public function setProduct(?Product $product): static
    {
        $this->product = $product;

        return $this;
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

    public function getUdate(): ?\DateTimeInterface
    {
        return $this->udate;
    }

    public function setUdate(?\DateTimeInterface $udate): static
    {
        $this->udate = $udate;

        return $this;
    }
}
