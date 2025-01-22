<?php

namespace App\Form;

use App\Entity\Unite;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UniteType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('code_unite',null,[
                'label_attr' => ['class' => 'hidden'],
            ])
            // ->add('nom_court')
            // ->add('nom_long')
            // ->add('departement')
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Unite::class,
        ]);
    }
}
