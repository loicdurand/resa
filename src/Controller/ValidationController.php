<?php
// src/Controller/LuckyController.php
namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

use App\Entity\Role;
use App\Entity\Action;
use App\Entity\Atelier;
use App\Entity\HoraireOuverture;
use App\Entity\Permission;
use App\Entity\Vehicule;
use App\Entity\Reservation;
use App\Entity\StatutReservation;
use App\Form\HoraireOuvertureType;


class ValidationController extends AbstractController
{
    private $app_const;
    private $requestStack, $session;
    public $params, $request;

    public function __construct(RequestStack $requestStack)
    {
        $this->request = Request::createFromGlobals();
        $this->requestStack = $requestStack;
        $this->session = $this->requestStack->getSession();
        // /* paramètres session */
        $this->params = [
            'nigend' => $this->session->get('HTTP_NIGEND'),
            'unite' => $this->session->get('HTTP_UNITE'),
            'profil' => $this->session->get('HTTP_PROFIL')
        ];
    }

    #[Route('/validation', name: 'validation')]
    public function validation(ManagerRegistry $doctrine, RequestStack $requestStack): Response
    {
        if (is_null($this->params['nigend']))
            return $this->redirectToRoute('login');

        $this->setAppConst();

        $em = $doctrine->getManager();

        $statut_en_attente = $em
            ->getRepository(StatutReservation::class)
            ->findOneBy(['code' => 'En attente']);

        $resas_en_attente = $em
            ->getRepository(Reservation::class)
            ->findBy(
                ['statut' => $statut_en_attente->getId()],
                ['date_debut' => 'ASC']
            );

        return $this->render('validation/validation.html.twig', array_merge(
            $this->getAppConst(),
            $this->params,
            [
                'reservations' => $resas_en_attente,
            ]
        ));
    }

    #[Route('/validation/vehicules', name: 'vehicules', methods: ['POST'])]
    public function vehicules(ManagerRegistry $doctrine, RequestStack $requestStack)
    {
        $data = (array) json_decode($this->request->getContent());
        $id = $data['id'];
        $em = $doctrine->getManager();

        $vl_equiv = $em
            ->getRepository(Vehicule::class)
            ->getVehiculeEquiv($id);

        // if ($this->getParameter('app.env') == 'dev')
        //     sleep(seconds: 1.5);

        return $this->json([
            'vl' => $vl_equiv
        ]);
    }

    #[Route('/validation/valid', name: 'valid', methods: ['POST'])]
    public function valid(ManagerRegistry $doctrine, RequestStack $requestStack)
    {
        $data = (array) json_decode($this->request->getContent());
        $id = $data['id'];
        $em = $doctrine->getManager();

        if ($this->getParameter('app.env') == 'dev')
            sleep(seconds: 1.5);

        $statut_valide = $em
            ->getRepository(StatutReservation::class)
            ->findOneBy(['code' => 'Confirmée']);

        $reservation = $em->getRepository(Reservation::class)
            ->findOneBy(['id' => $id]);

        $reservation->setStatut($statut_valide);
        $em->persist($reservation);
        $em->flush();

        return $this->json([
            'id' => $reservation->getId(),
            'statut' => $reservation->getStatut()->getCode()
        ]);
    }

    #[Route('/validation/modif', name: 'modif', methods: ['POST'])]
    public function modif(ManagerRegistry $doctrine, RequestStack $requestStack)
    {
        $data = (array) json_decode($this->request->getContent());
        $id = $data['id'];
        $vehicule_id = $data['vl'];
        $em = $doctrine->getManager();

        if ($this->getParameter('app.env') == 'dev')
            sleep(seconds: 1.5);

        $statut_valide = $em
            ->getRepository(StatutReservation::class)
            ->findOneBy(['code' => 'Confirmée']);

        $vl = $em
            ->getRepository(Vehicule::class)
            ->findOneBy(['id' => $vehicule_id]);

        $reservation = $em->getRepository(Reservation::class)
            ->findOneBy(['id' => $id]);

        $reservation->setStatut($statut_valide);
        $reservation->setVehicule($vl);

        $em->persist($reservation);
        $em->flush();

        return $this->json([
            'id' => $reservation->getId(),
            'statut' => $reservation->getStatut()->getCode()
        ]);
    }

    #[Route('/validation/suppr', name: 'suppr', methods: ['POST'])]
    public function suppr(ManagerRegistry $doctrine, RequestStack $requestStack)
    {
        $data = (array) json_decode($this->request->getContent());
        $id = $data['id'];
        $em = $doctrine->getManager();

        if ($this->getParameter('app.env') == 'dev')
            sleep(seconds: 1.5);

        $statut_annulee = $em
            ->getRepository(StatutReservation::class)
            ->findOneBy(['code' => 'Annulée']);

        $reservation = $em->getRepository(Reservation::class)
            ->findOneBy(['id' => $id]);

        $reservation->setStatut($statut_annulee);
        $em->persist($reservation);
        $em->flush();

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
        foreach (
            [
                'app.env',
                'app.name',
                'app.tagline',
                'app.slug',
                'app.limit_resa_months',
                'app.max_resa_duration',
                'app.minutes_select_interval',
            ] as $param
        ) {
            $AppConstName = strToUpper(str_replace('.', '_', $param));
            $this->app_const[$AppConstName] = $this->getParameter($param);
        }
    }
}
