<?php

namespace App\Controller;

use App\Entity\Token;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\String\Slugger\SluggerInterface;

use Doctrine\Persistence\ManagerRegistry;
use App\Entity\User;
use App\Entity\Atelier;
use App\Entity\Unite;
use App\Entity\Role;
use App\Entity\Action;
use App\Entity\CarburantVehicule;
use App\Entity\CategorieVehicule;
use App\Entity\GenreVehicule;
use App\Entity\Permission;
use App\Entity\TransmissionVehicule;
use App\Entity\Reservation;
use App\Entity\Vehicule;
use App\Entity\Photo;
use App\Form\PhotoType;
use App\Form\VehiculeType;
use App\Entity\HoraireOuverture;
use App\Entity\Restriction;
use App\Entity\FicheSuivi;
use App\Entity\TypeFicheSuivi;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

use App\Service\PhotoService;
use App\Service\LdapService;

class ParcController extends AbstractController
{
    private $app_const;
    private $requestStack, $session;
    public $params, $request;

    public function __construct(RequestStack $requestStack)
    {
        $this->request = Request::createFromGlobals();
        $this->requestStack = $requestStack;
        $this->session = $this->requestStack->getSession();

        $this->session = $this->requestStack->getSession();

        $this->params = [
            'nigend' => $this->session->get('HTTP_NIGEND'),
            'unite' => $this->session->get('HTTP_UNITE'),
            'profil' => $this->session->get('HTTP_PROFIL'),
            'departement' => $this->session->get('HTTP_DEPARTEMENT'),
        ];
    }


    #[Route('/parc', name: 'resa_parc')]
    public function afficher(ManagerRegistry $doctrine): Response
    {
        if (is_null($this->params['nigend']))
            return $this->redirectToRoute('resa_login');

        $this->setAppConst();

        $em = $doctrine->getManager();
        $departement = $this->params['departement'];
        $vehicules = $em
            ->getRepository(Vehicule::class)
            ->findBy(['departement' => $departement]);

        return $this->render('parc/afficher.html.twig', array_merge(
            $this->getAppConst(),
            $this->params,
            [
                'vehicules' => $vehicules,
            ]
        ));
    }

    #[Route('/parc/ajouter', name: 'resa_parc_ajouter')]
    public function ajouter(ManagerRegistry $doctrine): Response
    {
        if (is_null($this->params['nigend']))
            return $this->redirectToRoute('resa_login');

        $this->setAppConst();

        $em = $doctrine->getManager();

        $unites = $em
            ->getRepository(Unite::class)
            ->findBy(['departement' => $this->params['departement']]);

        $genre = $em
            ->getRepository(GenreVehicule::class)
            ->findOneBy(['code' => 'VP']);

        $categ = $em
            ->getRepository(CategorieVehicule::class)
            ->findOneBy(['libelle' => 'Berline']);

        $carb = $em
            ->getRepository(CarburantVehicule::class)
            ->findOneBy(['code' => 'GO']);

        $transm = $em
            ->getRepository(TransmissionVehicule::class)
            ->findOneBy(['code' => 'BVM']);

        $atelier = $em
            ->getRepository(Unite::class)
            ->findOneBy(['code_unite' => $this->params['unite']]);

        $vl = new Vehicule();
        $vl
            ->setNbPlaces(5)
            ->setSerigraphie(0)
            ->setGenre($genre)
            ->setCategorie($categ)
            ->setCarburant($carb)
            ->setTransmission($transm)
            ->setDepartement($this->params['departement'])
            ->setUnite($atelier);

        $form = $this->createForm(VehiculeType::class, $vl);

        $form->handleRequest($this->request);
        if ($form->isSubmitted() && $form->isValid()) {
            $vehicule = $form->getData();

            $code_unite = $vehicule->getUnite()->getCodeUnite();
            $unite_en_bdd = $em
                ->getRepository(Unite::class)
                ->findOneBy(['code_unite' => $code_unite]);

            if (is_null($unite_en_bdd) && $this->app_const['APP_MACHINE'] !== 'chrome') {
                $ldap = new LdapService();
                $ldap_unite = $ldap->get_unite_from_ldap($code_unite);
                $unite = $ldap->format_ldap_unite($ldap_unite);
                $unite_en_bdd = new Unite();
                $unite_en_bdd
                    ->setCodeUnite($unite->code)
                    ->setNomCourt($unite->nom_court)
                    ->setNomLong($unite->nom)
                    ->setDepartement($this->params['departement']);
                $em->persist($unite_en_bdd);
                $em->flush();
            }

            $vehicule->setUnite($unite_en_bdd);

            if (!$vehicule->getId()) {
                $em->persist($vehicule);
            }
            $em->flush();
            return $this->redirectToRoute('resa_parc_upload', [
                'vehicule' => $vehicule->getId(),
                'action' => 'ajouter'
            ]);
        }

        return $this->render('parc/ajouter.html.twig', array_merge(
            $this->getAppConst(),
            $this->params,
            [
                'form' => $form,
                'action' => 'ajouter',
                'unites' => $unites
            ]
        ));
    }

