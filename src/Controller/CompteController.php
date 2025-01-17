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
use App\Form\HoraireOuvertureType;

class CompteController extends AbstractController
{
    private $app_const;
    private $requestStack;
    private $session;
    public $params;
    public $request;

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

    #[Route('/compte', name: 'compte')]
    public function compte(ManagerRegistry $doctrine, RequestStack $requestStack): Response
    {
        if (is_null($this->params['nigend'])) {
            return $this->redirectToRoute('login');
        }

        $this->setAppConst();

        $open = $this->request->get('open');

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

        $unite =  $em
            ->getRepository(Atelier::class)
            ->findOneBy(['code_unite' => $this->params['unite']]);

        // GESTION DES HORAIRES - RÉSERVÉ AU CSAG
        $role = $em
            ->getRepository(Role::class)
            ->findOneBy(['nom' => $this->params['profil']]);
        $action = $em
            ->getRepository(Action::class)
            ->findOneBy(['nom' => 'GERER_HORAIRES']);
        $permission = $em
            ->getRepository(Permission::class)
            ->findOneBy([
                'role' => $role->getId(),
                'action' => $action->getId()
            ]);

        $action_params = [
            'open' => $open,
            'roles' => $roles
        ];

        if (!is_null($permission)) {
            $horaires = $unite->getHorairesOuverture();
            $horaire = new HoraireOuverture();
            $horaire->setCodeUnite($unite);
            $form = $this->createForm(HoraireOuvertureType::class, $horaire);

            $form->handleRequest($this->request);
            if ($form->isSubmitted() && $form->isValid()) {
                $data = $form->getData();
                $exists = false;

                foreach ($horaires as $h) {
                    if (
                        $data->getCodeUnite() === $h->getCodeUnite() &&
                        $data->getJour() === $h->getJour() &&
                        $data->getCreneau() === $h->getCreneau()
                    ) {
                        $exists = true;
                        if (is_null($data->getDebut())) {
                            $em->remove($h);
                        } else {
                            $h->setDebut($data->getDebut());
                            $h->setFin($data->getFin());
                            $em->persist($h);
                        }
                        $em->flush();
                    }
                }

                if (!$exists) {
                    if (is_null($data->getDebut())) {
                        $em->remove($h);
                    } else {
                        $em->persist($data);
                    }
                    $em->flush();
                }

                return $this->redirectToRoute('compte', [
                    'open' => 'compte/gestion_horaires.html.twig'
                ]);
            }

            $action_params['form'] = $form;
            $action_params['horaires'] = $horaires;
        }

        return $this->render('compte/compte.html.twig', array_merge(
            $this->getAppConst(),
            $this->params,
            $action_params
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
}
