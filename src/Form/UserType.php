<?php

namespace App\Form;

use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserType extends AbstractType
{
    private $userRepository;

    public function __construct(UserRepository $userRepository){
        $this->userRepository = $userRepository;

    }
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            // ->add('nigend', EntityType::class, [
            //     'class' => User::class,
            //     'choice_label' => function (?User $user) {
            //         return $user ? $user->getNigend() . ' (Profil: ' . $user->getProfil() . ')' : '';
            //     },
            //     'choice_value' => function (?User $user) {
            //         return $user ? $user->getNigend() : '';
            //     }
            // ])
             ->add('nigend')
            // ->add('unite')
            // ->add('profil')
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