    #[Route('/parc/upload', name: 'resa_parc_upload')]
    public function upload(
        ManagerRegistry $doctrine,
        SluggerInterface $slugger,
        #[Autowire('%kernel.project_dir%/assets/images/uploads')] string $photosDirectory
    ): Response {

        $this->setAppConst();

        $vehicule_id = $this->request->get('vehicule');
        $action = $this->request->get('action');
        $token = $this->request->get('token');
        $em = $doctrine->getManager();

        if (is_null($this->params['nigend']) && is_null($token)) {
            return $this->redirectToRoute('resa_login');
        } else if (!is_null($token)) {
            $tkn = $em->getRepository(Token::class)->findOneBy(['token' => $token]);
            if (is_null($tkn))
                return $this->redirectToRoute('resa_login');
            $user = $tkn->getUser();
            if ($this->app_const['APP_TOKEN_GIVES_FULL_ACCESS'] === true) {
                $this->session->set('HTTP_NIGEND', $user->getNigend());
                $this->session->set('HTTP_UNITE', $user->getUnite());
                $this->session->set('HTTP_PROFIL', $user->getProfil());
            }
            $this->params = [
                'nigend' => $user->getNigend(),
                'unite' => $user->getUnite(),
                'profil' => $user->getProfil()
            ];
        }

        $vehicule = $em->getRepository(Vehicule::class)->findOneBy(['id' => $vehicule_id]);
        // if (count($vehicule->getPhotos()) > 0) {
        //     return $this->redirectToRoute('editer_images', [
        //         'vehicule' => $vehicule->getId(),
        //         'action' => $action
        //     ]);
        // }

        $random_hex = bin2hex(\random_bytes(18));
        $baseurl = $this->request->getScheme() . '://' . $this->request->getHttpHost() . '/parc/upload?vehicule=' . $vehicule_id . '&action=ajouter';
        $url = $baseurl . '&token=' . $random_hex;

        $tkn = $em->getRepository(Token::class)->findOneBy(['url' => $baseurl]);
        if (is_null($tkn)) {
            $usr = $em->getRepository(User::class)->findOneBy(['nigend' => $this->params['nigend']]);
            $tkn = new Token();
            $tkn->setUser($usr);
            $tkn->setToken($random_hex);
            $tkn->setUrl($baseurl);
            $em->persist($tkn);
            $em->flush();
        }

        $form = $this->createForm(PhotoType::class);

        $form->handleRequest($this->request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                /** @var UploadedFile $photoFile */

                $photos = $form->get('photos')->getData();

                // if (count($photos) === 0) {
                //     return $this->redirectToRoute('parc');
                // }

                foreach ($photos as $photoFile) {

                    if ($photoFile) {

                        $originalFilename = pathinfo($photoFile->getClientOriginalName(), PATHINFO_FILENAME);
                        $safeFilename = substr($slugger->slug($originalFilename), 0, 20);
                        $newFilename = $safeFilename . '-' . uniqid() . '.' . $photoFile->guessExtension();
                        try {
                            $photoFile->move($photosDirectory, $newFilename);
                        } catch (FileException $e) {
                        }
                        try {
                            $photoservice = new PhotoService();
                            $src = $photosDirectory . '/' . $newFilename;
                            $dest = $photosDirectory . '/mini/' . $newFilename;
                            $photoservice->createThumbnail($src, $dest, 320, null);
                        } catch (\Throwable $th) {
                            throw $th;
                        }


                        $photo = new Photo();
                        $photo->setVehicule($vehicule);
                        $photo->setPath('mini/' . $newFilename);

                        $em->persist($photo);
                        $em->flush();
                    }
                }

                return $this->redirectToRoute('resa_editer_images', [
                    'vehicule' => $vehicule->getId(),
                    'action' => $action
                ]);
            }
        }

