<?php

namespace App\Entity;

use App\Repository\UserIngredientRepository;
use DateTime;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: UserIngredientRepository::class)]
class UserIngredient
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 5, scale: 5, nullable: true)]
    private ?string $quantity = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $unite_mesure = null;

    #[ORM\Column(nullable: true)]
    private ?DateTime $dateExpiration = null;

    #[ORM\Column(nullable: true)]
    private DateTime|null $addedAt = null;

    #[ORM\Column(nullable: true)]
    private DateTime|null $AjouteA = null;

    public function getAddedAt(): ?DateTime
    {
        return $this->addedAt;
    }

    public function setAddedAt(DateTime|null $addedAt): void
    {
        $this->addedAt = $addedAt;
    }

    #[ORM\ManyToOne(inversedBy: 'userIngredients')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    #[ORM\ManyToOne(inversedBy: 'userIngredients')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Ingredient $ingredient = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getQuantity(): ?string
    {
        return $this->quantity;
    }

    public function setQuantity(?string $quantity): static
    {
        $this->quantity = $quantity;

        return $this;
    }

    public function getUniteMesure(): ?string
    {
        return $this->unite_mesure;
    }

    public function setUniteMesure(?string $unite_mesure): static
    {
        $this->unite_mesure = $unite_mesure;

        return $this;
    }

    public function getDateExpiration(): ?DateTime
    {
        return $this->dateExpiration;
    }

    public function setDateExpiration(?DateTime $dateExpiration): static
    {
        $this->dateExpiration = $dateExpiration;

        return $this;
    }

    public function getAjouteA(): ?DateTime
    {
        return $this->AjouteA;
    }

    public function setAjouteA(?DateTime $AjouteA): static
    {
        $this->AjouteA = $AjouteA;

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

    public function getIngredient(): ?Ingredient
    {
        return $this->ingredient;
    }

    public function setIngredient(?Ingredient $ingredient): static
    {
        $this->ingredient = $ingredient;

        return $this;
    }
}
