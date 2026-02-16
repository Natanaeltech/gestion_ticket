<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;

/**
 * Repository pour l'entité User
 * Gère les requêtes liées aux utilisateurs
 * 
 * @extends ServiceEntityRepository<User>
 */
class UserRepository extends ServiceEntityRepository implements PasswordUpgraderInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    /**
     * Utilisé pour mettre à niveau (rehash) le mot de passe automatiquement
     * au fil du temps.
     */
    public function upgradePassword(PasswordAuthenticatedUserInterface $user, string $newHashedPassword): void
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', $user::class));
        }

        $user->setPassword($newHashedPassword);
        $this->getEntityManager()->persist($user);
        $this->getEntityManager()->flush();
    }

    /**
     * Trouve tous les techniciens (utilisateurs avec ROLE_TECHNICIEN ou ROLE_ADMIN)
     * 
     * @return User[] Liste des techniciens
     */
    public function findTechniciens(): array
    {
        return $this->createQueryBuilder('u')
            ->where('u.roles LIKE :role_tech OR u.roles LIKE :role_admin')
            ->setParameter('role_tech', '%ROLE_TECHNICIEN%')
            ->setParameter('role_admin', '%ROLE_ADMIN%')
            ->orderBy('u.nom', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Trouve un utilisateur par son email
     * 
     * @param string $email L'email de l'utilisateur
     * @return User|null L'utilisateur ou null s'il n'existe pas
     */
    public function findOneByEmail(string $email): ?User
    {
        return $this->createQueryBuilder('u')
            ->where('u.email = :email')
            ->setParameter('email', $email)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Trouve tous les utilisateurs d'un département
     * 
     * @param string $departement Le nom du département
     * @return User[] Liste des utilisateurs du département
     */
    public function findByDepartement(string $departement): array
    {
        return $this->createQueryBuilder('u')
            ->where('u.departement = :departement')
            ->setParameter('departement', $departement)
            ->orderBy('u.nom', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Compte le nombre total de techniciens
     * 
     * @return int Nombre de techniciens
     */
    public function countTechniciens(): int
    {
        return (int) $this->createQueryBuilder('u')
            ->select('COUNT(u.id)')
            ->where('u.roles LIKE :role_tech OR u.roles LIKE :role_admin')
            ->setParameter('role_tech', '%ROLE_TECHNICIEN%')
            ->setParameter('role_admin', '%ROLE_ADMIN%')
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Compte le nombre total d'utilisateurs normaux
     * 
     * @return int Nombre d'utilisateurs
     */
    public function countUsers(): int
    {
        return (int) $this->createQueryBuilder('u')
            ->select('COUNT(u.id)')
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Recherche des utilisateurs par nom ou prénom
     * 
     * @param string $searchTerm Terme de recherche
     * @return User[] Liste des utilisateurs correspondants
     */
    public function searchByName(string $searchTerm): array
    {
        return $this->createQueryBuilder('u')
            ->where('u.nom LIKE :term OR u.prenom LIKE :term')
            ->setParameter('term', '%' . $searchTerm . '%')
            ->orderBy('u.nom', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Sauvegarde un utilisateur en base de données
     * 
     * @param User $user L'utilisateur à sauvegarder
     * @param bool $flush Si true, applique immédiatement les changements
     */
    public function save(User $user, bool $flush = false): void
    {
        $this->getEntityManager()->persist($user);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * Supprime un utilisateur de la base de données
     * 
     * @param User $user L'utilisateur à supprimer
     * @param bool $flush Si true, applique immédiatement les changements
     */
    public function remove(User $user, bool $flush = false): void
    {
        $this->getEntityManager()->remove($user);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}
