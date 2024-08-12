<?php

namespace App\Entity;

use App\Repository\BarchasbRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: BarchasbRepository::class)]
class Barchasb
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToMany(targetEntity: Article::class, mappedBy: 'barchasbs')]
    private Collection $articles;

    #[ORM\ManyToMany(targetEntity: Category::class, mappedBy: 'barchasbs')]
    private Collection $category;

    #[ORM\ManyToMany(targetEntity: Article::class, mappedBy: 'barchasbs')]
    private Collection $product;

    #[ORM\Column(type: "string", length: 256, nullable: true)]
    private ?string $title;

    #[ORM\Column(type: "datetime", nullable: true)]
    private ?\DateTimeInterface $cdate;

    #[ORM\Column(type: "integer", nullable: true)]
    private ?int $published;

    #[ORM\Column(type: "integer", nullable: true)]
    private ?int $type;

    #[ORM\ManyToOne(targetEntity: "User")]
    #[ORM\JoinColumn(name: "User", referencedColumnName: "id", nullable: true)]
    private ?User $user = null;

    public function __construct()
    {
        $this->articles = new ArrayCollection();
        $this->category = new ArrayCollection();
        $this->product = new ArrayCollection();
    }


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

    public function getCdate(): ?\DateTimeInterface
    {
        return $this->cdate;
    }

    public function setCdate(?\DateTimeInterface $cdate): static
    {
        $this->cdate = $cdate;

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

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;

        return $this;
    }

    /**
     * @return Collection<int, Article>
     */
    public function getArticles(): Collection
    {
        return $this->articles;
    }

    public function addArticle(Article $article): static
    {
        if (!$this->articles->contains($article)) {
            $this->articles->add($article);
            $article->addBarchasb($this);
        }

        return $this;
    }

    public function removeArticle(Article $article): static
    {
        if ($this->articles->removeElement($article)) {
            $article->removeBarchasb($this);
        }

        return $this;
    }

    /**
     * @return Collection<int, Category>
     */
    public function getCategory(): Collection
    {
        return $this->category;
    }

    public function addCategory(Category $category): static
    {
        if (!$this->category->contains($category)) {
            $this->category->add($category);
            $category->addBarchasb($this);
        }

        return $this;
    }

    public function removeCategory(Category $category): static
    {
        if ($this->category->removeElement($category)) {
            $category->removeBarchasb($this);
        }

        return $this;
    }

    /**
     * @return Collection<int, Article>
     */
    public function getProduct(): Collection
    {
        return $this->product;
    }

    public function addProduct(Article $product): static
    {
        if (!$this->product->contains($product)) {
            $this->product->add($product);
            $product->addBarchasb($this);
        }

        return $this;
    }

    public function removeProduct(Article $product): static
    {
        if ($this->product->removeElement($product)) {
            $product->removeBarchasb($this);
        }

        return $this;
    }
}
