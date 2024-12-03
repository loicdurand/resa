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
use App\Entity\Permission;


class CompteController extends AbstractController
{
    private $app_const;
    private $requestStack, $session;
    public $params, $request;

    #[Route('/compte')]
    public function compte(ManagerRegistry $doctrine, RequestStack $requestStack): Response
    {
        $this->setAppConst();

        $this->request = Request::createFromGlobals();
        $this->requestStack = $requestStack;
        $this->session = $this->requestStack->getSession();

        $this->session = $this->requestStack->getSession();

        $this->params = [
            'nigend' => $this->session->get('HTTP_NIGEND'),
            'unite' => $this->session->get('HTTP_UNITE'),
            'profil' => $this->session->get('HTTP_PROFIL')
        ];

        $em = $doctrine->getManager();
        $roles = $em
            ->getRepository(Role::class)
            ->findAll();

        return $this->render('compte/compte.html.twig', array_merge(
            $this->getAppConst(),
            $this->params,
            [
                'roles' => $roles,
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
                'app.dev_nigend_default'
            ] as $param
        ) {
            $AppConstName = strToUpper(str_replace('.', '_', $param));
            $this->app_const[$AppConstName] = $this->getParameter($param);
        }
    }
}
