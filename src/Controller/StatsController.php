<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Validator\Constraints\Json;

use App\Repository\VehiculeRepository;

final class StatsController extends AbstractController
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
            'profil' => $this->session->get('HTTP_PROFIL'),
            'departement' => $this->session->get('HTTP_DEPARTEMENT'),
        ];
    }

    #[Route('/stats', name: 'resa_stats')]
    public function index(): Response
    {

        if (is_null($this->params['nigend']))
            return $this->redirectToRoute('resa_login');

        $this->setAppConst();

        return $this->render('stats/stats.html.twig', array_merge(
            $this->getAppConst(),
            $this->params,
            []
        ));
    }

    #[Route('/stats/getdata', name: 'resa_stats_getdata', methods: ['GET'])]
    public function getdata(ManagerRegistry $manager): JsonResponse
    {

        $labels = [];
        $evolutionVehicules = [];
        $vehiculesReserves = [];

        $vl_repo = new VehiculeRepository($manager);
        $stocks = $vl_repo->getStockByMonths();

        foreach ($stocks as $data) {
        }

        sleep(0.5);
        return new JsonResponse([
            'labels' => ['Sem 1', 'Sem 2', 'Sem 3', 'Sem 4', 'Sem 5', 'Sem 6'],
            'evolutionVehicules' => [5, 5, 8, 10, 12, 15],
            'vehiculesReserves' => [4, 5, 8, 10, 12, 15], // On simule la saturation
            'repartitionDuree' => [
                '1-2 jours' => 10,
                '3-7 jours' => 5,
                'Plus de 1 mois' => 45 // Le coupable est ici
            ]

        ]);
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
}
