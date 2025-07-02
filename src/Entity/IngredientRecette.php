<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class IngredientRecette
{
#[ORM\Id]
#[ORM\ManyToOne(targetEntity: Recette::class, inversedBy: 'ingredientRecettes')]
#[ORM\JoinColumn(nullable: false)]
private $recette;

#[ORM\Id]
#[ORM\ManyToOne(targetEntity: Ingredient::class, inversedBy: 'ingredientRecettes')]
#[ORM\JoinColumn(nullable: false)]
private $ingredient;

#[ORM\Column]
private ?string $quantite = null;

#[ORM\Column(length: 255)]
private ?string $unite_mesure = null;

// Getters and Setters

public function getRecette(): ?Recette
{
return $this->recette;
}

public function setRecette(?Recette $recette): self
{
$this->recette = $recette;
return $this;
}

public function getIngredient(): ?Ingredient
{
return $this->ingredient;
}

public function setIngredient(?Ingredient $ingredient): self
{
$this->ingredient = $ingredient;
return $this;
}

public function getQuantite(): ?string
{
return $this->quantite;
}

public function setQuantite(string $quantite): self
{
$this->quantite = $quantite;
return $this;
}

public function getUniteMesure(): ?string
{
return $this->unite_mesure;
}

public function setUniteMesure(string $unite_mesure): self
{
$this->unite_mesure = $unite_mesure;
return $this;
}
}
