<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Entité représentant un utilisateur du système
 */
#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: 'users')]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    /**
     * Identifiant unique de l'utilisateur
     */
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    /**
     * Email de l'utilisateur (utilisé pour la connexion)
     */
    #[ORM\Column(length: 180, unique: true)]
    private ?string $email = null;

    /**
     * Rôles de l'utilisateur (ROLE_USER, ROLE_TECHNICIEN, ROLE_ADMIN)
     */
    #[ORM\Column]
    private array $roles = [];

    /**
     * Mot de passe hashé
     */
    #[ORM\Column]
    private ?string $password = null;

    /**
     * Nom complet de l'utilisateur
     */
    #[ORM\Column(length: 255)]
    private ?string $nom = null;

    /**
     * Prénom de l'utilisateur
     */
    #[ORM\Column(length: 255)]
    private ?string $prenom = null;

    /**
     * Département de l'utilisateur
     */
    #[ORM\Column(length: 100, nullable: true)]
    private ?string $departement = null;

    /**
     * Téléphone de l'utilisateur
     */
    #[ORM\Column(length: 20, nullable: true)]
    private ?string $telephone = null;

    /**
     * Collection des tickets créés par cet utilisateur
     */
    #[ORM\OneToMany(targetEntity: Ticket::class, mappedBy: 'createur')]
    private Collection $tickets;

    public function __construct()
    {
        $this->tickets = new ArrayCollection();
        $this->roles = ['ROLE_USER'];
    }

    // Getters et Setters

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
     * Méthode requise par UserInterface
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
        $roles = $this->roles;
        // Garantit que chaque utilisateur a au moins ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function setRoles(array $roles): static
    {
        $this->roles = $roles;
        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;
        return $this;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials(): void
    {
        // Efface les données sensibles temporaires
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

    public function getPrenom(): ?string
    {
        return $this->prenom;
    }

    public function setPrenom(string $prenom): static
    {
        $this->prenom = $prenom;
        return $this;
    }

    public function getDepartement(): ?string
    {
        return $this->departement;
    }

    public function setDepartement(?string $departement): static
    {
        $this->departement = $departement;
        return $this;
    }

    public function getTelephone(): ?string
    {
        return $this->telephone;
    }

    public function setTelephone(?string $telephone): static
    {
        $this->telephone = $telephone;
        return $this;
    }

    /**
     * @return Collection<int, Ticket>
     */
    public function getTickets(): Collection
    {
        return $this->tickets;
    }

    public function addTicket(Ticket $ticket): static
    {
        if (!$this->tickets->contains($ticket)) {
            $this->tickets->add($ticket);
            $ticket->setCreateur($this);
        }

        return $this;
    }

    public function removeTicket(Ticket $ticket): static
    {
        if ($this->tickets->removeElement($ticket)) {
            // set the owning side to null (unless already changed)
            if ($ticket->getCreateur() === $this) {
                $ticket->setCreateur(null);
            }
        }

        return $this;
    }

    /**
     * Retourne le nom complet de l'utilisateur
     */
    public function getNomComplet(): string
    {
        return $this->prenom . ' ' . $this->nom;
    }

    /**
     * Vérifie si l'utilisateur est un technicien
     */
    public function isTechnicien(): bool
    {
        return in_array('ROLE_TECHNICIEN', $this->roles) || in_array('ROLE_ADMIN', $this->roles);
    }

    /**
     * Vérifie si l'utilisateur est un administrateur
     */
    public function isAdmin(): bool
    {
        return in_array('ROLE_ADMIN', $this->roles);
    }
}
