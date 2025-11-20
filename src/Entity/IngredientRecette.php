<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Repository\IngredientRecetteRepository;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;

#[ORM\Entity(repositoryClass: IngredientRecetteRepository::class)]
class IngredientRecette
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Recette::class, inversedBy: 'ingredientRecettes')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Recette $recette = null;




    // Dans IngredientRecette
    #[ManyToOne(targetEntity: Ingredient::class)]
    #[JoinColumn(name: 'ingredient_id', referencedColumnName: 'id', nullable: false)]
    private ?Ingredient $ingredient = null;

    public function setIngredient(?Ingredient $ingredient): self
    {
        $this->ingredient = $ingredient;
        return $this;
    }

    /*
#[ORM\Id]
#[ORM\ManyToOne(targetEntity: Recette::class, inversedBy: 'ingredientRecettes', cascade: ['persist'])]
#[ORM\JoinColumn(nullable: false)]
private ?Recette $recette = null;

#[ORM\Id]
#[ORM\ManyToOne(targetEntity: Ingredient::class, inversedBy: 'ingredientRecettes')]
#[ORM\JoinColumn(nullable: false)]
private $ingredient;
*/
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