        return $this->render('parc/upload.html.twig', array_merge(
            $this->getAppConst(),
            $this->params,
            [
                'action' => $action,
                'url' => $url,
                'vehicule' => $vehicule,
                'form' => $form,
                'token' => is_null($token) ? false : $token
            ]
        ));
    }

    #[Route('/parc/editer_images', name: 'resa_editer_images')]
    public function editer_images(
        ManagerRegistry $doctrine,
        SluggerInterface $slugger,
        #[Autowire('%kernel.project_dir%/assets/images/uploads')] string $photosDirectory
    ): Response {

        $this->setAppConst();

        $vehicule_id = $this->request->get('vehicule');
        $action = $this->request->get('action');
        $token = $this->request->get('token');
        $em = $doctrine->getManager();

        if (is_null($this->params['nigend']) && is_null($token)) {
            return $this->redirectToRoute('resa_login');
        } else if (!is_null($token)) {
            $tkn = $em->getRepository(Token::class)->findOneBy(['token' => $token]);
            if (is_null($tkn))
                return $this->redirectToRoute('resa_login');
            $user = $tkn->getUser();
            if ($this->app_const['APP_TOKEN_GIVES_FULL_ACCESS'] === true) {
                $this->session->set('HTTP_NIGEND', $user->getNigend());
                $this->session->set('HTTP_UNITE', $user->getUnite());
                $this->session->set('HTTP_PROFIL', $user->getProfil());
            }
            $this->params = [
                'nigend' => $user->getNigend(),
                'unite' => $user->getUnite(),
                'profil' => $user->getProfil()
            ];
        }

        $vehicule = $em->getRepository(Vehicule::class)->findOneBy(['id' => $vehicule_id]);

        $random_hex = bin2hex(\random_bytes(18));
        $baseurl = $this->request->getScheme() . '://' . $this->request->getHttpHost() . '/parc/upload?vehicule=' . $vehicule_id . '&action=ajouter';
        $url = $baseurl . '&token=' . $random_hex;

        $tkn = $em->getRepository(Token::class)->findOneBy(['url' => $baseurl]);
        if (is_null($tkn)) {
            $usr = $em->getRepository(User::class)->findOneBy(['nigend' => $this->params['nigend']]);
            $tkn = new Token();
            $tkn->setUser($usr);
            $tkn->setToken($random_hex);
            $tkn->setUrl($baseurl);
            $em->persist($tkn);
            $em->flush();
        }

        return $this->render('parc/upload_confirmation.html.twig', array_merge(
            $this->getAppConst(),
            $this->params,
            [
                'action' => $action,
                'url' => $url,
                'vehicule' => $vehicule,
                'token' => is_null($token) ? false : $token
            ]
        ));
    }

    #[Route('/parc/modifier/{vehicule_id}', name: 'resa_parc_modifier')]
    public function modifier(string $vehicule_id, ManagerRegistry $doctrine): Response
    {

        if (is_null($this->params['nigend']))
            return $this->redirectToRoute('resa_login');

        $this->setAppConst();

        $em = $doctrine->getManager();

        $unites = $em
            ->getRepository(Unite::class)
            ->findBy(['departement' => $this->params['departement']]);

        $vl = $em
            ->getRepository(Vehicule::class)
            ->findOneBy(['id' => $vehicule_id]);

        $form = $this->createForm(VehiculeType::class, $vl);

        $form->handleRequest($this->request);
        if ($form->isSubmitted() && $form->isValid()) {
            $vehicule = $form->getData();

            $code_unite = $vehicule->getUnite()->getCodeUnite();
            $unite_en_bdd = $em
                ->getRepository(Unite::class)
                ->findOneBy(['code_unite' => $code_unite]);
            if (is_null($unite_en_bdd) && $this->app_const['APP_MACHINE'] !== 'chrome') {
                $ldap = new LdapService();
                $ldap_unite = $ldap->get_unite_from_ldap($code_unite);
                $unite = $ldap->format_ldap_unite($ldap_unite);
                $unite_en_bdd = new Unite();
                $unite_en_bdd
                    ->setCodeUnite($unite->code)
                    ->setNomCourt($unite->nom_court)
                    ->setNomLong($unite->nom)
                    ->setDepartement($this->params['departement']);
                $em->persist($unite_en_bdd);
                $em->flush();
            }

            $vehicule->setUnite($unite_en_bdd);

            $em->persist($vehicule);
            $em->flush();

            return $this->redirectToRoute('resa_parc');
        }

        return $this->render('parc/ajouter.html.twig', array_merge(
            $this->getAppConst(),
            $this->params,
            [
                'form' => $form,
                'action' => 'modifier',
                'vehicule_id' => $vehicule_id,
                'unites' => $unites
            ]
        ));
    }

    #[Route('/parc/supprimer/{vehicule_id}', name: 'resa_parc_supprimer')]
    public function supprimer(string $vehicule_id, ManagerRegistry $doctrine): Response
    {
        if (is_null($this->params['nigend']))
            return $this->redirectToRoute('resa_login');

        $this->setAppConst();

        $em = $doctrine->getManager();

        $unites = $em
            ->getRepository(Unite::class)
            ->findBy(['departement' => $this->params['departement']]);

        $vl = $em
            ->getRepository(Vehicule::class)
            ->findOneBy(['id' => $vehicule_id]);

        $form = $this->createForm(VehiculeType::class, $vl, ['disabled' => true]);

        $form->handleRequest($this->request);
        if ($form->isSubmitted() && $form->isValid()) {
            $vehicule = $form->getData();
            $em->remove($vehicule);
            $em->flush();

            return $this->redirectToRoute('resa_parc');
        }

        return $this->render('parc/ajouter.html.twig', array_merge(
            $this->getAppConst(),
            $this->params,
            [
                'form' => $form,
                'unites' => $unites,
                'action' => 'supprimer'
            ]
        ));
    }

    #[Route('/parc/suivi/{vehicule_id}', name: 'resa_parc_suivi')]
    public function suivi(string $vehicule_id, ManagerRegistry $doctrine): Response
    {

        if (is_null($this->params['nigend']))
            return $this->redirectToRoute('resa_login');

        $this->setAppConst();

        $em = $doctrine->getManager();

        $vl = $em
            ->getRepository(Vehicule::class)
            ->findOneBy(['id' => $vehicule_id]);

        $fiches = $em
            ->getRepository(FicheSuivi::class)
            ->findBy(['vehicule' => $vl]);

        $types_suivis = $em
            ->getRepository(TypeFicheSuivi::class)
            ->findAll();

        return $this->render('parc/suivi.html.twig', array_merge(
            $this->getAppConst(),
            $this->params,
            [
                'vehicule' => $vl,
                'fiches' => $fiches,
                'types_suivis' => $types_suivis
            ]
        ));
    }

    #[Route('/parc/suivi/{reservation_id}/{fiche_suivi_type_id}', name: 'resa_parc_upload_suivi', methods: ['POST'])]
    public function upload_suivi(string $reservation_id, string $fiche_suivi_type_id, ManagerRegistry $doctrine): Response
    {

        if (is_null($this->params['nigend']))
            return $this->redirectToRoute('resa_login');

        $this->setAppConst();
        $em = $doctrine->getManager();

        // L'utilisateur uploade ses fiches de suivi (perception ou reintegration) via un formulaire POST, que l'on enregistre dans la fiche dans /assets/pdf/uploads/ avec un nom de fichier unique
        $file = $this->request->files->get('file');
        $filePath = $this->getParameter('kernel.project_dir') . '/assets/pdf/uploads/';
        $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $newFilename = $originalFilename . '-' . uniqid() . '.' . $file->guessExtension();
        try {
            $file->move($filePath, $newFilename);
        } catch (FileException $e) {
            return $this->json(['status' => 'error', 'message' => 'Erreur lors de l\'upload du fichier.']);
        }
        $reservation = $em
            ->getRepository(Reservation::class)
            ->findOneBy(['id' => $reservation_id]);

        $suivi = new FicheSuivi();
        $suivi->setReservation($reservation);
        $suivi->setVehicule($reservation->getVehicule());
        $suivi->setCreatedAt(new \DateTime('now'));
        $suivi->setPath($newFilename);
        $suivi->setType($em->getRepository(TypeFicheSuivi::class)->findOneBy(['id' => $fiche_suivi_type_id]));
        $em->persist($suivi);
        $em->flush();
        return $this->json(['status' => 'success', 'filename' => $newFilename]);
    }

    #[Route('/parc/tdb/{debut}/{fin}/{affichage}', name: 'resa_parc_tdb')]
    public function tdb(ManagerRegistry $doctrine, \DateTime $debut, \DateTime $fin, ?string $affichage = "m"): Response
    {

        if (is_null($this->params['nigend']))
            return $this->redirectToRoute('resa_login');

        $em = $doctrine->getManager();
        $this->setAppConst();

        $tmp = new \DateTime('now');
        $max = new \DateTime($tmp->format('Y-m-d') . ' 23:59:59');
        $max->modify('+' . $this->app_const['APP_LIMIT_RESA_MONTHS'] . ' months');

        $limit_resa = $this->app_const['APP_LIMIT_RESA_MONTHS'];
        $limit_resa = $limit_resa . ' mois';

        $dates = [];
        $tomorrow = $tmp->modify('+1 days');
        for ($i = 0; $tomorrow->format("Y-m-d") !== $max->format("Y-m-d"); $i++) {
            $dates[] = $tomorrow->format("Y-m-d");
            $tomorrow->modify('+ 1 days');
        }

        $horaires = $em
            ->getRepository(HoraireOuverture::class)
            ->findAll();

        $horaires_csag = $this->horaires_to_arr($horaires);
        $reservations = $em->getRepository(Reservation::class)
            ->findBetween($this->params['departement'], $debut, $fin);

        $vehicules = [];
        $ids = [];

        $reservations = $this->dispatch_parc_in_tdb($doctrine, $reservations);

        foreach ($reservations as $reservation) {
            $vl = $reservation->getVehicule();
            if (!in_array($vl->getId(), $ids)) {
                $ids[] = $vl->getId();
                $vehicules[] = [
                    'id' => $vl->getId(),
                    'immatriculation' => $vl->getImmatriculation(),
                    'marque' => $vl->getMarque(),
                    'modele' => $vl->getModele(),
                    'color' => $vl->getCouleurVignette()
                ];
            }

            if ($affichage === 'j') {
                $reservation->starts = $reservation->getDateDebut()->format('Ymd') === $debut->format('Ymd');
                $reservation->ends = $reservation->getDateFin()->format('Ymd') === $debut->format('Ymd');
                $reservation->nor = !($reservation->starts || $reservation->ends);
                $heure_debut = $reservation->starts ? $reservation->getHeureDebut() : '00:00';
                $reservation->rowspan = $this->get_rowspan($reservation, $debut, $horaires_csag);
                $reservation->heure_affichee = $heure_debut;
            }
        }

        foreach ($horaires_csag as $day => $heures) {
            if ($heures === "")
                // replissage par défaut pour les jours où le CSAG est fermé
                $horaires_csag[$day] = '8,9,10,11,14,15,16';
        }

        return $this->render('parc/tdb.html.twig', array_merge(
            $this->getAppConst(),
            $this->params,
            [
                'limit_resa' => $limit_resa,
                'horaires' => $horaires_csag,
                'debut' => $debut,
                'fin' => $fin,
                'max' => $max,
                'vehicules' => $vehicules,
                'reservations' => $reservations,
                'affichage' => $affichage,
                'dates' => $dates
            ]
        ));
    }

    #[Route('/parc/rotate', name: 'resa_rotate', methods: ['POST'])]
    public function rotate(
        ManagerRegistry $doctrine,
        RequestStack $requestStack,
        #[Autowire('%kernel.project_dir%/assets/images/uploads')] string $photosDirectory
    ) {
        $em = $doctrine->getManager();
        $images = (array) json_decode($this->request->getContent());
        foreach ($images as $image) {
            $id = $image->id;
            $angle = intval($image->rotation);
            if ($image->suppr) {
                $photo = $em
                    ->getRepository(Photo::class)
                    ->findOneBy(['id' => $id]);
                $vl = $photo->getVehicule();
                $vl->removePhoto($photo);
                $em->flush();
            } else if (!$angle) {
                continue;
            } else {
                $photo = $em
                    ->getRepository(Photo::class)
                    ->findOneBy(['id' => $id]);
                $src = $photosDirectory . '/' . $photo->getPath();
                $photoservice = new PhotoService();
                $photoservice->rotate($src, $angle);
            }
        }
        return $this->json($images);
    }


    private function getAppConst()
    {
        return $this->app_const;
    }

    private function setAppConst()
    {
        $this->app_const = [];
        foreach (
            [
                'app.env',
                'app.machine',
                'app.name',
                'app.tagline',
                'app.slug',
                'app.limit_resa_months',
                'app.max_resa_duration',
                'app.minutes_select_interval',
                'app.token_gives_full_access',
                'app.unites_em'
            ] as $param
        ) {
            $AppConstName = strToUpper(str_replace('.', '_', $param));
            $this->app_const[$AppConstName] = $this->getParameter($param);
        }
    }

    private function dispatch_parc_in_tdb(ManagerRegistry $doctrine, $reservations)
    {

        $em = $doctrine->getManager();

        // On a besoin de connaître le "type" de valideur (CSAG, unité PJ ou Etat-Major)
        $filtre_validateur = "";

        $nigend = $this->params['nigend'];

        $user = $em->getRepository(User::class)
            ->findOneBy(['nigend' => $this->addZeros($nigend, 8)]);
        $code_unite = $user->getUnite();

        $env_unites_pj = $_ENV['APP_UNITES_PJ'] ?? '';
        $raw_unites_pj = explode(',', $env_unites_pj);
        $unites_pj = [];
        foreach ($raw_unites_pj as $code) {
            $unites_pj[] = $this->addZeros($code, 8);
        }

        if ($user->getProfil() === "SOLC") {
            $filtre_validateur = 'SOLC';
        } else if (in_array($code_unite, $unites_pj)) {
            $filtre_validateur = "PJ";
        } else {
            $code_unite_CSAG = $this->addZeros($_ENV['APP_CSAG_CODE_UNITE'], 8);
            if ($code_unite == $code_unite_CSAG) {
                $filtre_validateur = "CSAG";
            } else {
                $filtre_validateur = "EM";
            }
        }

        $resas = array_filter($reservations, function ($resa) use ($filtre_validateur) {
            if ($filtre_validateur === "SOLC")
                return true;
            $type_demande = $resa->getTypeDemande();
            // si valideur PJ --> uniquement les VLs dont le demandeur a selectionné "opérationnel"
            if ($filtre_validateur === "PJ") {
                if ($type_demande->getCode() === "ope") {
                    return true;
                }
                return false;
            }
            // Si valideur EM --> uniquement les VLs qui ont la restriction "Etat-Major"
            $vl = $resa->getVehicule();
            $restriction = $vl->getRestriction();
            $restriction_code = $restriction->getCode();

            if ($filtre_validateur === "EM") {
                if ($restriction_code === "EM") {
                    return true;
                }
                return false;
            }

            // Le valideur CSAG prend ce qu'il reste, sauf les VLs EM
            if ($filtre_validateur === "CSAG") {
                if ($restriction_code !== "EM") {
                    return true;
                }
                return false;
            }
        });

        return $resas;
    }

    private function addZeros($str, $maxlen = 2)
    {
        $str = '' . $str;
        while (strlen($str) < $maxlen)
            $str = "0" . $str;
        return $str;
    }

    private function horaires_to_arr(array $horaires)
    {
        $out = [
            'LU' => '',
            'MA' => '',
            'ME' => '',
            'JE' => '',
            'VE' => '',
            'SA' => '',
            'DI' => ''
        ];
        foreach ($horaires as $horaire) {
            $day = $horaire->getJour();
            [$Hd] = explode(':', $horaire->getDebut());
            $hd = intval($Hd);
            [$Hf] = explode(':', $horaire->getFin());
            $hf = intval($Hf);
            $out[$day] =  $out[$day] . ($out[$day] === '' ? '' : ',') . implode(',', range($hd, $hf - 1));
        }
        return $out;
    }

    private function get_rowspan(Reservation $reservation, $date_ref, $horaires_csag)
    {
        $intervalles_minutes = $this->app_const['APP_MINUTES_SELECT_INTERVAL'];

        $days = ['LU', 'MA', 'ME', 'JE', 'VE', 'SA', 'DI'];
        $D = $date_ref->format('w');
        $dow = $D == 0 ? 6 : $D - 1;
        $horaires = explode(',', $horaires_csag[$days[$dow]]);
        if (count($horaires) === 1) {
            // replissage par défaut pour les jours où le CSAG est fermé
            $horaires = ["8", "9", "10", "11", "14", "15", "16"];
        }

        $curr_date = $date_ref->format('Ymd');
        $date_debut = $reservation->getDateDebut()->format('Ymd');
        $date_fin = $reservation->getDateFin()->format('Ymd');
        $heure_debut = $date_debut === $curr_date ? $reservation->getHeureDebut() : $horaires[0] . ':00';
        $heure_fin = $curr_date === $date_fin ? $reservation->getHeureFin() : (1 + $horaires[array_key_last($horaires)]) . ':00';

        [$hd, $md] = explode(':', $heure_debut);
        $d = intval($hd) * 60 + intval($md);

        [$hf, $mf] = explode(':', $heure_fin);
        $f = intval($hf) * 60 + intval($mf);

        $diff = $f - $d;
        $rowspan = $diff / $intervalles_minutes;

        return $rowspan === 0 ? 1 : $rowspan;
    }
}
