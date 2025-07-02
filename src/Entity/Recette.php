<?php

namespace App\Entity;

use App\Repository\RecetteRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: RecetteRepository::class)]
class Recette
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $nom = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $instructions = null;

    #[ORM\Column]
    private ?int $tempsPreparation = null;

    #[ORM\Column(type: "integer", nullable: true)]
    private ?int $tempsCuisson;

    public function getTempsCuisson(): ?int
    {
        return $this->tempsCuisson;
    }

    public function setTempsCuisson(?int $tempsCuisson): self
    {
        $this->tempsCuisson = $tempsCuisson;
    }

    #[ORM\Column(length: 80, nullable:true)]
    private ?string $difficulte = null;

    #[ORM\Column(nullable:true)]
    private ?\DateTime $dateCreation = null;

    #[ORM\Column(nullable:true)]
    private ?int $nombreDePortions = null;


    #[ORM\OneToMany(mappedBy: 'recette', targetEntity: IngredientRecette::class)]
    private Collection $ingredientRecettes;

    public function __construct()
    {
        $this->ingredientRecettes = new ArrayCollection();
    }

    public function getIngredientRecettes(): Collection
    {
        return $this->ingredientRecettes;
    }

    public function addIngredientRecette(IngredientRecette $ingredientRecette): self
    {
        if (!$this->ingredientRecettes->contains($ingredientRecette)) {
            $this->ingredientRecettes[] = $ingredientRecette;
            $ingredientRecette->setRecette($this);
        }

        return $this;
    }

    public function removeIngredientRecette(IngredientRecette $ingredientRecette): self
    {
        if ($this->ingredientRecettes->removeElement($ingredientRecette)) {
            // set the owning side to null (unless already changed)
            if ($ingredientRecette->getRecette() === $this) {
                $ingredientRecette->setRecette(null);
            }
        }

        return $this;
    }




//
//    /**
//     * @var Collection<int, Ingredient>
//     */
//    #[ORM\ManyToMany(targetEntity: Ingredient::class, inversedBy: 'recettes')]
//    private Collection $ingredients;

//    /**
//     * @var Collection<int, Regime>
//     */
//    #[ORM\ManyToMany(targetEntity: Regime::class, mappedBy: 'recettes')]
//    private Collection $regimes;
//
//    /**
//     * @var Collection<int, Menu>
//     */
//    #[ORM\ManyToMany(targetEntity: Menu::class, mappedBy: 'recettes')]
//    private Collection $menus;
//
//    public function __construct()
//    {
//        $this->ingredients = new ArrayCollection();
//        $this->regimes = new ArrayCollection();
//        $this->menus = new ArrayCollection();
//    }
//


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

    public function getInstructions(): ?string
    {
        return $this->instructions;
    }

    public function setInstructions(string $instructions): static
    {
        $this->instructions = $instructions;

        return $this;
    }

    public function getTempsPreparation(): ?int
    {
        return $this->tempsPreparation;
    }

    public function setTempsPreparation(int $tempsPreparation): static
    {
        $this->tempsPreparation = $tempsPreparation;

        return $this;
    }

    public function getDifficulte(): ?string
    {
        return $this->difficulte;
    }

    public function setDifficulte(string $difficulte): static
    {
        $this->difficulte = $difficulte;

        return $this;
    }

    public function getDateCreation(): ?\DateTime
    {
        return $this->dateCreation;
    }

    public function setDateCreation(\DateTime $dateCreation): static
    {
        $this->dateCreation = $dateCreation;

        return $this;
    }

    public function getNombreDePortions(): ?int
    {
        return $this->nombreDePortions;
    }

    public function setNombreDePortions(int $nombreDePortions): static
    {
        $this->nombreDePortions = $nombreDePortions;

        return $this;
    }
//
//    /**
//     * @return Collection<int, Ingredient>
//     */
//    public function getIngredients(): Collection
//    {
//        return $this->ingredients;
//    }
//
//    public function addIngredient(Ingredient $ingredient): self
//    {
//        if (!$this->ingredients->contains($ingredient)) {
//            $this->ingredients->add($ingredient);
//        }
//
//        return $this;
//    }
//
//    public function removeIngredient(Ingredient $ingredient): self
//    {
//        $this->ingredients->removeElement($ingredient);
//
//        return $this;
//    }

    /**
     * @return Collection<int, Regime>
     */
    public function getRegimes(): Collection
    {
        return $this->regimes;
    }

    public function addRegime(Regime $regime): static
    {
        if (!$this->regimes->contains($regime)) {
            $this->regimes->add($regime);
            $regime->addRecette($this);
        }

        return $this;
    }

    public function removeRegime(Regime $regime): static
    {
        if ($this->regimes->removeElement($regime)) {
            $regime->removeRecette($this);
        }

        return $this;
    }

    /**
     * @return Collection<int, Menu>
     */
    public function getMenus(): Collection
    {
        return $this->menus;
    }

    public function addMenu(Menu $menu): static
    {
        if (!$this->menus->contains($menu)) {
            $this->menus->add($menu);
            $menu->addRecette($this);
        }

        return $this;
    }

    public function removeMenu(Menu $menu): static
    {
        if ($this->menus->removeElement($menu)) {
            $menu->removeRecette($this);
        }

        return $this;
    }



}
