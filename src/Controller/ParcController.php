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

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

use App\Service\PhotoService;

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
            'profil' => $this->session->get('HTTP_PROFIL')
        ];
    }


    #[Route('/parc', name: 'parc')]
    public function afficher(ManagerRegistry $doctrine): Response
    {
        if (is_null($this->params['nigend']))
            return $this->redirectToRoute('login');

        $this->setAppConst();

        $em = $doctrine->getManager();
        $vehicules = $em
            ->getRepository(Vehicule::class)
            ->findAll();

        //dd($vehicules);

        return $this->render('parc/afficher.html.twig', array_merge(
            $this->getAppConst(),
            $this->params,
            [
                'vehicules' => $vehicules,
            ]
        ));
    }

    #[Route('/parc/ajouter')]
    public function ajouter(ManagerRegistry $doctrine): Response
    {
        if (is_null($this->params['nigend']))
            return $this->redirectToRoute('login');

        $this->setAppConst();

        $em = $doctrine->getManager();

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

        $vl = new Vehicule();
        $vl
            ->setNbPlaces(5)
            ->setSerigraphie(0)
            ->setGenre($genre)
            ->setCategorie($categ)
            ->setCarburant($carb)
            ->setTransmission($transm);

        $form = $this->createForm(VehiculeType::class, $vl);

        $form->handleRequest($this->request);
        if ($form->isSubmitted() && $form->isValid()) {
            $vehicule = $form->getData();
            if (!$vehicule->getId()) {
                $em->persist($vehicule);
            }
            $em->flush();
            return $this->redirectToRoute('upload', [
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
            ]
        ));
    }

    #[Route('/parc/upload', name: 'upload')]
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
            return $this->redirectToRoute('login');
        } else if (!is_null($token)) {
            $tkn = $em->getRepository(Token::class)->findOneBy(['token' => $token]);
            if (is_null($tkn))
                return $this->redirectToRoute('login');
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

        $random_hex = bin2hex(random_bytes(18));
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

                return $this->redirectToRoute('parc');
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

    #[Route('/parc/modifier/{vehicule_id}')]
    public function modifier(string $vehicule_id, ManagerRegistry $doctrine): Response
    {
        if (is_null($this->params['nigend']))
            return $this->redirectToRoute('login');

        $this->setAppConst();

        $em = $doctrine->getManager();

        $vl = $em
            ->getRepository(Vehicule::class)
            ->findOneBy(['id' => $vehicule_id]);

        $form = $this->createForm(VehiculeType::class, $vl);

        $form->handleRequest($this->request);
        if ($form->isSubmitted() && $form->isValid()) {
            $vehicule = $form->getData();
            $em->persist($vehicule);
            $em->flush();

            return $this->redirectToRoute('parc');
        }

        return $this->render('parc/ajouter.html.twig', array_merge(
            $this->getAppConst(),
            $this->params,
            [
                'form' => $form,
                'action' => 'modifier',
                'vehicule_id' => $vehicule_id
            ]
        ));
    }

    #[Route('/parc/supprimer/{vehicule_id}')]
    public function supprimer(string $vehicule_id, ManagerRegistry $doctrine): Response
    {
        if (is_null($this->params['nigend']))
            return $this->redirectToRoute('login');

        $this->setAppConst();

        $em = $doctrine->getManager();

        $vl = $em
            ->getRepository(Vehicule::class)
            ->findOneBy(['id' => $vehicule_id]);

        $form = $this->createForm(VehiculeType::class, $vl, ['disabled' => true]);

        $form->handleRequest($this->request);
        if ($form->isSubmitted() && $form->isValid()) {
            $vehicule = $form->getData();
            $em->remove($vehicule);
            $em->flush();

            return $this->redirectToRoute('parc');
        }

        return $this->render('parc/ajouter.html.twig', array_merge(
            $this->getAppConst(),
            $this->params,
            [
                'form' => $form,
                'action' => 'supprimer'
            ]
        ));
    }

    #[Route('/parc/tdb/{debut}/{fin}/{affichage}')]
    public function tdb(ManagerRegistry $doctrine, \DateTime $debut, \DateTime $fin, ?string $affichage = "m"): Response
    {

        if (is_null($this->params['nigend']))
            return $this->redirectToRoute('login');

        $em = $doctrine->getManager();
        $this->setAppConst();

        $tmp = new \DateTime('now');
        $max = new \DateTime($tmp->format('Y-m-d') . ' 23:59:59');
        $max->modify('+' . $this->app_const['APP_LIMIT_RESA_MONTHS'] . ' months');

        $limit_resa = $this->app_const['APP_LIMIT_RESA_MONTHS'];
        $limit_resa = $limit_resa . ' mois';

        $horaires = $em
            ->getRepository(HoraireOuverture::class)
            ->findAll();

        $horaires_csag = $this->horaires_to_arr($horaires);

        $reservations = $em->getRepository(Reservation::class)
            ->findBetween($debut, $fin);

        $vehicules = [];
        $ids = [];

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
                'affichage' => $affichage
            ]
        ));
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
                'app.token_gives_full_access'
            ] as $param
        ) {
            $AppConstName = strToUpper(str_replace('.', '_', $param));
            $this->app_const[$AppConstName] = $this->getParameter($param);
        }
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
