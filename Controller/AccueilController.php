<?php

namespace App\Controller;

use App\Entity\Reservation;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Doctrine\Persistence\ManagerRegistry;

use App\Entity\HoraireOuverture;
use App\Entity\StatutReservation;
use App\Entity\Vehicule;
use App\Entity\Atelier;

use App\Form\ReservationType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class AccueilController extends AbstractController
{
    private $app_const;
    private $em;
    private $requestStack, $session;
    public $request, $params;

    public function __construct(RequestStack $requestStack, ManagerRegistry $doctrine)
    {
        $this->em = $doctrine->getManager();

        $this->request = Request::createFromGlobals();
        $this->requestStack = $requestStack;
        $this->session = $this->requestStack->getSession();
        // /* paramètres session */
        $this->params = [
            'nigend' => $this->session->get('HTTP_NIGEND'),
            'unite' => $this->session->get('HTTP_UNITE'),
            'profil' => $this->session->get('HTTP_PROFIL'),
            'departement' => $this->session->get('HTTP_DEPARTEMENT'),
        ];
    }

    #[Route('/', name: 'accueil')]
    public function accueil(): Response
    {

        if (is_null($this->params['nigend']))
            return $this->redirectToRoute('login');

        $this->setAppConst();

        $vehicules = $this->em
            ->getRepository(Vehicule::class)
            ->findBy(['departement' => $this->params['departement']]);

        $categories = [];
        $transmissions = [];
        $category_ids = [];
        $transmission_ids = [];
        foreach ($vehicules as $vl) {
            $cat = $vl->getCategorie();
            $cat_id = $cat->getId();
            $trans = $vl->getTransmission();
            $trans_id = $trans->getId();

            if (!in_array($cat_id, $category_ids)) {
                $category_ids[] = $cat_id;
                $categories[] = $cat;
            }
            if (!in_array($trans_id, $transmission_ids)) {
                $transmission_ids[] = $trans_id;
                $transmissions[] = [
                    'code' => $trans->getCode(),
                    'libelle' => $trans->getLibelle()
                ];
            }
        }

        $atelier = $this->em
        ->getRepository(Atelier::class)
        ->findOneBy(['code_unite'=>$this->params['unite']]);

        $horaires = $this->em
            ->getRepository(HoraireOuverture::class)
            ->findBy(['code_unite' => $atelier]);

        if(count($horaires)==0){
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

                    $this->em->persist($horaire);
                    $this->em->flush();
                }
            };
            $horaires = $this->em
                ->getRepository(HoraireOuverture::class)
                ->findBy(['code_unite' => $atelier]);
        }

        $dates = [];
        $dates_fin = [];
        // $timezone = new \DateTimeZone('America/Guadeloupe');
        $now = new \DateTime('now');
        $max = new \DateTime('now');
        $max->modify('+' . $this->app_const['APP_LIMIT_RESA_MONTHS'] . ' months');
        $max_date = $max->format("Y-m-d");
        //$max->modify($this->app_const['APP_MAX_RESA_DURATION']);
        //$max->modify('- 1 days');
        $ok = false;
        for ($i = 0; $now->format("Y-m-d") !== $max->format("Y-m-d"); $i++) {
            $fr_date =  $this->FR($now->format('Y-m-d'));
            $atelier_ouvert = $this->getHorairesByDay(substr($fr_date, 0, 2), $horaires);
            if ($now->format("Y-m-d") === $max_date) {
                $ok = true;
            }

            if (!$ok) {
                $dates[] = [
                    'en' => $now->format('Y-m-d'),
                    'fr' => $i === 0 ? 'Aujourd\'hui' : ($i === 1 ? 'Demain' : $fr_date),
                    'short' => $i === 0 ? 'Auj.' : ($i === 1 ? 'Demain' : /*preg_replace('#\s.*#', ' ', $fr_date).*/ $now->format('d/m')),
                    'horaires' => $atelier_ouvert
                ];
            }

            $dates_fin[] = [
                'en' => $now->format('Y-m-d'),
                'fr' => $i === 0 ? 'Aujourd\'hui' : ($i === 1 ? 'Demain' : $fr_date),
                'short' => $i === 0 ? 'Auj.' : ($i === 1 ? 'Demain' : /*preg_replace('#\s.*#', ' ', $fr_date).*/ $now->format('d/m')),
                'horaires' => $atelier_ouvert
            ];
            $now->modify('+ 1 days');
        }

        $last_date = [];
        for ($i = count($dates) - 1; $i > 0; $i--) {
            if (count($dates_fin[$i]['horaires']) > 0) {
                $last_date = $dates_fin[$i];
                $i = 0;
            }
        }

        return $this->render('accueil/accueil.html.twig', array_merge(
            $this->getAppConst(),
            $this->params,
            [
                'vehicules' => $vehicules,
                'categories' => $categories,
                'transmissions' => $transmissions,
                'dates' => $dates,
                'dates_fin' => $dates_fin,
                'last_date' => $last_date,
            ]
        ));
    }

    #[Route(path: '/reserver/{vl_id}', name: 'reserver')]
    #[Route(path: '/reserver/{vl_id}/{from}/{to}', name: 'reserver')]
    public function reserver(string $vl_id, string $from = '', string $to = ''): Response
    {
        if (is_null($this->params['nigend']))
            return $this->redirectToRoute('login');

        $this->setAppConst();

        $filtered = true;
        $tmp = new \DateTime('now');
        $max = new \DateTime($tmp->format('Y-m-d') . ' 23:59:59');
        $max->modify('+' . $this->app_const['APP_LIMIT_RESA_MONTHS'] . ' months');

        if ($from === '') {
            $filtered = false;
            // $timezone = new \DateTimeZone('America/Guadeloupe');
            $now = new \DateTime('now');
            $now->modify('+ 1 days');
            $now->modify('+ 1 hours');
            $from =  $now;
            $to = $max;
        } else {
            $from = new \DateTime($from);
            $to = new \DateTime($to);
        }

        $vehicule = $this->em
            ->getRepository(Vehicule::class)
            ->findOneBy(['id' => $vl_id]);

        $statut_resa_en_attente = $this->em
            ->getRepository(StatutReservation::class)
            ->findOneBy(['code' => 'En attente']);

        $limit_resa = $this->app_const['APP_LIMIT_RESA_MONTHS'];
        $limit_resa = $limit_resa . ' mois';

        $horaires = $this->em
            ->getRepository(HoraireOuverture::class)
            ->findAll();

        $resa = new Reservation();
        $resa->setUser($this->params['nigend']);
        $resa->setVehicule($vehicule);
        $resa->setStatut($statut_resa_en_attente);

        if ($filtered) {
            $resa->setDateDebut($from);
            $resa->setHeureDebut($from->format('h:i'));
            $resa->setDateFin($to);
            $resa->setHeureFin($to->format('h:i'));
        }

        $form = $this->createForm(ReservationType::class, $resa);

        $form->handleRequest($this->request);
        if ($form->isSubmitted() && $form->isValid()) {
            $reservation = $form->getData();
            if (!$reservation->getId()) {
                $this->em->persist($reservation);
            }
            $this->em->flush();
            return $this->redirectToRoute('success');
        }

        // dd($vehicule->getReservations()[1]->getDateFin());

        return $this->render('accueil/reserver.html.twig', array_merge(
            $this->getAppConst(),
            $this->params,
            [
                'vehicule' => $vehicule,
                'from' => [
                    'date' => $this->FR($from->format('Y-m-d'), 'short'),
                    'heure' => $from->format('H:00')
                ],
                'to' => [
                    'date' => $this->FR($to->format('Y-m-d'), 'short'),
                    'heure' => $to->format('H:00')
                ],
                'year' => $from->format('Y'),
                'max' => $max,
                'limit_resa' => $limit_resa,
                'filtered' => $filtered,
                'horaires' => $this->horaires_to_arr($horaires),
                'form' => $form
            ]
        ));
    }

    /**
     * Utils
     */

    private function getAppConst()
    {
        return $this->app_const;
    }

    private function setAppConst()
    {
        $this->app_const = [];
        //dd($this->getParameter('app.max_resa_duration'));
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

    private function FR(String $date, $short = false)
    {
        $days = ['Dimanche', 'Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi'];
        $months = ['', 'Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin', 'Juillet', 'Août', 'Septembre', 'Octobre', 'Novembre', 'Décembre'];
        $dt = new \DateTime($date . ' 08:00:00');
        $dow = intval($dt->format('w'));
        $d  = $dt->format('d');
        $m = intval($dt->format('m'));
        $Y = $dt->format('Y');
        if ($short !== false)
            return $days[$dow] . ' ' . $d . ' ' . mb_substr($months[$m], 0, 3);
        return $days[$dow] . ' ' . $d . ' ' . $months[$m] . ' ' .  $Y;
    }

    private function getHorairesByDay(String $day, array $horaires)
    {
        $out = [];
        $day = strtoupper($day);
        foreach ($horaires as $horaire) {
            if ($day == $horaire->getJour()) {
                $out[$horaire->getCreneau()] = $horaire->getDebut() . '-' . $horaire->getFin();
            }
        }

        return $out;
    }
}
