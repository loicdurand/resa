<?php

namespace App\Form;

use App\Entity\Reservation;
use App\Entity\StatutReservation;
use App\Entity\Vehicule;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ReservationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('date_debut', null, [
                'widget' => 'single_text',
            ])
            ->add('heure_debut')
            ->add('date_fin', null, [
                'widget' => 'single_text',
            ])
            ->add('heure_fin')
            ->add('user')
            ->add('vehicule', EntityType::class, [
                'class' => Vehicule::class,
                'choice_label' => 'id',
            ])
            ->add('statut', EntityType::class, [
                'class' => StatutReservation::class,
                'choice_label' => 'id',
            ])
            ->add('observation', TextareaType::class, [
                'required' => false,
                'row_attr' => ['class' => 'fr-col-12 fr-col-sm-12'],
                'help_attr' => ['content' => 'Faculatif (255 caractères maximum).'],
                'attr' => [
                    'placeholder' => "Observations (aide votre valideur à apprécier l'opportunité de la demande).",
                    'class' => 'fr-input'
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Reservation::class,
        ]);
    }
}
