<?php

namespace App\DataFixtures;

use App\Entity\Role;
use App\Entity\Action;
use App\Entity\Permission;

use App\Entity\Type;

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
            ['CTTE', 'Camionnettes (jusqu’è 3.500 kg, autre que tracteur routier)'],
            ['CAM',    'Camions (plus de 3.500 kg, autre que tracteur routier et camionnette)'],
            ['CL',    'Cyclomoteurs à deux roues ou cyclo-moteurs non carrossés à 3 roues'],
            ['CYCL',    'Cyclomoteurs à trois roues'],
            ['MAGA',    'Machines agricoles automotrices'],
            ['MTL',    'Motocyclettes légères'],
            ['MIAR',    'Machines et instruments remorqués'],
            ['MTT1',    'Motocyclettes autres que motocyclettes légères, dont la puissance maximale nette CE <= 25 kW'],
            ['MTT2',    'Autres motocyclettes'],
            ['QM', 'Quadricycles à moteur'],
            ['REA',    'Remorques agricoles'],
            ['RETC',    'Remorques pour transports combinés'],
            ['REM',    'Remorques routières'],
            ['RESP',    'Remorques spécialisées'],
            ['SREA',    'Semi-remorques agricoles'],
            ['SRAT',    'Semi-remorques avant-train'],
            ['SRTC',    'Semi-remorques pour transports combinés'],
            ['SREM',    'Semi-remorques routières'],
            ['SRSP',    'Semi-remorques spécialisées'],
            ['TRA',    'Tracteurs agricoles'],
            ['TRR', 'Tracteurs routiers'],
            ['TCP',    'Transports en commun de personnes'],
            ['TM',    'Tricycles à moteur'],
            ['VASP',    'Véhicules automoteur spécialisés'],
            ['VP',    'Voitures particulières'],
            ['VTSU',    'Véhicules très spécialisés à usage divers']
        ];

        foreach ($genres as $genre) {
            [$code, $libelle] = $genre;
            $entity = new Type();
            $entity->setCode($code);
            $entity->setLibelle($libelle);
            $manager->persist($entity);
            $manager->flush();
        }
    }
}
