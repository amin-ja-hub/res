<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: "Article")]
class Article
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: "AUTO")]
    #[ORM\Column(type: "integer")]
    private ?int $id;

    #[ORM\ManyToMany(targetEntity: Barchasb::class, inversedBy: 'articles')]
    #[ORM\JoinTable(name: 'ArticleBar')]
    private Collection $barchasbs;

    #[ORM\Column(type: "string", length: 256, nullable: true)]
    private ?string $title;

    #[ORM\Column(type: "text", nullable: true)]
    private ?string $text;

    #[ORM\Column(type: "datetime", nullable: true)]
    private ?\DateTimeInterface $cdate;

    #[ORM\Column(type: "datetime", nullable: true)]
    private ?\DateTimeInterface $udate;

    #[ORM\Column(type: "text", nullable: true)]
    private ?string $metadesc;

    #[ORM\Column(type: "integer", nullable: true)]
    private ?int $published;

    #[ORM\Column(type: "integer", nullable: true)]
    private ?int $type;

    #[ORM\Column(type: "string", length: 256, nullable: true)]
    private ?string $url;

    #[ORM\Column(type: "integer", nullable: true)]
    private ?int $bazdid;

    #[ORM\Column(type: "integer", nullable: true)]
    private ?int $remove;

    #[ORM\ManyToOne(targetEntity: "User")]
    #[ORM\JoinColumn(name: "User", referencedColumnName: "id", nullable: true)]
    private ?User $user = null;

    #[ORM\ManyToOne(targetEntity: "File")]
    #[ORM\JoinColumn(name: "Image", referencedColumnName: "id", nullable: true)]
    private ?File $image = null;

    #[ORM\ManyToOne(targetEntity: "Category")]
    #[ORM\JoinColumn(name: "Category", referencedColumnName: "id", nullable: true)]
    private ?Category $category= null;

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

    public function getText(): ?string
    {
        return $this->text;
    }

    public function setText(?string $text): static
    {
        $this->text = $text;

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

    public function getMetaKey(): ?string
    {
        return $this->metaKey;
    }

    public function setMetadesc(?string $metadesc): static
    {
        $this->metadesc = $metadesc;

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

    public function getUrl(): ?string
    {
        return $this->url;
    }

    public function setUrl(?string $url): static
    {
        $this->url = $url;

        return $this;
    }

    public function getBazdid(): ?int
    {
        return $this->bazdid;
    }

    public function setBazdid(?int $bazdid): static
    {
        $this->bazdid = $bazdid;

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

    public function getCategory(): ?Category
    {
        return $this->category;
    }

    public function setCategory(?Category $category): static
    {
        $this->category = $category;

        return $this;
    }

    public function getMetadesc(): ?string
    {
        return $this->metadesc;
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
