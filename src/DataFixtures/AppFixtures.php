<?php

namespace App\DataFixtures;

use App\Entity\Role;
use App\Entity\Action;
use App\Entity\Permission;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager)
    {

        // GESTION DES ROLES

        $roles = [
            'SOLC' => [
                'ordre' => 100,
                'nom' => 'SOLC',
                'libelle' => 'SOLC'
            ],
            'CSAG' => [
                'ordre' => 200,
                'nom' => 'CSAG',
                'libelle' => 'CSAG'
            ],
            'CDT' => [
                'ordre' => 300,
                'nom' => 'CDT',
                'libelle' => 'Commandement'
            ],
            'USR' => [
                'ordre' => 400,
                'nom' => 'USR',
                'libelle' => 'Utilisateur'
            ]
        ];

        foreach ($roles as $key => $role) {
            $entity = new Role();
            $entity->setNom($role['nom']);
            $entity->setLibelle($role['libelle']);
            $entity->setOrdre($role['ordre']);
            $manager->persist($entity);
            $manager->flush();

            $roles[$key]['role'] = $entity;
        }

        // GESTION DES ACTIONS
        $actions = [
            [
                'nom' => 'GERER_HORAIRES',
                'libelle' => 'Gérer les horaires de l\'atelier',
                'defaut' => ['CSAG']
            ],
            [
                'nom' => 'GERER_VL',
                'libelle' => 'Gérer le parc de véhicules',
                'defaut' => ['SOLC', 'CSAG']
            ],
            [
                'nom' => 'VALIDER_RESAS',
                'libelle' => 'Valider les réservations de véhicules',
                'defaut' => ['CSAG', 'CDT']
            ],
            [
                'nom' => 'RESERVER_VL',
                'libelle' => 'Réserver un véhicule',
                'defaut' => ['USR', 'CSAG', 'CDT', 'SOLC']
            ]
        ];

        foreach ($actions as $action) {
            $entity = new Action();
            $entity->setNom($action['nom']);
            $entity->setLibelle($action['libelle']);
            $manager->persist($entity);
            $manager->flush();

            foreach ($action['defaut'] as $def) {
                $perm = new Permission();
                $perm->setRole($roles[$def]['role']);
                $perm->setAction($entity);
                $manager->persist($perm);
                $manager->flush();
            }
        }
    }
}
