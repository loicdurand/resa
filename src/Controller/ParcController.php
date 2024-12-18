<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

use Doctrine\Persistence\ManagerRegistry;
use App\Entity\Role;
use App\Entity\Action;
use App\Entity\CarburantVehicule;
use App\Entity\CategorieVehicule;
use App\Entity\GenreVehicule;
use App\Entity\Permission;
use App\Entity\TransmissionVehicule;
use App\Entity\Vehicule;
use App\Form\VehiculeType;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

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
            return $this->redirectToRoute('parc');
        }

        return $this->render('parc/ajouter.html.twig', array_merge(
            $this->getAppConst(),
            $this->params,
            [
                'form' => $form,
                'action' => 'ajouter'
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
                'action' => 'modifier'
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
