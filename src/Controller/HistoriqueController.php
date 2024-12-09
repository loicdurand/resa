<?php

namespace App\Controller;

use App\Entity\Reservation;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Doctrine\Persistence\ManagerRegistry;
use App\Entity\Role;
use App\Entity\Action;
use App\Entity\CarburantVehicule;
use App\Entity\CategorieVehicule;
use App\Entity\GenreVehicule;
use App\Entity\HoraireOuverture;
use App\Entity\Permission;
use App\Entity\StatutReservation;
use App\Entity\TransmissionVehicule;
use App\Entity\Vehicule;
use App\Form\ReservationType;
use App\Form\VehiculeType;
use Doctrine\Common\Collections\Collection;
use phpDocumentor\Reflection\Types\Boolean;
use PhpParser\Node\Expr\Cast\Array_;
use PhpParser\Node\Expr\Cast\Object_;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class HistoriqueController extends AbstractController
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
            'profil' => $this->session->get('HTTP_PROFIL')
        ];
    }

    #[Route(path: '/historique/success', name: 'success')]
    public function historique(): Response
    {
        if (is_null($this->params['nigend']))
            return $this->redirectToRoute('login');

        $this->setAppConst();

        $nigend = $this->params['nigend'];
        $last_resa = $this->em
            ->getRepository(Reservation::class)
            ->findLastByNigend($nigend);

        dd($last_resa);


        return $this->render('historique/historique.html.twig', array_merge(
            $this->getAppConst(),
            $this->params,
            [
                'reservation' => $last_resa
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
                'app.dev_nigend_default'
            ] as $param
        ) {
            $AppConstName = strToUpper(str_replace('.', '_', $param));
            $this->app_const[$AppConstName] = $this->getParameter($param);
        }
    }
}
