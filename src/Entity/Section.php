<?php
namespace App\Entity;

use App\Entity\UserInformation;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: "sections")]
class Section
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: "AUTO")]
    #[ORM\Column(type: "integer")]
    private ?int $id = null;

    #[ORM\Column(type: "string", length: 255, nullable: true)]
    private ?string $title = null;

    #[ORM\Column(type: "text", nullable: true)]
    private ?string $description = null;

    #[ORM\Column(type: "string", length: 255, nullable: true)]
    private ?string $type = null;

    #[ORM\Column(type: "string", length: 255, nullable: true)]
    private ?string $shtype = null;

    #[ORM\Column(type: "string", length: 255, nullable: true)]
    private ?string $icon = null;

    #[ORM\Column(type: "string", length: 255, nullable: true)]
    private ?string $start = null;

    #[ORM\Column(type: "string", length: 255, nullable: true)]
    private ?string $end = null;

    #[ORM\Column(type: "string", length: 255, nullable: true)]
    private ?string $percents = null;

    #[ORM\ManyToOne(targetEntity: UserInformation::class, inversedBy: "sections")]
    #[ORM\JoinColumn(name: "user_information_id", referencedColumnName: "id", nullable: false)]
    private ?UserInformation $userInformation;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(?string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getUserInformation(): ?UserInformation
    {
        return $this->userInformation;
    }

    public function setUserInformation(?UserInformation $userInformation): self
    {
        $this->userInformation = $userInformation;

        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(?string $type): static
    {
        $this->type = $type;

        return $this;
    }

    public function getIcon(): ?string
    {
        return $this->icon;
    }

    public function setIcon(?string $icon): static
    {
        $this->icon = $icon;

        return $this;
    }

    public function getStart(): ?string
    {
        return $this->start;
    }

    public function setStart(?string $start): static
    {
        $this->start = $start;

        return $this;
    }

    public function getEnd(): ?string
    {
        return $this->end;
    }

    public function setEnd(?string $end): static
    {
        $this->end = $end;

        return $this;
    }

    public function getPercents(): ?string
    {
        return $this->percents;
    }

    public function setPercents(?string $percents): static
    {
        $this->percents = $percents;

        return $this;
    }

    public function getShtype(): ?string
    {
        return $this->shtype;
    }

    public function setShtype(?string $shtype): static
    {
        $this->shtype = $shtype;

        return $this;
    }
}
