<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;

#[ORM\Entity]
#[ORM\Table(name: "User")]
#[UniqueEntity(fields: ['username'], message: 'نام کاربری تکراری میباشد.')]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: "AUTO")]
    #[ORM\Column(type: "integer")]
    private ?int $id;

    #[ORM\Column(type: "string", length: 256, nullable: true)]
    private ?string $fullName;

    #[ORM\Column(type: "string", length: 256, unique: true)]
    private ?string $username = null;

    #[ORM\Column(type: "string", length: 256, nullable: true)]
    private ?string $plainPassword;

    #[ORM\Column(type: "string")]
    private string $password;

    #[ORM\Column(type: "datetime", nullable: true)]
    private ?\DateTimeInterface $cdate;

    #[ORM\Column(type: "datetime", nullable: true)]
    private ?\DateTimeInterface $udate;

    #[ORM\Column(type: "integer", nullable: true)]
    private ?int $published;

    #[ORM\Column(type: "integer", nullable: true)]
    private ?int $role;

    #[ORM\Column(type: "integer", nullable: true)]
    private ?int $remove;

    #[ORM\Column(type: "string", length: 256, nullable: true)]
    private ?string $mobile;

    #[ORM\Column(type: "string", length: 256, nullable: true)]
    private ?string $email;

    #[ORM\ManyToOne(targetEntity: "File")]
    #[ORM\JoinColumn(name: "Image", referencedColumnName: "id", nullable: true)]
    private ?File $image;

    #[ORM\OneToMany(mappedBy: "user", targetEntity: "Sefareshat")]
    private Collection $sefareshats;

    public function __construct()
    {
        $this->sefareshats = new ArrayCollection();
    }

    // Getters and Setters

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFullName(): ?string
    {
        return $this->fullName;
    }

    public function setFullName(?string $fullName): static
    {
        $this->fullName = $fullName;

        return $this;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(string $username): static
    {
        $this->username = $username;

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

    public function getPublished(): ?int
    {
        return $this->published;
    }

    public function setPublished(?int $published): static
    {
        $this->published = $published;

        return $this;
    }

    public function getRemove(): ?int
    {
        return $this->remove;
    }

    public function setRemove(?int $remove): static
    {
        $this->remove = $remove;

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

    public function getPlainPassword(): ?string
    {
        return $this->plainPassword;
    }

    public function setPlainPassword(?string $plainPassword): self
    {
        $this->plainPassword = $plainPassword;

        return $this;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    public function eraseCredentials(): void
    {
        // If you store any temporary, sensitive data on the user, clear it here
        $this->plainPassword = null;
    }

    public function getUserIdentifier(): string
    {
        return $this->username;
    }

    public function getMobile(): ?string
    {
        return $this->mobile;
    }

    public function setMobile(?string $mobile): static
    {
        $this->mobile = $mobile;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): static
    {
        $this->email = $email;

        return $this;
    }

    // Implement UserInterface methods
    public function getRoles(): array
    {
        // Map integers to role strings
        $role = [
            0 => 'ROLE_USER',
            1 => 'ROLE_ADMIN',
            2 => 'ROLE_WRITER'
        ];

        return [$role[$this->role]];
    }

    public function setRoles(?int $role): static
    {
        $this->role = $role;

        return $this;
    }
    public function getRole(): ?int
    {
        return $this->role;
    }

    public function setRole(?int $role): static
    {
        $this->role = $role;

        return $this;
    }

    /**
     * @return Collection<int, Sefareshat>
     */
    public function getSefareshats(): Collection
    {
        return $this->sefareshats;
    }

    public function addSefareshat(Sefareshat $sefareshat): static
    {
        if (!$this->sefareshats->contains($sefareshat)) {
            $this->sefareshats->add($sefareshat);
            $sefareshat->setUser($this);
        }

        return $this;
    }

    public function removeSefareshat(Sefareshat $sefareshat): static
    {
        if ($this->sefareshats->removeElement($sefareshat)) {
            // set the owning side to null (unless already changed)
            if ($sefareshat->getUser() === $this) {
                $sefareshat->setUser(null);
            }
        }

        return $this;
    }

}
