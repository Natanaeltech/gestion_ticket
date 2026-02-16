<?php

namespace App\Repository;

use App\Entity\Ticket;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * Repository pour l'entité Ticket
 * Contient des requêtes personnalisées pour récupérer les tickets
 * 
 * @extends ServiceEntityRepository<Ticket>
 */
class TicketRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Ticket::class);
    }

    /**
     * Trouve tous les tickets ouverts (non résolus et non fermés)
     * 
     * @return Ticket[] Liste des tickets ouverts
     */
    public function findOpenTickets(): array
    {
        return $this->createQueryBuilder('t')
            ->where('t.statut = :ouvert OR t.statut = :en_cours')
            ->setParameter('ouvert', 'ouvert')
            ->setParameter('en_cours', 'en_cours')
            ->orderBy('t.priorite', 'DESC')
            ->addOrderBy('t.dateCreation', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Trouve les tickets non assignés
     * 
     * @return Ticket[] Liste des tickets sans technicien
     */
    public function findUnassignedTickets(): array
    {
        return $this->createQueryBuilder('t')
            ->where('t.technicien IS NULL')
            ->andWhere('t.statut != :ferme')
            ->setParameter('ferme', 'ferme')
            ->orderBy('t.priorite', 'DESC')
            ->addOrderBy('t.dateCreation', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Trouve les tickets assignés à un technicien spécifique
     * 
     * @param User $technicien Le technicien concerné
     * @return Ticket[] Liste des tickets assignés
     */
    public function findByTechnicien(User $technicien): array
    {
        return $this->createQueryBuilder('t')
            ->where('t.technicien = :technicien')
            ->setParameter('technicien', $technicien)
            ->orderBy('t.statut', 'ASC')
            ->addOrderBy('t.priorite', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Trouve les tickets urgents qui ne sont pas encore résolus
     * 
     * @return Ticket[] Liste des tickets urgents
     */
    public function findUrgentTickets(): array
    {
        return $this->createQueryBuilder('t')
            ->where('t.priorite = :urgente')
            ->andWhere('t.statut != :resolu')
            ->andWhere('t.statut != :ferme')
            ->setParameter('urgente', 'urgente')
            ->setParameter('resolu', 'resolu')
            ->setParameter('ferme', 'ferme')
            ->orderBy('t.dateCreation', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Trouve les tickets créés par un utilisateur
     * 
     * @param User $user L'utilisateur créateur
     * @return Ticket[] Liste des tickets de l'utilisateur
     */
    public function findByCreateur(User $user): array
    {
        return $this->createQueryBuilder('t')
            ->where('t.createur = :user')
            ->setParameter('user', $user)
            ->orderBy('t.dateCreation', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Compte les tickets par statut
     * 
     * @return array Tableau associatif avec les comptes par statut
     */
    public function countByStatut(): array
    {
        $results = $this->createQueryBuilder('t')
            ->select('t.statut, COUNT(t.id) as count')
            ->groupBy('t.statut')
            ->getQuery()
            ->getResult();

        // Convertir en tableau associatif
        $counts = [];
        foreach ($results as $result) {
            $counts[$result['statut']] = $result['count'];
        }

        return $counts;
    }

    /**
     * Compte les tickets par priorité
     * 
     * @return array Tableau associatif avec les comptes par priorité
     */
    public function countByPriorite(): array
    {
        $results = $this->createQueryBuilder('t')
            ->select('t.priorite, COUNT(t.id) as count')
            ->groupBy('t.priorite')
            ->getQuery()
            ->getResult();

        $counts = [];
        foreach ($results as $result) {
            $counts[$result['priorite']] = $result['count'];
        }

        return $counts;
    }

    /**
     * Trouve les tickets récents (créés dans les X derniers jours)
     * 
     * @param int $days Nombre de jours
     * @return Ticket[] Liste des tickets récents
     */
    public function findRecentTickets(int $days = 7): array
    {
        $date = new \DateTime();
        $date->modify("-{$days} days");

        return $this->createQueryBuilder('t')
            ->where('t.dateCreation >= :date')
            ->setParameter('date', $date)
            ->orderBy('t.dateCreation', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Recherche de tickets par mot-clé
     * Recherche dans le titre et la description
     * 
     * @param string $keyword Mot-clé de recherche
     * @return Ticket[] Liste des tickets correspondants
     */
    public function searchByKeyword(string $keyword): array
    {
        return $this->createQueryBuilder('t')
            ->where('t.titre LIKE :keyword OR t.description LIKE :keyword')
            ->setParameter('keyword', '%' . $keyword . '%')
            ->orderBy('t.dateCreation', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Trouve les tickets par catégorie
     * 
     * @param string $categorie La catégorie recherchée
     * @return Ticket[] Liste des tickets de cette catégorie
     */
    public function findByCategorie(string $categorie): array
    {
        return $this->createQueryBuilder('t')
            ->where('t.categorie = :categorie')
            ->setParameter('categorie', $categorie)
            ->orderBy('t.dateCreation', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Calcule le temps moyen de résolution des tickets
     * 
     * @return float|null Temps moyen en heures, ou null si aucun ticket résolu
     */
    public function getAverageResolutionTime(): ?float
    {
        $result = $this->createQueryBuilder('t')
            ->select('AVG(TIMESTAMPDIFF(HOUR, t.dateCreation, t.dateResolution)) as avgTime')
            ->where('t.dateResolution IS NOT NULL')
            ->getQuery()
            ->getSingleScalarResult();

        return $result ? (float) $result : null;
    }

    /**
     * Sauvegarde un ticket en base de données
     * 
     * @param Ticket $ticket Le ticket à sauvegarder
     * @param bool $flush Si true, applique immédiatement les changements
     */
    public function save(Ticket $ticket, bool $flush = false): void
    {
        $this->getEntityManager()->persist($ticket);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * Supprime un ticket de la base de données
     * 
     * @param Ticket $ticket Le ticket à supprimer
     * @param bool $flush Si true, applique immédiatement les changements
     */
    public function remove(Ticket $ticket, bool $flush = false): void
    {
        $this->getEntityManager()->remove($ticket);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}
