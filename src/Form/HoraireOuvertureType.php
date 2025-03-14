<?php

namespace App\Form;

use App\Entity\Atelier;
use App\Entity\HoraireOuverture;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class HoraireOuvertureType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('jour', null, [
                'attr' => [
                    'readonly' => true,
                ]
            ])
            ->add('creneau', null, [
                'attr' => [
                    'readonly' => true,
                ]
            ])
            ->add('debut')
            ->add('fin')
            ->add('code_unite', EntityType::class, [
                'class' => Atelier::class,
                'choice_label' => 'code_unite',
                'attr' => [
                    'readonly' => true,
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => HoraireOuverture::class,
        ]);
    }
}
