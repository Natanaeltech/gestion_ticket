<?php

namespace App\Controller;

use App\Entity\Ticket;
use App\Entity\User;
use App\Form\TicketType;
use App\Repository\TicketRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

/**
 * Contrôleur gérant toutes les opérations liées aux tickets
 */
#[Route('/ticket')]
class TicketController extends AbstractController
{
    /**
     * Affiche la liste de tous les tickets
     * Les utilisateurs normaux voient uniquement leurs tickets
     * Les techniciens et admins voient tous les tickets
     */
    #[Route('/', name: 'app_ticket_index', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    public function index(TicketRepository $ticketRepository): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        
        // Si l'utilisateur est technicien ou admin, afficher tous les tickets
        if ($this->isGranted('ROLE_TECHNICIEN') || $this->isGranted('ROLE_ADMIN')) {
            $tickets = $ticketRepository->findAll();
        } else {
            // Sinon, afficher uniquement les tickets de l'utilisateur
            $tickets = $ticketRepository->findBy(['createur' => $user]);
        }

        return $this->render('ticket/index.html.twig', [
            'tickets' => $tickets,
        ]);
    }

    /**
     * Affiche le formulaire de création d'un nouveau ticket
     * Traite la soumission du formulaire
     */
    #[Route('/nouveau', name: 'app_ticket_new', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_USER')]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $ticket = new Ticket();
        
        // Associer le ticket à l'utilisateur connecté
        /** @var User $user */
        $user = $this->getUser();
        $ticket->setCreateur($user);

        // Créer et traiter le formulaire
        $form = $this->createForm(TicketType::class, $ticket);
        $form->handleRequest($request);

        // Si le formulaire est soumis et valide
        if ($form->isSubmitted() && $form->isValid()) {
            // Persister le ticket en base de données
            $entityManager->persist($ticket);
            $entityManager->flush();

            // Ajouter un message de succès
            $this->addFlash('success', 'Votre ticket a été créé avec succès !');

            // Rediriger vers la liste des tickets
            return $this->redirectToRoute('app_ticket_index');
        }

        return $this->render('ticket/new.html.twig', [
            'ticket' => $ticket,
            'form' => $form,
        ]);
    }

    /**
     * Affiche les détails d'un ticket spécifique
     */
    #[Route('/{id}', name: 'app_ticket_show', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    public function show(Ticket $ticket): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        
        // Vérifier que l'utilisateur a le droit de voir ce ticket
        // (soit c'est son ticket, soit c'est un technicien/admin)
        if ($ticket->getCreateur() !== $user && 
            !$this->isGranted('ROLE_TECHNICIEN') && 
            !$this->isGranted('ROLE_ADMIN')) {
            throw $this->createAccessDeniedException('Vous n\'avez pas accès à ce ticket.');
        }

        return $this->render('ticket/show.html.twig', [
            'ticket' => $ticket,
        ]);
    }

    /**
     * Affiche le formulaire d'édition d'un ticket
     * Traite la modification du ticket
     */
    #[Route('/{id}/modifier', name: 'app_ticket_edit', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_USER')]
    public function edit(Request $request, Ticket $ticket, EntityManagerInterface $entityManager): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        
        // Seul le créateur ou un technicien/admin peut modifier
        if ($ticket->getCreateur() !== $user && 
            !$this->isGranted('ROLE_TECHNICIEN') && 
            !$this->isGranted('ROLE_ADMIN')) {
            throw $this->createAccessDeniedException('Vous ne pouvez pas modifier ce ticket.');
        }

        // Les utilisateurs normaux ne peuvent modifier que les tickets ouverts
        if (!$this->isGranted('ROLE_TECHNICIEN') && 
            !$this->isGranted('ROLE_ADMIN') && 
            $ticket->getStatut() !== 'ouvert') {
            $this->addFlash('error', 'Vous ne pouvez pas modifier un ticket qui n\'est plus ouvert.');
            return $this->redirectToRoute('app_ticket_show', ['id' => $ticket->getId()]);
        }

        $form = $this->createForm(TicketType::class, $ticket);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Mettre à jour la date de modification
            $ticket->setDateMiseAJour(new \DateTime());
            
            $entityManager->flush();

            $this->addFlash('success', 'Le ticket a été modifié avec succès !');

            return $this->redirectToRoute('app_ticket_show', ['id' => $ticket->getId()]);
        }

        return $this->render('ticket/edit.html.twig', [
            'ticket' => $ticket,
            'form' => $form,
        ]);
    }

    /**
     * Supprime un ticket
     * Réservé aux administrateurs
     */
    #[Route('/{id}', name: 'app_ticket_delete', methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function delete(Request $request, Ticket $ticket, EntityManagerInterface $entityManager): Response
    {
        // Vérification du token CSRF pour la sécurité
        if ($this->isCsrfTokenValid('delete'.$ticket->getId(), $request->request->get('_token'))) {
            $entityManager->remove($ticket);
            $entityManager->flush();

            $this->addFlash('success', 'Le ticket a été supprimé avec succès.');
        }

        return $this->redirectToRoute('app_ticket_index');
    }

    /**
     * Permet à un technicien de s'assigner un ticket
     */
    #[Route('/{id}/assigner', name: 'app_ticket_assign', methods: ['POST'])]
    #[IsGranted('ROLE_TECHNICIEN')]
    public function assign(Request $request, Ticket $ticket, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('assign'.$ticket->getId(), $request->request->get('_token'))) {
            /** @var User $user */
            $user = $this->getUser();
            
            $ticket->setTechnicien($user);
            $ticket->setStatut('en_cours');
            $ticket->setDateMiseAJour(new \DateTime());
            
            $entityManager->flush();

            $this->addFlash('success', 'Le ticket vous a été assigné avec succès.');
        }

        return $this->redirectToRoute('app_ticket_show', ['id' => $ticket->getId()]);
    }

    /**
     * Permet à un technicien de changer le statut d'un ticket
     */
    #[Route('/{id}/statut/{nouveauStatut}', name: 'app_ticket_change_status', methods: ['POST'])]
    #[IsGranted('ROLE_TECHNICIEN')]
    public function changeStatus(
        Request $request, 
        Ticket $ticket, 
        string $nouveauStatut,
        EntityManagerInterface $entityManager
    ): Response {
        // Vérifier que le nouveau statut est valide
        $statutsValides = ['ouvert', 'en_cours', 'resolu', 'ferme'];
        
        if (!in_array($nouveauStatut, $statutsValides)) {
            $this->addFlash('error', 'Statut invalide.');
            return $this->redirectToRoute('app_ticket_show', ['id' => $ticket->getId()]);
        }

        if ($this->isCsrfTokenValid('status'.$ticket->getId(), $request->request->get('_token'))) {
            $ticket->setStatut($nouveauStatut);
            $entityManager->flush();

            $this->addFlash('success', 'Le statut du ticket a été mis à jour.');
        }

        return $this->redirectToRoute('app_ticket_show', ['id' => $ticket->getId()]);
    }

    /**
     * Affiche le tableau de bord des tickets (statistiques)
     * Réservé aux techniciens et administrateurs
     */
    #[Route('/dashboard/stats', name: 'app_ticket_dashboard', methods: ['GET'])]
    #[IsGranted('ROLE_TECHNICIEN')]
    public function dashboard(TicketRepository $ticketRepository): Response
    {
        // Récupérer les statistiques
        $totalTickets = $ticketRepository->count([]);
        $ticketsOuverts = $ticketRepository->count(['statut' => 'ouvert']);
        $ticketsEnCours = $ticketRepository->count(['statut' => 'en_cours']);
        $ticketsResolus = $ticketRepository->count(['statut' => 'resolu']);
        $ticketsFermes = $ticketRepository->count(['statut' => 'ferme']);
        
        // Statistiques par priorité
        $prioriteUrgente = $ticketRepository->count(['priorite' => 'urgente']);
        $prioriteHaute = $ticketRepository->count(['priorite' => 'haute']);

        return $this->render('ticket/dashboard.html.twig', [
            'totalTickets' => $totalTickets,
            'ticketsOuverts' => $ticketsOuverts,
            'ticketsEnCours' => $ticketsEnCours,
            'ticketsResolus' => $ticketsResolus,
            'ticketsFermes' => $ticketsFermes,
            'prioriteUrgente' => $prioriteUrgente,
            'prioriteHaute' => $prioriteHaute,
        ]);
    }
}
