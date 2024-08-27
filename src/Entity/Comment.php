<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: "Comment")]
class Comment
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: "AUTO")]
    #[ORM\Column(type: "integer")]
    private ?int $id;

    #[ORM\Column(type: "string", length: 256, nullable: true)]
    private ?string $fullName;

    #[ORM\Column(type: "string", length: 256, nullable: true)]
    private ?string $phone;

    #[ORM\Column(type: "text", nullable: true)]
    private ?string $text;

    #[ORM\Column(type: "datetime", nullable: true)]
    private ?\DateTimeInterface $cdate;

    #[ORM\Column(type: "integer", nullable: true)]
    private ?int $published;

    #[ORM\Column(type: "datetime", nullable: true)]
    private ?\DateTimeInterface $udate;

    #[ORM\Column(type: "integer", nullable: true)]
    private ?int $type;

    #[ORM\Column(type: "integer", nullable: true)]
    private ?int $countLike;

    #[ORM\Column(type: "integer", nullable: true)]
    private ?int $remove;

    #[ORM\ManyToOne(targetEntity: "Article")]
    #[ORM\JoinColumn(name: "article", referencedColumnName: "id", nullable: true)]
    private ?Article $article = null; // Notice the nullable type declaration

    #[ORM\ManyToOne(targetEntity: "Product")]
    #[ORM\JoinColumn(name: "product", referencedColumnName: "id", nullable: true)]
    private ?Product $product = null; // Notice the nullable type declaration    
    
    #[ORM\ManyToOne(targetEntity: "Comment")]
    #[ORM\JoinColumn(name: "Comment", referencedColumnName: "id", nullable: true)]
    private ?Comment $comment = null;

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

    public function getPublished(): ?int
    {
        return $this->published;
    }

    public function setPublished(?int $published): static
    {
        $this->published = $published;

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

    public function getType(): ?int
    {
        return $this->type;
    }

    public function setType(?int $type): static
    {
        $this->type = $type;

        return $this;
    }

    public function getCountLike(): ?int
    {
        return $this->countLike;
    }

    public function setCountLike(?int $countLike): static
    {
        $this->countLike = $countLike;

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

    public function getArticle(): ?Article
    {
        return $this->article;
    }

    public function setArticle(?Article $article): static
    {
        $this->article = $article;

        return $this;
    }

    public function getComment(): ?Comment
    {
        return $this->comment;
    }

    public function setComment(?Comment $comment): static
    {
        $this->comment = $comment;

        return $this;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(?string $phone): static
    {
        $this->phone = $phone;

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
}
