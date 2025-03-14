<?php

namespace App\DataFixtures;

use App\Entity\CategorieVehicule;
use App\Entity\Reservation;
use App\Entity\Role;
use App\Entity\Action;
use App\Entity\GenreVehicule;
use App\Entity\Permission;
use App\Entity\CarburantVehicule;
use App\Entity\TransmissionVehicule;
use App\Entity\Atelier;
use App\Entity\Unite;
use App\Entity\HoraireOuverture;
use App\Entity\StatutReservation;
use App\Entity\User;
use App\Entity\Vehicule;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void    
    {

        // GESTION DES ROLES
        $users = [
            ['249205', '86977', 'SOLC'],
            ['170044', '56751', 'CSAG'],
            ['167194', '6768', 'VDT']
        ];

        foreach ($users as [$nigend, $code_unite, $profil]) {
            $user = new User();
            $user->setNigend($nigend);
            $user->setUnite($code_unite);
            $user->setProfil($profil);
            $user->setDepartement(971);
            $manager->persist($user);
            $manager->flush();
        }

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
            'VDT' => [
                'ordre' => 300,
                'nom' => 'VDT',
                'libelle' => 'Validateur'
            ],
            'CDT' => [
                'ordre' => 400,
                'nom' => 'CDT',
                'libelle' => 'Commandement'
            ],
            'USR' => [
                'ordre' => 500,
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
                'template' => 'compte/gestion_horaires.html.twig', //'horaires_atelier',
                'defaut' => ['CSAG']
            ],
            [
                'nom' => 'GERER_VL',
                'libelle' => 'Gérer le parc de véhicules',
                'template' => 'compte/gestion_parc.html.twig',
                'defaut' => ['SOLC', 'CSAG']
            ],
            [
                'nom' => 'AFFICHER_TDB',
                'libelle' => 'Afficher le tableau de bord',
                'template' => 'compte/tdb.html.twig',
                'defaut' => ['SOLC', 'CSAG']
            ],
            [
                'nom' => 'VALIDER_RESAS',
                'libelle' => 'Valider les réservations de véhicules',
                'template' => 'compte/valider_resas.html.twig', //'validation_reservation',
                'defaut' => ['SOLC', 'VDT']
            ],
            [
                'nom' => 'RESERVER_VL',
                'libelle' => 'Réserver un véhicule',
                'template' => 'compte/reserver_vl.html.twig',
                'defaut' => ['USR', 'CSAG', 'VDT', 'CDT', 'SOLC']
            ]
        ];

        foreach ($actions as $action) {
            $entity = new Action();
            $entity->setNom($action['nom']);
            $entity->setLibelle($action['libelle']);
            $entity->setTemplate($action['template']);
            $manager->persist($entity);
            $manager->flush();

            foreach ($action['defaut'] as $def) {
                $perm = new Permission();
                $perm->setAction($entity);
                $perm->setRole($roles[$def]['role']);
                $manager->persist($perm);
                $manager->flush();
            }
        }

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
            ['VTSU',    'Véhicules très spécialisés à usage divers', 100]
        ];

        $genrs = [];

        foreach ($genres as $genre) {
            [$code, $libelle, $ordre] = $genre;
            $entity = new GenreVehicule();
            $entity->setCode($code);
            $entity->setLibelle($libelle);
            $entity->setOrdre($ordre);
            $manager->persist($entity);
            $manager->flush();
            if ($code === 'CTTE' || $code === 'VP') {
                $genrs[] = $entity;
            }
        }

        $categories = [
            // 'Citadine' => 'sedan.png',
            'Berline' => 'sedan.png',
            '4x4' => 'suv.png',
            'Utilitaire' => 'utilitaire.png',
            'Minibus' => 'mpv.png'
        ];

        $cats = [];

        foreach ($categories as $categorie => $illustration) {
            $entity = new CategorieVehicule();
            $entity->setLibelle($categorie);
            $entity->setIllustration($illustration);
            $manager->persist($entity);
            $manager->flush();
            $cats[] = $entity;
        }

        $carburants = [
            [
                'code' => 'SP',
                'libelle' => 'Essence'
            ],
            [
                'code' => 'GO',
                'libelle' => 'Diesel'
            ],
            [
                'code' => 'DIV',
                'libelle' => 'Autre (GPL, éthanol, GNV, etc...)'
            ],
            [
                'code' => 'ELEC',
                'libelle' => 'Électrique'
            ]
        ];

        $carbs = [];

        foreach ($carburants as $carburant) {
            $entity = new CarburantVehicule();
            $entity->setCode($carburant['code']);
            $entity->setLibelle($carburant['libelle']);
            $manager->persist($entity);
            $manager->flush();
            $carbs[] = $entity;
        }

        $transmissions = [
            [
                'code' => 'BVA',
                'libelle' => 'Boite de vitesses automatique'
            ],
            [
                'code' => 'BVM',
                'libelle' => 'Boite de vitesses manuelle'
            ]
        ];

        $transms = [];

        foreach ($transmissions as $transmission) {
            $entity = new TransmissionVehicule();
            $entity->setCode($transmission['code']);
            $entity->setLibelle($transmission['libelle']);
            $manager->persist($entity);
            $manager->flush();
            $transms[] = $entity;
        }

        $atelier = new Atelier();
        $atelier->setCodeUnite('00056751');
        $atelier->setDepartement(971);
        $atelier->setNomCourt('CSAG 971');
        $atelier->setNomLong('Centre de Soutien Automobile de la Gendarmerie de Baie-Mahault');
        $manager->persist($atelier);
        $manager->flush();

        $jours_ouvrables = ['LU', 'MA', 'ME', 'JE', 'VE'];
        foreach ($jours_ouvrables as $jour) {
            for ($i = 0; $i <= 1; $i++) {
                $horaire = new HoraireOuverture();
                $horaire->setCodeUnite($atelier);
                $horaire->setJour($jour);
                if ($i === 0) {
                    $horaire->setCreneau('AM');
                    $horaire->setDebut('08:00');
                    $horaire->setFin('12:00');
                } else {
                    $horaire->setCreneau('PM');
                    $horaire->setDebut('14:00');
                    $horaire->setFin('17:00');
                }
                $manager->persist($horaire);
                $manager->flush();
            }
        };

        // TYPES DE RÉSERVATIONS

        $types_resas = [
            ['En attente', 'En attente de validation hiérachique'],
            ['Confirmée', 'Réservation validée par la hiérarchie'],
            ['En cours', 'La réservation a débuté'],
            ['Terminée', 'Réservation terminée'],
            ['Annulée', 'Réservation annulée par la hiérachie']
        ];

        foreach ($types_resas as $index => [$code, $libelle]) {
            $entity = new StatutReservation();
            $entity->setCode($code);
            $entity->setLibelle($libelle);
            $manager->persist($entity);
            $manager->flush();
            if ($index == 0) {
                $resa_en_attente = $entity;
            }
        };

        $vls = [
            [$genrs[1], $cats[3], $carbs[1], $transms[1], 'RENAULT', 'Master', '1.5 DCi', null, '2025-02-11', 7, 'GS-517-PF', 0],
            [$genrs[1], $cats[3], $carbs[1], $transms[1], 'RENAULT', 'Master', '1.5 DCi', null, '2025-02-11', 3, 'GY-057-GF', 0],
            [$genrs[1], $cats[1], $carbs[1], $transms[0], 'BMW', 'X4', '30D', null, '2025-07-12', 4, 'GL-146-SZ', 0],
            [$genrs[1], $cats[1], $carbs[1], $transms[0], 'HYUNDAI', 'Sante Fe', null, null, '2025-07-12', 7, 'WW-682-LV', 0],
            [$genrs[1], $cats[0], $carbs[1], $transms[0], 'CITROËN', 'Berlingo', null, null, '2025-03-11', 5, 'CD-943-AR', 0],
            [$genrs[1], $cats[0], $carbs[0], $transms[1], 'PEUGEOT', 'Partner', null, null, '2024-12-11', 2, 'AD-089-FR', 1],
            [$genrs[0], $cats[2], $carbs[1], $transms[1], 'BMW', 'X1', null, null, '2025-03-06', 5, 'EA-125-NP', 0]
        ];

        $from = new \DateTime('now');
        $from->modify('+ 1 days');
        $tmp = new \DateTime('now');
        $max = new \DateTime($tmp->format('Y-m-d') . ' 23:59:59');
        $max->modify('+4 months');
        $to = $max;

        $atelier = new Unite();
        $atelier->setCodeUnite('00056751');
        $atelier->setDepartement(971);
        $atelier->setNomCourt('CSAG 971');
        $atelier->setNomLong('Centre de Soutien Automobile de la Gendarmerie de Baie-Mahault');
        $manager->persist($atelier);
        $manager->flush();

        foreach ($vls as $vl) {

            [$gre, $cat, $carb, $tr, $marque, $modele, $mot, $finit, $ct, $pl, $immat, $serig] = $vl;
            $VL = new Vehicule();
            $VL->setGenre($gre);
            $VL->setCategorie($cat);
            $VL->setCarburant($carb);
            $VL->setTransmission($tr);
            $VL->setMarque($marque);
            $VL->setModele($modele);
            $VL->setMotorisation($mot);
            $VL->setFinition($finit);
            $VL->setControleTechnique(\DateTime::createFromFormat('Y-m-d H:i:s', $ct . ' 23:59:59'));
            $VL->setNbPlaces($pl);
            $VL->setImmatriculation($immat);
            $VL->setSerigraphie($serig);
            $VL->setDepartement(971);
            $VL->setUnite($atelier);
            $manager->persist($VL);
            $manager->flush();
        }
    }
}
