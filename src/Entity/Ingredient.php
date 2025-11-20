<?php

namespace App\Entity;

use App\Repository\IngredientRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;


#[ORM\Entity(repositoryClass: IngredientRepository::class)]
class Ingredient
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $nom = null;

    #[ORM\Column(length: 255)]
    private ?string $categorie = null;

    #[ORM\Column(type: "integer", nullable: true)]
    private $quantite = null;

    #[ORM\Column(length: 255, nullable:true)]
    private ?string $unite_mesure = null;

    // Getters and Setters

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): static
    {
        $this->nom = $nom;

        return $this;
    }

    public function getQuantite(): ?string
    {
        return $this->quantite;
    }

    public function setQuantite(string $quantite): static
    {
        $this->quantite = $quantite;

        return $this;
    }

    #[ORM\OneToMany(mappedBy: 'ingredient', targetEntity: IngredientRecette::class)]
    private Collection $ingredientRecettes;

    /**
     * @var Collection<int, UserIngredient>
     */
    #[ORM\OneToMany(targetEntity: UserIngredient::class, mappedBy: 'ingredient')]
    private Collection $userIngredients;

    public function __construct()
    {
        $this->ingredientRecettes = new ArrayCollection();
        $this->userIngredients = new ArrayCollection();
    }

    public function getIngredientRecettes(): Collection
    {
        return $this->ingredientRecettes;
    }

    public function getCategorie(): ?string
    {
        return $this->categorie;
    }

    public function setCategorie(string $categorie): static
    {
        $this->categorie = $categorie;

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

    /**
     * @return Collection<int, UserIngredient>
     */
    public function getUserIngredients(): Collection
    {
        return $this->userIngredients;
    }

    public function addUserIngredient(UserIngredient $userIngredient): static
    {
        if (!$this->userIngredients->contains($userIngredient)) {
            $this->userIngredients->add($userIngredient);
            $userIngredient->setIngredient($this);
        }

        return $this;
    }

    public function removeUserIngredient(UserIngredient $userIngredient): static
    {
        if ($this->userIngredients->removeElement($userIngredient)) {
            // set the owning side to null (unless already changed)
            if ($userIngredient->getIngredient() === $this) {
                $userIngredient->setIngredient(null);
            }
        }

        return $this;
    }

}
