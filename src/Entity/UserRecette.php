<?php

namespace App\Entity;

use App\Repository\UserRecetteRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: UserRecetteRepository::class)]
class UserRecette
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'userRecettes')]
    private ?User $user = null;

    #[ORM\ManyToOne(inversedBy: 'userRecettes')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Recette $recette = null;

    #[ORM\Column(nullable: true)]
    private ?bool $Favoritis = null;

    #[ORM\Column(nullable: true)]
    private ?int $notation = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTime $cuisineA = null;

    #[ORM\Column(nullable: true)]
    private ?int $nombreCuisiné = null;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getRecette(): ?Recette
    {
        return $this->recette;
    }

    public function setRecette(?Recette $recette): static
    {
        $this->recette = $recette;

        return $this;
    }

    public function isFavoritis(): ?bool
    {
        return $this->Favoritis;
    }

    public function setFavoritis(?bool $Favoritis): static
    {
        $this->Favoritis = $Favoritis;

        return $this;
    }

    public function getNotation(): ?int
    {
        return $this->notation;
    }

    public function setNotation(?int $notation): static
    {
        $this->notation = $notation;

        return $this;
    }

    public function getCuisineA(): ?\DateTime
    {
        return $this->cuisineA;
    }

    public function setCuisineA(?\DateTime $cuisineA): static
    {
        $this->cuisineA = $cuisineA;

        return $this;
    }

    public function getNombreCuisiné(): ?int
    {
        return $this->nombreCuisiné;
    }

    public function setNombreCuisiné(?int $nombreCuisiné): static
    {
        $this->nombreCuisiné = $nombreCuisiné;

        return $this;
    }
}
