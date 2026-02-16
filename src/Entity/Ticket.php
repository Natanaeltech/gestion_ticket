<?php

namespace App\Entity;

use App\Repository\TicketRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

/**
 * Entité représentant un ticket de support informatique
 */
#[ORM\Entity(repositoryClass: TicketRepository::class)]
#[ORM\Table(name: 'tickets')]
class Ticket
{
    /**
     * Identifiant unique du ticket
     */
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    /**
     * Titre ou sujet du ticket
     */
    #[ORM\Column(length: 255)]
    private ?string $titre = null;

    /**
     * Description détaillée du problème
     */
    #[ORM\Column(type: Types::TEXT)]
    private ?string $description = null;

    /**
     * Statut du ticket: 'ouvert', 'en_cours', 'resolu', 'ferme'
     */
    #[ORM\Column(length: 50)]
    private ?string $statut = 'ouvert';

    /**
     * Priorité du ticket: 'basse', 'normale', 'haute', 'urgente'
     */
    #[ORM\Column(length: 50)]
    private ?string $priorite = 'normale';

    /**
     * Catégorie du problème: 'materiel', 'logiciel', 'reseau', 'compte', 'autre'
     */
    #[ORM\Column(length: 100)]
    private ?string $categorie = null;

    /**
     * Utilisateur qui a créé le ticket
     */
    #[ORM\ManyToOne(inversedBy: 'tickets')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $createur = null;

    /**
     * Technicien assigné au ticket (peut être null si non assigné)
     */
    #[ORM\ManyToOne]
    private ?User $technicien = null;

    /**
     * Date de création du ticket
     */
    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $dateCreation = null;

    /**
     * Date de dernière mise à jour
     */
    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $dateMiseAJour = null;

    /**
     * Date de résolution du ticket
     */
    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $dateResolution = null;

    public function __construct()
    {
        $this->dateCreation = new \DateTime();
        $this->statut = 'ouvert';
        $this->priorite = 'normale';
    }

    // Getters et Setters

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitre(): ?string
    {
        return $this->titre;
    }

    public function setTitre(string $titre): static
    {
        $this->titre = $titre;
        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;
        return $this;
    }

    public function getStatut(): ?string
    {
        return $this->statut;
    }

    public function setStatut(string $statut): static
    {
        $this->statut = $statut;
        $this->dateMiseAJour = new \DateTime();
        
        // Si le statut passe à 'resolu' ou 'ferme', on enregistre la date de résolution
        if (in_array($statut, ['resolu', 'ferme']) && $this->dateResolution === null) {
            $this->dateResolution = new \DateTime();
        }
        
        return $this;
    }

    public function getPriorite(): ?string
    {
        return $this->priorite;
    }

    public function setPriorite(string $priorite): static
    {
        $this->priorite = $priorite;
        return $this;
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

    public function getCreateur(): ?User
    {
        return $this->createur;
    }

    public function setCreateur(?User $createur): static
    {
        $this->createur = $createur;
        return $this;
    }

    public function getTechnicien(): ?User
    {
        return $this->technicien;
    }

    public function setTechnicien(?User $technicien): static
    {
        $this->technicien = $technicien;
        $this->dateMiseAJour = new \DateTime();
        return $this;
    }

    public function getDateCreation(): ?\DateTimeInterface
    {
        return $this->dateCreation;
    }

    public function setDateCreation(\DateTimeInterface $dateCreation): static
    {
        $this->dateCreation = $dateCreation;
        return $this;
    }

    public function getDateMiseAJour(): ?\DateTimeInterface
    {
        return $this->dateMiseAJour;
    }

    public function setDateMiseAJour(?\DateTimeInterface $dateMiseAJour): static
    {
        $this->dateMiseAJour = $dateMiseAJour;
        return $this;
    }

    public function getDateResolution(): ?\DateTimeInterface
    {
        return $this->dateResolution;
    }

    public function setDateResolution(?\DateTimeInterface $dateResolution): static
    {
        $this->dateResolution = $dateResolution;
        return $this;
    }

    /**
     * Retourne le badge CSS approprié selon le statut
     */
    public function getStatutBadgeClass(): string
    {
        return match($this->statut) {
            'ouvert' => 'badge bg-primary',
            'en_cours' => 'badge bg-warning',
            'resolu' => 'badge bg-success',
            'ferme' => 'badge bg-secondary',
            default => 'badge bg-info'
        };
    }

    /**
     * Retourne la classe CSS appropriée selon la priorité
     */
    public function getPrioriteBadgeClass(): string
    {
        return match($this->priorite) {
            'basse' => 'badge bg-secondary',
            'normale' => 'badge bg-info',
            'haute' => 'badge bg-warning',
            'urgente' => 'badge bg-danger',
            default => 'badge bg-light'
        };
    }
}
