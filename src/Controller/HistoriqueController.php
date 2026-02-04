<?php

namespace App\Controller;

use App\Entity\Reservation;
use App\Entity\User;
use App\Entity\StatutReservation;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class HistoriqueController extends AbstractController
{
    private $app_const;
    private $em;
    private $requestStack;
    private $session;
    public $request;
    public $params;

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

    #[Route(path: '/historique/confirmation', name: 'resa_success')]
    public function confirmation(): Response
    {
        if (is_null($this->params['nigend'])) {
            return $this->redirectToRoute('resa_login');
        }

        $this->setAppConst();

        $nigend = $this->params['nigend'];
        $last_resa = $this->em
            ->getRepository(Reservation::class)
            ->findLastByNigend($nigend);

        return $this->render('historique/confirmation.html.twig', array_merge(
            $this->getAppConst(),
            $this->params,
            [
                'reservation' => $last_resa,
                'vehicule' => $last_resa->getVehicule()
            ]
        ));
    }

    #[Route(path: '/historique', name: 'resa_historique')]
    public function historique(): Response
    {
        if (is_null($this->params['nigend'])) {
            return $this->redirectToRoute('resa_login');
        }

        $this->setAppConst();

        $nigend = $this->params['nigend'];
        $unite = $this->params['unite'];
        $nigends = [];

        $camarades = $this->em
            ->getRepository(User::class)
            ->findBy(['unite' => $unite]);

        foreach ($camarades as $camarade) {
            if (!array_key_exists($camarade->getNigend(), $nigends)) {
                $nigends[] = $camarade->getNigend();
            }
        }

        $resas = $this->em
            ->getRepository(Reservation::class)
            ->findByNigends($nigends);

        $nigends = [];
        foreach ($resas as $resa) {
            $nigend = $resa->getUser();
            if (!array_key_exists($nigend, $nigends)) {
                $usr = $this->em->getRepository(User::class)->findOneBy(['nigend' => $nigend]);
                if (!is_null($usr)) {
                    $mail = $usr->getMail();
                    [$uid] = preg_split("/@/", $mail);
                } else {
                    $uid = '';
                }
                $nigends[$nigend] = $uid;
            }

            $nigend = $resa->getDemandeur();
            if (!array_key_exists($nigend, $nigends)) {
                $usr = $this->em->getRepository(User::class)->findOneBy(['nigend' => $nigend]);
                if (!is_null($usr)) {
                    $mail = $usr->getMail();
                    [$uid] = preg_split("/@/", $mail);
                } else {
                    $uid = '';
                }
                $nigends[$nigend] = $uid;
            }
        }

        return $this->render('historique/historique.html.twig', array_merge(
            $this->getAppConst(),
            $this->params,
            [
                'reservations' => $resas,
                'nigends' => $nigends
            ]
        ));
    }

    #[Route('/historique/annuler', name: 'resa_historique_annuler', methods: ['POST'])]
    public function historique_annuler(ManagerRegistry $doctrine)
    {
        $data = (array) json_decode($this->request->getContent());
        $id = $data['id'];
        $msg = $data['msg'];

        $statut_annule_usr = $this->em
            ->getRepository(StatutReservation::class)
            ->findOneBy(['code' => 'Annulée (USR)']);

        $reservation = $this->em
            ->getRepository(Reservation::class)
            ->findOneBy(['id' => $id]);

        // La réservation est-elle en cours ?
        $deb = new \DateTime($reservation->getDateDebut()->format('Y-m-d') . ' ' . $reservation->getHeureDebut() . ':00');
        $fin = new \DateTime($reservation->getDateFin()->format('Y-m-d') . ' ' . $reservation->getHeureFin() . ':00');
        $now = new \DateTime('now');
        if ($now >= $deb && $now <= $fin) {
            $timezone = new \DateTimeZone('America/Guadeloupe');
            $now->setTimezone($timezone);
            $reservation->setDateFin($now);
            $reservation->setHeureFin($now->format('h:i'));
        }

        $reservation->setStatut($statut_annule_usr);
        if ($msg != '') {
            $obs = ' // ' . $reservation->getObservation();
            $msg_len = strlen($msg);
            $obs_len = strlen($obs);
            if ($msg_len + $obs_len >= 255) {
                if ($msg_len >= 255)
                    $msg = substr($msg, 0, 254);
                else
                    $msg = $msg . substr($obs, 0, $msg_len -  254 - 4);
            } else {
                $msg = $msg . $obs;
            }

            $reservation->setObservation($msg);
        }

        $this->em->persist($reservation);
        $this->em->flush();

        if ($this->getParameter('app.env') == 'dev')
            sleep(seconds: 1.5);

        return $this->json([
            'id' => $reservation->getId(),
            'statut' => $reservation->getStatut()->getCode()
        ]);
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
                'app.token_gives_full_access',
                'app.unites_em'
            ] as $param
        ) {
            $AppConstName = strToUpper(str_replace('.', '_', $param));
            $this->app_const[$AppConstName] = $this->getParameter($param);
        }
    }
}
