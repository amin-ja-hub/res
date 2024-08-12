<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: "Category")]
class Category
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: "AUTO")]
    #[ORM\Column(type: "integer")]
    private ?int $id = null;

    #[ORM\ManyToMany(targetEntity: Barchasb::class, inversedBy: 'cats')]
    #[ORM\JoinTable(name: 'CatBar')]
    private Collection $barchasbs;

    #[ORM\Column(type: "string", length: 256, nullable: true)]
    private ?string $title = null;

    #[ORM\Column(type: "string", length: 256, nullable: true)]
    private ?string $url = null;

    #[ORM\Column(type: "datetime", nullable: true)]
    private ?\DateTimeInterface $cdate = null;

    #[ORM\Column(type: "datetime", nullable: true)]
    private ?\DateTimeInterface $udate = null;

    #[ORM\Column(type: "integer", nullable: true)]
    private ?int $published = null;

    #[ORM\Column(type: "integer", nullable: true)]
    private ?int $type = null;

    #[ORM\Column(type: "text", nullable: true)]
    private ?string $metaKey = null;

    #[ORM\Column(type: "text", nullable: true)]
    private ?string $metadesc = null;

    #[ORM\Column(type: "integer", nullable: true)]
    private ?int $remove = null;

    #[ORM\ManyToOne(targetEntity: Category::class)]
    #[ORM\JoinColumn(name: "Parent", referencedColumnName: "id", nullable: true)]
    private ?Category $parent = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: "User", referencedColumnName: "id", nullable: true)]
    private ?User $user = null;

    #[ORM\ManyToOne(targetEntity: File::class)]
    #[ORM\JoinColumn(name: "Image", referencedColumnName: "id", nullable: true)]
    private ?File $image = null;

    public function __construct()
    {
        $this->barchasbs = new ArrayCollection();
    }

    // Getters and Setters

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(?string $title): static
    {
        $this->title = $title;
        return $this;
    }

    public function getUrl(): ?string
    {
        return $this->url;
    }

    public function setUrl(?string $url): static
    {
        $this->url = $url;
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

    public function getType(): ?int
    {
        return $this->type;
    }

    public function setType(?int $type): static
    {
        $this->type = $type;
        return $this;
    }

    public function getMetaKey(): ?string
    {
        return $this->metaKey;
    }

    public function setMetaKey(?string $metaKey): static
    {
        $this->metaKey = $metaKey;
        return $this;
    }

    public function getMetadesc(): ?string
    {
        return $this->metadesc;
    }

    public function setMetadesc(?string $metadesc): static
    {
        $this->metadesc = $metadesc;
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

    public function getParent(): ?self
    {
        return $this->parent;
    }

    public function setParent(?self $parent): static
    {
        $this->parent = $parent;
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

    public function getImage(): ?File
    {
        return $this->image;
    }

    public function setImage(?File $image): static
    {
        $this->image = $image;
        return $this;
    }

    /**
     * @return Collection<int, Barchasb>
     */
    public function getBarchasbs(): Collection
    {
        return $this->barchasbs;
    }

    public function addBarchasb(Barchasb $barchasb): static
    {
        if (!$this->barchasbs->contains($barchasb)) {
            $this->barchasbs->add($barchasb);
        }

        return $this;
    }

    public function removeBarchasb(Barchasb $barchasb): static
    {
        $this->barchasbs->removeElement($barchasb);
        return $this;
    }
}
