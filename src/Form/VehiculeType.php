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
            ->add('marque', null, [
                'attr' => ['class' => 'fr-input'],
                'label_attr' => ['class' => 'fr-label']
            ])
            ->add('modele', null, [
                'attr' => ['class' => 'fr-input'],
                'label_attr' => ['class' => 'fr-label']
            ])
            ->add('motorisation', null, [
                'attr' => [
                    'class' => 'fr-input',
                    'placeholder' => '200CDI, 30D, 1.5DCi 75, etc...'
                ],
                'label_attr' => ['class' => 'fr-label']
            ])
            ->add('finition', null, [
                'attr' => [
                    'class' => 'fr-input',
                    'placeholder' => 'Privilège, Elegance, etc...'
                ],
                'label_attr' => ['class' => 'fr-label']
            ])
            ->add('controle_technique', null, [
                'widget' => 'single_text',
                'attr' => ['class' => 'fr-input'],
                'label_attr' => ['class' => 'fr-label']
            ])
            ->add('NbPlaces', null, [
                'attr' => ['class' => 'fr-input'],
                'label_attr' => ['class' => 'fr-label']
            ])
            ->add('immatriculation', null, [
                'attr' => ['class' => 'fr-input'],
                'label_attr' => ['class' => 'fr-label']
            ])
            ->add('serigraphie', null, [
                'label_attr' => ['class' => 'fr-label']
            ])
            ->add('genre', EntityType::class, [
                'class' => GenreVehicule::class,
                'choice_label' => 'code',
                'attr' => ['class' => 'fr-select'],
                'label_attr' => ['class' => 'fr-label']
            ])
            ->add('categorie', EntityType::class, [
                'class' => CategorieVehicule::class,
                'choice_label' => 'libelle',
                'attr' => ['class' => 'fr-select'],
                'label_attr' => ['class' => 'fr-label']
            ])
            ->add('carburant', EntityType::class, [
                'class' => CarburantVehicule::class,
                'choice_label' => 'libelle',
                'attr' => ['class' => 'fr-select'],
                'label_attr' => ['class' => 'fr-label']
            ])
            ->add('transmission', EntityType::class, [
                'class' => TransmissionVehicule::class,
                'choice_label' => 'libelle',
                'attr' => ['class' => 'fr-select'],
                'label_attr' => ['class' => 'fr-label']
            ])
            ->add('submit')
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Vehicule::class,
        ]);
    }
}
