<?php

namespace App\DataFixtures;

use App\Entity\Role;
use App\Entity\Action;
use App\Entity\GenreVehicule;
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
                'template' => null, //'horaires_atelier',
                'defaut' => ['CSAG']
            ],
            [
                'nom' => 'GERER_VL',
                'libelle' => 'Gérer le parc de véhicules',
                'template' => 'compte/gestion_parc.html.twig',
                'defaut' => ['SOLC', 'CSAG']
            ],
            [
                'nom' => 'VALIDER_RESAS',
                'libelle' => 'Valider les réservations de véhicules',
                'template' => null, //'validation_reservation',
                'defaut' => ['CSAG', 'CDT']
            ],
            [
                'nom' => 'RESERVER_VL',
                'libelle' => 'Réserver un véhicule',
                'template' => null, //'reservation_vehicule',
                'defaut' => ['USR', 'CSAG', 'CDT', 'SOLC']
            ]
        ];

        // GESTION DES VÉHICULES

        $genres = [
            ['CTTE', 'Camionnettes (jusqu’è 3.500 kg, autre que tracteur routier)', 100],
            ['CAM',    'Camions (plus de 3.500 kg, autre que tracteur routier et camionnette)', 100],
            ['CL',    'Cyclomoteurs à deux roues ou cyclo-moteurs non carrossés à 3 roues', 100],
            ['CYCL',    'Cyclomoteurs à trois roues', 100],
            ['MAGA',    'Machines agricoles automotrices', 100],
            ['MTL',    'Motocyclettes légères', 100],
            ['MIAR',    'Machines et instruments remorqués', 100],
            ['MTT1',    'Motocyclettes autres que motocyclettes légères, dont la puissance maximale nette CE <= 25 kW', 100],
            ['MTT2',    'Autres motocyclettes', 100],
            ['QM', 'Quadricycles à moteur', 100],
            ['REA',    'Remorques agricoles', 100],
            ['RETC',    'Remorques pour transports combinés', 100],
            ['REM',    'Remorques routières', 100],
            ['RESP',    'Remorques spécialisées', 100],
            ['SREA',    'Semi-remorques agricoles', 100],
            ['SRAT',    'Semi-remorques avant-train', 100],
            ['SRTC',    'Semi-remorques pour transports combinés', 100],
            ['SREM',    'Semi-remorques routières', 100],
            ['SRSP',    'Semi-remorques spécialisées', 100],
            ['TRA',    'Tracteurs agricoles', 100],
            ['TRR', 'Tracteurs routiers', 100],
            ['TCP',    'Transports en commun de personnes', 100],
            ['TM',    'Tricycles à moteur', 100],
            ['VASP',    'Véhicules automoteur spécialisés', 100],
            ['VP',    'Voitures particulières', 0],
            ['VTSU',    'Véhicules très spécialisés à usage divers',100]
        ];

        foreach ($genres as $genre) {
            [$code, $libelle, $ordre] = $genre;
            $entity = new GenreVehicule();
            $entity->setCode($code);
            $entity->setLibelle($libelle);
            $entity->setOrdre($ordre);
            $manager->persist($entity);
            $manager->flush();
        }
    }
}
