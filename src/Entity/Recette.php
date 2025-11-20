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

    #[ORM\OneToMany(mappedBy: 'recette', targetEntity: IngredientRecette::class, cascade: ['persist', 'remove'])]
    private Collection $ingredientRecettes;

    #[ORM\Column(length: 255)]
    private ?string $nom = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $descriptions = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $instructions = null;

    #[ORM\Column(type: "integer", nullable: true)]
    private ?int $temps_preparation = null;

    #[ORM\Column(type: "integer", nullable: true)]
    private ?int $temps_cuisson = null;

    #[ORM\Column(length: 80, nullable: true)]
    private ?string $difficulte = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTime $dateCreation = null;

    #[ORM\Column(nullable: true)]
    private ?int $nombreDePortions = null;



    public function setIngredientRecettes(Collection $ingredientRecettes): void
    {
        $this->ingredientRecettes = $ingredientRecettes;
    }

    #[ORM\ManyToMany(targetEntity: Regime::class, mappedBy: 'recettes')]
    private Collection $regimes;

    #[ORM\ManyToMany(targetEntity: Menu::class, mappedBy: 'recettes')]
    private Collection $menus;


    #[Column(name: 'source_url', type: 'string')]
    private ?string $sourceUrl = null;

    public function getSourceUrl(): ?string
    {
        return $this->sourceUrl;
    }

    public function setSourceUrl(?string $sourceUrl): void
    {
        $this->sourceUrl = $sourceUrl;
    }




    public function getDescriptions(): ?string
    {
        return $this->descriptions;
    }

    public function setDescriptions(?string $descriptions): void
    {
        $this->descriptions = $descriptions;
    }


    /**
     * @var Collection<int, UserRecette>
     */
    #[ORM\OneToMany(targetEntity: UserRecette::class, mappedBy: 'recette')]
    private Collection $userRecettes;


    #[ORM\Column(length: 500, nullable: true)]
    private ?string $imageUrl = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $imagePath = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $imageAlt = null;

    // Getters et setters pour les nouvelles propriétés
    public function getImageUrl(): ?string
    {
        return $this->imageUrl;
    }

    public function setImageUrl(?string $imageUrl): static
    {
        $this->imageUrl = $imageUrl;
        return $this;
    }

    public function getImagePath(): ?string
    {
        return $this->imagePath;
    }

    public function setImagePath(?string $imagePath): static
    {
        $this->imagePath = $imagePath;
        return $this;
    }

    public function getImageAlt(): ?string
    {
        return $this->imageAlt;
    }

    public function setImageAlt(?string $imageAlt): static
    {
        $this->imageAlt = $imageAlt;
        return $this;
    }


    public function __construct()
    {
        $this->ingredientRecettes = new ArrayCollection();
        $this->regimes = new ArrayCollection();
        $this->menus = new ArrayCollection();
        $this->userRecettes = new ArrayCollection();
    }

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

    public function getDescription(): ?string
    {
        return $this->descriptions;
    }

    public function setDescription(?string $descriptions): static
    {
        $this->descriptions = $descriptions;
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
        return $this->temps_preparation;
    }

    public function setTempsPreparation(int $temps_preparation): static
    {
        $this->temps_preparation = $temps_preparation;
        return $this;
    }

    public function getTempsCuisson(): ?int
    {
        return $this->temps_cuisson;
    }

    public function setTempsCuisson(?int $temps_cuisson): static
    {
        $this->temps_cuisson = $temps_cuisson;
        return $this;
    }

    public function getDifficulte(): ?string
    {
        return $this->difficulte;
    }

    public function setDifficulte(?string $difficulte): self
    {
        $this->difficulte = $difficulte;
        return $this;
    }

    public function getDateCreation(): ?\DateTime
    {
        return $this->dateCreation;
    }

    public function setDateCreation(?\DateTime $dateCreation): static
    {
        $this->dateCreation = $dateCreation;
        return $this;
    }

    public function getNombreDePortions(): ?int
    {
        return $this->nombreDePortions;
    }

    public function setNombreDePortions(?int $nombreDePortions): static
    {
        $this->nombreDePortions = $nombreDePortions;
        return $this;
    }

    public function getIngredientRecettes(): Collection
    {
        return $this->ingredientRecettes;
    }

    public function addIngredientRecette(IngredientRecette $ingredientRecette): static
    {
        if (!$this->ingredientRecettes->contains($ingredientRecette)) {
            $this->ingredientRecettes[] = $ingredientRecette;
            $ingredientRecette->setRecette($this);
        }
        return $this;
    }

    public function __toString(): string
    {
        $ingredientNames = [];
        foreach ($this->ingredientRecettes as $ingredient) {
            $ingredientNames[] = (string) $ingredient;
        }
        return implode("\n", $ingredientNames);
    }


    public function removeIngredientRecette(IngredientRecette $ingredientRecette): static
    {
        if ($this->ingredientRecettes->removeElement($ingredientRecette)) {
            if ($ingredientRecette->getRecette() === $this) {
                $ingredientRecette->setRecette(null);
            }
        }
        return $this;
    }

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

    /**
     * @return Collection<int, UserRecette>
     */
    public function getUserRecettes(): Collection
    {
        return $this->userRecettes;
    }

    public function addUserRecette(UserRecette $userRecette): static
    {
        if (!$this->userRecettes->contains($userRecette)) {
            $this->userRecettes->add($userRecette);
            $userRecette->setRecette($this);
        }

        return $this;
    }

    public function removeUserRecette(UserRecette $userRecette): static
    {
        if ($this->userRecettes->removeElement($userRecette)) {
            // set the owning side to null (unless already changed)
            if ($userRecette->getRecette() === $this) {
                $userRecette->setRecette(null);
            }
        }

        return $this;
    }
}
