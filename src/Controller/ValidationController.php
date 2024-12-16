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
                'reservations' => $resas_en_attente
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
