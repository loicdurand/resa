<?php

namespace App\Form;

use App\Entity\CarburantVehicule;
use App\Entity\CategorieVehicule;
use App\Entity\GenreVehicule;
use App\Entity\TransmissionVehicule;
use App\Entity\Vehicule;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class VehiculeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('marque')
            ->add('modele')
            ->add('motorisation')
            ->add('finition')
            ->add('controle_technique', null, [
                'widget' => 'single_text',
            ])
            ->add('nb_places')
            ->add('immatriculation')
            ->add('serigraphie')
            ->add('genre', EntityType::class, [
                'class' => GenreVehicule::class,
                'choice_label' => 'id',
            ])
            ->add('categorie', EntityType::class, [
                'class' => CategorieVehicule::class,
                'choice_label' => 'id',
            ])
            ->add('carburant', EntityType::class, [
                'class' => CarburantVehicule::class,
                'choice_label' => 'id',
            ])
            ->add('transmission', EntityType::class, [
                'class' => TransmissionVehicule::class,
                'choice_label' => 'id',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Vehicule::class,
        ]);
    }
}
