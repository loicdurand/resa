<?php

namespace App\Form;

use App\Form\UniteType;
use App\Entity\CarburantVehicule;
use App\Entity\CategorieVehicule;
use App\Entity\GenreVehicule;
use App\Entity\TransmissionVehicule;
use App\Entity\Restriction;
use App\Entity\Vehicule;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

// use Symfony\Component\Validator\Constraints\File;

class VehiculeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {

        $builder
            ->add('immatriculation', null, [
                'row_attr' => ['class' => 'fr-col-12 fr-col-sm-12'],
                'attr' => ['class' => 'fr-input'],
                'help_attr' => ['content' => 'Obligatoire (9 caractères maximum).']
            ])
            ->add('marque', null, [
                'row_attr' => ['class' => 'fr-col-12 fr-col-sm-12'],
                'help_attr' => ['content' => 'Obligatoire (255 caractères maximum).']
            ])
            ->add('modele', null, [
                'row_attr' => ['class' => 'fr-col-12 fr-col-sm-12'],
                'help_attr' => ['content' => 'Obligatoire (255 caractères maximum).']
            ])
            ->add('motorisation', null, [
                'row_attr' => ['class' => 'fr-col-6 fr-col-sm-6'],
                'help_attr' => ['content' => 'Facultatif (25 caractères maximum).'],
                'attr' => [
                    'placeholder' => '200CDI, 30D, 1.5DCi 75, etc...'
                ]
            ])
            ->add('finition', null, [
                'row_attr' => ['class' => 'fr-col-6 fr-col-sm-6'],
                'help_attr' => ['content' => 'Faculatif (25 caractères maximum).'],
                'attr' => [
                    'placeholder' => 'Privilège, Elegance, etc...'
                ],
            ])
            ->add('controle_technique', null, [
                'row_attr' => ['class' => 'fr-col-6 fr-col-sm-6'],
                'widget' => 'single_text',
                'help_attr' => ['content' => 'Facultatif.'],
            ])
            ->add('NbPlaces', null, [
                'row_attr' => ['class' => 'fr-col-6 fr-col-sm-6'],
                'help_attr' => ['content' => 'Faculatif.'],
                'label' =>  'Nombre de places',
            ])
            ->add('serigraphie', null, [
                'label' => 'Véhicule sérigraphié',
            ])

            ->add('genre', EntityType::class, [
                'class' => GenreVehicule::class,
                'choice_label' => 'code',
                'row_attr' => ['class' => 'fr-select-group fr-col-6 fr-col-sm-6'],
                'label_attr' => ['class' => 'fr-label'],
                'help_attr' => ['content' => 'Faculatif.'],
            ])
            ->add('categorie', EntityType::class, [
                'class' => CategorieVehicule::class,
                'choice_label' => 'libelle',
                'row_attr' => ['class' => 'fr-select-group fr-col-6 fr-col-sm-6'],
                'label_attr' => ['class' => 'fr-label'],
                'help_attr' => ['content' => 'Faculatif.'],
            ])
            ->add('transmission', EntityType::class, [
                'class' => TransmissionVehicule::class,
                'choice_label' => 'libelle',
                'row_attr' => ['class' => 'fr-select-group fr-col-6 fr-col-sm-6'],
                'label_attr' => ['class' => 'fr-label'],
                'help_attr' => ['content' => 'Faculatif.'],
            ])
            ->add('carburant', EntityType::class, [
                'class' => CarburantVehicule::class,
                'choice_label' => 'libelle',
                'row_attr' => ['class' => 'fr-select-group fr-col-6 fr-col-sm-6'],
                'label_attr' => ['class' => 'fr-label'],
                'help_attr' => ['content' => 'Faculatif.']
            ])
            ->add('observation', TextareaType::class, [
                'required' => false,
                'row_attr' => ['class' => 'fr-col-12 fr-col-sm-12'],
                'help_attr' => ['content' => 'Faculatif (1024 caractères maximum).'],
                'attr' => [
                    'placeholder' => 'Information utile à donner aux utilisateurs, etc...',
                    'class' => 'fr-input'
                ],
            ])
            ->add('departement', NumberType::class, [
                'row_attr' => ['class' => 'fr-col-6 fr-col-sm-6'],
                'help_attr' => ['content' => 'Obligatoire (3 chiffres maximum).'],
                'attr' => [
                    'placeholder' => '971, 972, 973, etc...'
                ]
            ])
            ->add('unite', UniteType::class, [
                'row_attr' => ['class' => 'fr-col-6 fr-col-sm-6'],
                'help_attr' => ['content' => 'Code unité uniquement. Obligatoire (8 chiffres maximum).'],
                'attr' => [
                    'list' => 'unites-list',
                    'placeholder' => '123456, 56751, 0056751, etc...'
                ]
            ])
            ->add('restriction', EntityType::class, [
                'class' => Restriction::class,
                'choice_label' => 'libelle',
                'row_attr' => ['class' => 'fr-select-group fr-col-12 fr-col-sm-12'],
                'label_attr' => ['class' => 'fr-label'],
                'help_attr' => ['content' => 'Facultatif'],
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
