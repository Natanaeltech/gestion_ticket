<?php

namespace App\Form;

use App\Entity\Ticket;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Formulaire de création et modification de ticket
 */
class TicketType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            // Champ titre du ticket
            ->add('titre', TextType::class, [
                'label' => 'Titre du ticket',
                'attr' => [
                    'placeholder' => 'Ex: Problème de connexion au réseau',
                    'class' => 'form-control'
                ],
                'help' => 'Résumez brièvement votre problème'
            ])
            
            // Description détaillée
            ->add('description', TextareaType::class, [
                'label' => 'Description détaillée',
                'attr' => [
                    'placeholder' => 'Décrivez votre problème en détail...',
                    'class' => 'form-control',
                    'rows' => 6
                ],
                'help' => 'Plus vous donnez de détails, plus vite nous pourrons vous aider'
            ])
            
            // Catégorie du problème
            ->add('categorie', ChoiceType::class, [
                'label' => 'Catégorie',
                'choices' => [
                    'Matériel (ordinateur, imprimante, etc.)' => 'materiel',
                    'Logiciel (applications, système)' => 'logiciel',
                    'Réseau et connexion' => 'reseau',
                    'Compte utilisateur et accès' => 'compte',
                    'Autre' => 'autre',
                ],
                'attr' => [
                    'class' => 'form-select'
                ],
                'placeholder' => 'Sélectionnez une catégorie'
            ])
            
            // Priorité du ticket
            ->add('priorite', ChoiceType::class, [
                'label' => 'Priorité',
                'choices' => [
                    'Basse - Peut attendre' => 'basse',
                    'Normale - Traitement standard' => 'normale',
                    'Haute - Nécessite attention rapide' => 'haute',
                    'Urgente - Bloquant pour le travail' => 'urgente',
                ],
                'attr' => [
                    'class' => 'form-select'
                ],
                'data' => 'normale', // Priorité par défaut
                'help' => 'Choisissez "Urgente" uniquement si cela bloque complètement votre travail'
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Ticket::class,
        ]);
    }
}
