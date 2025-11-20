<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_EMAIL', fields: ['email'])]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 180)]
    private ?string $email = null;

    /**
     * @var list<string> The user roles
     */
    #[ORM\Column(type: 'json')] // ✅ FIXED: Added type: 'json'
    private array $roles = [];

    /**
     * @var string The hashed password
     */
    #[ORM\Column]
    private ?string $password = null;

    #[ORM\Column(length: 100)]
    #[Assert\NotBlank(message: 'Le prénom ne peut pas être vide.')]
    #[Assert\Length(min: 2, max: 100, minMessage: 'Le prénom doit faire au moins 2 caractères.')]
    private ?string $firstName = null;

    #[ORM\Column(length: 100)]
    #[Assert\NotBlank(message: 'Le nom ne peut pas être vide.')]
    #[Assert\Length(min: 2, max: 100, minMessage: 'Le nom doit faire au moins 2 caractères.')]
    private ?string $lastName = null;

    /**
     * @var Collection<int, Menu>
     */
    #[ORM\OneToMany(targetEntity: Menu::class, mappedBy: 'user')]
    private Collection $menus;

    // Missing properties that are referenced in your methods
    private ?string $plainPassword = null;
    private ?string $passwordConfirmation = null;
    private ?string $emailConfirmation = null;
    private ?\DateTimeInterface $createdAt = null;
    private ?string $resetToken = null;
    private ?\DateTimeInterface $resetTokenExpirationDate = null;

    /**
     * @var Collection<int, UserIngredient>
     */
    #[ORM\OneToMany(targetEntity: UserIngredient::class, mappedBy: 'user')]
    private Collection $userIngredients;

    /**
     * @var Collection<int, UserRecette>
     */
    #[ORM\OneToMany(targetEntity: UserRecette::class, mappedBy: 'user')]
    private Collection $userRecettes;

    public function __construct()
    {
        $this->menus = new ArrayCollection();
        $this->roles = []; // Ensure roles is initialized as an array
        $this->userIngredients = new ArrayCollection();
        $this->userRecettes = new ArrayCollection();
    }

    // Constantes pour les rôles
    public const ROLE_USER = 'ROLE_USER';
    public const ROLE_ADMIN = 'ROLE_ADMIN';
    public const ROLE_SUPER_ADMIN = 'ROLE_SUPER_ADMIN';

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;
        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        // Initialiser un tableau vide pour les rôles
        $roles = [];

        try {
            // Vérifier le type de données stocké
            if ($this->roles === null) {
                $roles = [];
            } elseif (is_array($this->roles)) {
                $roles = $this->roles;
            } elseif (is_string($this->roles)) {
                // Si c'est une chaîne, essayer de décoder le JSON
                if (empty($this->roles)) {
                    $roles = [];
                } elseif ($this->roles === '[]' || $this->roles === 'null') {
                    $roles = [];
                } else {
                    // Essayer de décoder en JSON
                    $decoded = json_decode($this->roles, true);
                    if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                        $roles = $decoded;
                    } else {
                        // Si ce n'est pas du JSON valide, traiter comme une chaîne simple
                        $roles = [$this->roles];
                    }
                }
            } else {
                // Type de données inattendu, utiliser un tableau vide
                $roles = [];
            }

            // Nettoyer les rôles (supprimer les valeurs vides)
            $roles = array_filter($roles, function($role) {
                return !empty($role) && is_string($role);
            });

        } catch (\Exception $e) {
            // En cas d'erreur, utiliser un tableau vide
            $roles = [];
        }

        // Garantir que chaque utilisateur a au moins ROLE_USER
        if (!in_array(self::ROLE_USER, $roles)) {
            $roles[] = self::ROLE_USER;
        }

        return array_values(array_unique($roles));
    }

// Also make your setRoles method more robust:
    public function setRoles(array $roles): self
    {
        // Ensure we're always storing an array
        $this->roles = array_values(array_unique($roles));
        return $this;
    }

    public function addRole(string $role): self
    {
        if (!in_array($role, $this->roles)) {
            $this->roles[] = $role;
        }
        return $this;
    }

    public function removeRole(string $role): self
    {
        if (($key = array_search($role, $this->roles)) !== false) {
            unset($this->roles[$key]);
            $this->roles = array_values($this->roles); // Réindexer
        }
        return $this;
    }

    public function hasRole(string $role): bool
    {
        return in_array($role, $this->getRoles());
    }

    public function isAdmin(): bool
    {
        return $this->hasRole(self::ROLE_ADMIN) || $this->hasRole(self::ROLE_SUPER_ADMIN);
    }

    public function isSuperAdmin(): bool
    {
        return $this->hasRole(self::ROLE_SUPER_ADMIN);
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(string $firstName): static
    {
        $this->firstName = $firstName;
        return $this;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(string $lastName): static
    {
        $this->lastName = $lastName;
        return $this;
    }

    public function getFullName(): string
    {
        return $this->firstName . ' ' . $this->lastName; // ✅ FIXED: Added space between names
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;
        return $this;
    }

    public function getPlainPassword(): ?string
    {
        return $this->plainPassword;
    }

    public function setPlainPassword(?string $plainPassword): static
    {
        $this->plainPassword = $plainPassword;
        return $this;
    }

    public function getPasswordConfirmation(): ?string
    {
        return $this->passwordConfirmation;
    }

    public function setPasswordConfirmation(?string $passwordConfirmation): static
    {
        $this->passwordConfirmation = $passwordConfirmation;
        return $this;
    }

    public function getEmailConfirmation(): ?string
    {
        return $this->emailConfirmation;
    }

    public function setEmailConfirmation(?string $emailConfirmation): static
    {
        $this->emailConfirmation = $emailConfirmation;
        return $this;
    }

    public function getSalt(): ?string
    {
        return null; // No salt needed with modern password hashers
    }

    public function getUsername(): string
    {
        return $this->email;
    }

    public function getConfirmationUrl(): string
    {
        return 'http://localhost:8000/confirm-email/' . $this->getId();
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): static
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    public function getResetToken(): ?string
    {
        return $this->resetToken;
    }

    public function setResetToken(?string $resetToken): static
    {
        $this->resetToken = $resetToken;
        return $this;
    }

    public function getResetTokenExpirationDate(): ?\DateTimeInterface
    {
        return $this->resetTokenExpirationDate;
    }

    public function setResetTokenExpirationDate(?\DateTimeInterface $resetTokenExpirationDate): static
    {
        $this->resetTokenExpirationDate = $resetTokenExpirationDate;
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
            $menu->setUser($this);
        }
        return $this;
    }

    public function removeMenu(Menu $menu): static
    {
        if ($this->menus->removeElement($menu)) {
            // set the owning side to null (unless already changed)
            if ($menu->getUser() === $this) {
                $menu->setUser(null);
            }
        }
        return $this;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials(): void
    {
        // If you store any temporary, sensitive data on the user, clear it here
        $this->plainPassword = null;
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
            $userIngredient->setUser($this);
        }

        return $this;
    }

    public function removeUserIngredient(UserIngredient $userIngredient): static
    {
        if ($this->userIngredients->removeElement($userIngredient)) {
            // set the owning side to null (unless already changed)
            if ($userIngredient->getUser() === $this) {
                $userIngredient->setUser(null);
            }
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
            $userRecette->setUser($this);
        }

        return $this;
    }

    public function removeUserRecette(UserRecette $userRecette): static
    {
        if ($this->userRecettes->removeElement($userRecette)) {
            // set the owning side to null (unless already changed)
            if ($userRecette->getUser() === $this) {
                $userRecette->setUser(null);
            }
        }

        return $this;
    }
}
