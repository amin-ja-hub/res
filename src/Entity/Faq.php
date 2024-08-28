<?php

namespace App\Entity;

use App\Repository\FaqEntryRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: FaqEntryRepository::class)]
#[ORM\Table(name: "faq_entries")]
class Faq
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: "AUTO")]
    #[ORM\Column(type: "integer")]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Category::class)]
    #[ORM\JoinColumn(name: "category_id", referencedColumnName: "id", nullable: true)]
    private ?Category $category = null;

    #[ORM\Column(type: "text", nullable: true)]
    private ?string $question = null;

    #[ORM\Column(type: "text", nullable: true)]
    private ?string $answer = null;

    #[ORM\Column(type: "datetime", nullable: true)]
    private ?\DateTimeInterface $cdate = null;

    #[ORM\Column(type: "datetime", nullable: true)]
    private ?\DateTimeInterface $udate = null;

    #[ORM\Column(type: "integer", nullable: true)]
    private ?int $published = null;
    
    #[ORM\Column(type: "integer", nullable: true)]
    private ?int $remove = null;      
    
    #[ORM\ManyToOne(targetEntity: "User")]
    #[ORM\JoinColumn(name: "User", referencedColumnName: "id", nullable: true)]
    private ?User $user = null;    
    
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getQuestion(): ?string
    {
        return $this->question;
    }

    public function setQuestion(string $question): static
    {
        $this->question = $question;

        return $this;
    }

    public function getAnswer(): ?string
    {
        return $this->answer;
    }

    public function setAnswer(string $answer): static
    {
        $this->answer = $answer;

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

    public function getCategory(): ?Category
    {
        return $this->category;
    }

    public function setCategory(?Category $category): static
    {
        $this->category = $category;

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

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;

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
}
