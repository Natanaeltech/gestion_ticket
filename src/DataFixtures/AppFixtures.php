<?php

namespace App\DataFixtures;

use App\Entity\Ticket;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

/**
 * Fixtures pour charger des données de test dans la base de données
 * 
 * Installation : composer require --dev doctrine/doctrine-fixtures-bundle
 * Utilisation : php bin/console doctrine:fixtures:load
 */
class AppFixtures extends Fixture
{
    private UserPasswordHasherInterface $passwordHasher;

    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
    }

    /**
     * Charge les données de test
     */
    public function load(ObjectManager $manager): void
    {
        // ========================================
        // Création des utilisateurs
        // ========================================
        
        // Administrateur
        $admin = new User();
        $admin->setEmail('admin@example.com');
        $admin->setRoles(['ROLE_ADMIN']);
        $admin->setPassword($this->passwordHasher->hashPassword($admin, 'password123'));
        $admin->setNom('Admin');
        $admin->setPrenom('Système');
        $admin->setDepartement('IT');
        $admin->setTelephone('01 23 45 67 89');
        $manager->persist($admin);

        // Techniciens
        $tech1 = new User();
        $tech1->setEmail('tech1@example.com');
        $tech1->setRoles(['ROLE_TECHNICIEN']);
        $tech1->setPassword($this->passwordHasher->hashPassword($tech1, 'password123'));
        $tech1->setNom('Dupont');
        $tech1->setPrenom('Jean');
        $tech1->setDepartement('Support Technique');
        $tech1->setTelephone('01 23 45 67 90');
        $manager->persist($tech1);

        $tech2 = new User();
        $tech2->setEmail('tech2@example.com');
        $tech2->setRoles(['ROLE_TECHNICIEN']);
        $tech2->setPassword($this->passwordHasher->hashPassword($tech2, 'password123'));
        $tech2->setNom('Martin');
        $tech2->setPrenom('Sophie');
        $tech2->setDepartement('Support Technique');
        $tech2->setTelephone('01 23 45 67 91');
        $manager->persist($tech2);

        // Utilisateurs normaux
        $user1 = new User();
        $user1->setEmail('user1@example.com');
        $user1->setRoles(['ROLE_USER']);
        $user1->setPassword($this->passwordHasher->hashPassword($user1, 'password123'));
        $user1->setNom('Durand');
        $user1->setPrenom('Pierre');
        $user1->setDepartement('Comptabilité');
        $user1->setTelephone('01 23 45 67 92');
        $manager->persist($user1);

        $user2 = new User();
        $user2->setEmail('user2@example.com');
        $user2->setRoles(['ROLE_USER']);
        $user2->setPassword($this->passwordHasher->hashPassword($user2, 'password123'));
        $user2->setNom('Petit');
        $user2->setPrenom('Marie');
        $user2->setDepartement('Ressources Humaines');
        $user2->setTelephone('01 23 45 67 93');
        $manager->persist($user2);

        $user3 = new User();
        $user3->setEmail('user3@example.com');
        $user3->setRoles(['ROLE_USER']);
        $user3->setPassword($this->passwordHasher->hashPassword($user3, 'password123'));
        $user3->setNom('Bernard');
        $user3->setPrenom('Luc');
        $user3->setDepartement('Commercial');
        $user3->setTelephone('01 23 45 67 94');
        $manager->persist($user3);

        // Sauvegarder les utilisateurs avant de créer les tickets
        $manager->flush();

        // ========================================
        // Création des tickets de test
        // ========================================

        // Ticket 1 : Urgent, ouvert
        $ticket1 = new Ticket();
        $ticket1->setCreateur($user1);
        $ticket1->setTitre('Impossible de se connecter au réseau');
        $ticket1->setDescription(
            'Depuis ce matin, je ne peux plus me connecter au réseau de l\'entreprise. ' .
            'J\'ai essayé de redémarrer mon ordinateur mais le problème persiste. ' .
            'Message d\'erreur : "Impossible de trouver le domaine".'
        );
        $ticket1->setStatut('ouvert');
        $ticket1->setPriorite('urgente');
        $ticket1->setCategorie('reseau');
        $manager->persist($ticket1);

        // Ticket 2 : Normal, ouvert
        $ticket2 = new Ticket();
        $ticket2->setCreateur($user2);
        $ticket2->setTitre('Imprimante en panne');
        $ticket2->setDescription(
            'L\'imprimante du 2ème étage ne fonctionne plus. ' .
            'Elle affiche un message d\'erreur "Bourrage papier" mais il n\'y a pas de papier coincé.'
        );
        $ticket2->setStatut('ouvert');
        $ticket2->setPriorite('normale');
        $ticket2->setCategorie('materiel');
        $ticket2->setDateCreation((new \DateTime())->modify('-2 hours'));
        $manager->persist($ticket2);

        // Ticket 3 : Haute priorité, en cours
        $ticket3 = new Ticket();
        $ticket3->setCreateur($user3);
        $ticket3->setTechnicien($tech1);
        $ticket3->setTitre('Problème avec Excel');
        $ticket3->setDescription(
            'Excel plante à chaque fois que j\'essaie d\'ouvrir un fichier .xlsx. ' .
            'Les fichiers .csv s\'ouvrent normalement. ' .
            'J\'ai essayé de réparer Office mais sans succès.'
        );
        $ticket3->setStatut('en_cours');
        $ticket3->setPriorite('haute');
        $ticket3->setCategorie('logiciel');
        $ticket3->setDateCreation((new \DateTime())->modify('-1 day'));
        $manager->persist($ticket3);

        // Ticket 4 : Normal, en cours
        $ticket4 = new Ticket();
        $ticket4->setCreateur($user1);
        $ticket4->setTechnicien($tech2);
        $ticket4->setTitre('Mot de passe oublié');
        $ticket4->setDescription(
            'J\'ai oublié mon mot de passe pour accéder à l\'application de gestion de temps. ' .
            'Pouvez-vous le réinitialiser ?'
        );
        $ticket4->setStatut('en_cours');
        $ticket4->setPriorite('normale');
        $ticket4->setCategorie('compte');
        $ticket4->setDateCreation((new \DateTime())->modify('-3 hours'));
        $manager->persist($ticket4);

        // Ticket 5 : Urgent, résolu
        $ticket5 = new Ticket();
        $ticket5->setCreateur($user2);
        $ticket5->setTechnicien($tech1);
        $ticket5->setTitre('Écran bleu au démarrage');
        $ticket5->setDescription(
            'Mon ordinateur affiche un écran bleu au démarrage avec le message "CRITICAL_PROCESS_DIED". ' .
            'Je ne peux plus accéder à mes fichiers.'
        );
        $ticket5->setStatut('resolu');
        $ticket5->setPriorite('urgente');
        $ticket5->setCategorie('materiel');
        $ticket5->setDateCreation((new \DateTime())->modify('-3 days'));
        $ticket5->setDateResolution((new \DateTime())->modify('-1 day'));
        $manager->persist($ticket5);

        // Ticket 6 : Haute priorité, résolu
        $ticket6 = new Ticket();
        $ticket6->setCreateur($user3);
        $ticket6->setTechnicien($tech2);
        $ticket6->setTitre('Problème d\'accès à la boîte mail');
        $ticket6->setDescription(
            'Je ne reçois plus d\'emails depuis hier. ' .
            'J\'ai vérifié mes filtres anti-spam mais rien.'
        );
        $ticket6->setStatut('resolu');
        $ticket6->setPriorite('haute');
        $ticket6->setCategorie('logiciel');
        $ticket6->setDateCreation((new \DateTime())->modify('-2 days'));
        $ticket6->setDateResolution((new \DateTime())->modify('-6 hours'));
        $manager->persist($ticket6);

        // Ticket 7 : Basse priorité, fermé
        $ticket7 = new Ticket();
        $ticket7->setCreateur($user1);
        $ticket7->setTechnicien($tech1);
        $ticket7->setTitre('Installation de logiciel');
        $ticket7->setDescription(
            'J\'ai besoin d\'installer Adobe Photoshop sur mon poste de travail pour mes missions graphiques.'
        );
        $ticket7->setStatut('ferme');
        $ticket7->setPriorite('basse');
        $ticket7->setCategorie('logiciel');
        $ticket7->setDateCreation((new \DateTime())->modify('-7 days'));
        $ticket7->setDateResolution((new \DateTime())->modify('-5 days'));
        $manager->persist($ticket7);

        // Ticket 8 : Normal, ouvert (non assigné)
        $ticket8 = new Ticket();
        $ticket8->setCreateur($user2);
        $ticket8->setTitre('Clavier qui ne fonctionne plus');
        $ticket8->setDescription(
            'Certaines touches de mon clavier ne répondent plus. ' .
            'Les touches E, R et T sont complètement mortes.'
        );
        $ticket8->setStatut('ouvert');
        $ticket8->setPriorite('normale');
        $ticket8->setCategorie('materiel');
        $ticket8->setDateCreation((new \DateTime())->modify('-4 hours'));
        $manager->persist($ticket8);

        // Ticket 9 : Haute priorité, ouvert (VPN)
        $ticket9 = new Ticket();
        $ticket9->setCreateur($user3);
        $ticket9->setTitre('VPN ne fonctionne pas en télétravail');
        $ticket9->setDescription(
            'Impossible de me connecter au VPN de l\'entreprise depuis mon domicile. ' .
            'J\'obtiens une erreur "Échec de l\'authentification" alors que mes identifiants sont corrects.'
        );
        $ticket9->setStatut('ouvert');
        $ticket9->setPriorite('haute');
        $ticket9->setCategorie('reseau');
        $ticket9->setDateCreation((new \DateTime())->modify('-1 hour'));
        $manager->persist($ticket9);

        // Ticket 10 : Normal, en cours
        $ticket10 = new Ticket();
        $ticket10->setCreateur($user1);
        $ticket10->setTechnicien($tech2);
        $ticket10->setTitre('Lenteur du système');
        $ticket10->setDescription(
            'Mon ordinateur est extrêmement lent depuis quelques jours. ' .
            'Le démarrage prend plus de 10 minutes et les applications mettent du temps à s\'ouvrir.'
        );
        $ticket10->setStatut('en_cours');
        $ticket10->setPriorite('normale');
        $ticket10->setCategorie('materiel');
        $ticket10->setDateCreation((new \DateTime())->modify('-6 hours'));
        $manager->persist($ticket10);

        // Sauvegarder tous les tickets
        $manager->flush();

        // Afficher un message de confirmation
        echo "✅ Fixtures chargées avec succès !\n";
        echo "📊 Utilisateurs créés : 6 (1 admin, 2 techniciens, 3 utilisateurs)\n";
        echo "🎫 Tickets créés : 10\n";
        echo "🔑 Mot de passe pour tous les comptes : password123\n";
    }
}
