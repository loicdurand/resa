<?php

namespace App\Controller;

use App\Entity\Reservation;
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
            'profil' => $this->session->get('HTTP_PROFIL')
        ];
    }

    #[Route(path: '/historique/confirmation', name: 'success')]
    public function confirmation(): Response
    {
        if (is_null($this->params['nigend'])) {
            return $this->redirectToRoute('login');
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

    #[Route(path: '/historique', name: 'historique')]
    public function historique(): Response
    {
        if (is_null($this->params['nigend'])) {
            return $this->redirectToRoute('login');
        }

        $this->setAppConst();

        $nigend = $this->params['nigend'];
        $resas = $this->em
            ->getRepository(Reservation::class)
            ->findByNigend($nigend);

        return $this->render('historique/historique.html.twig', array_merge(
            $this->getAppConst(),
            $this->params,
            [
                'reservations' => $resas,
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
