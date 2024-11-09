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
    private $request;
    private $requestStack;

    public function __construct(RequestStack $requestStack)
    {
        $this->request = Request::createFromGlobals();
        $this->requestStack = $requestStack;
        // $this->session = $this->requestStack->getSession();
        // /* paramètres session */
        // $this->nigend = $this->session->get('HTTP_NIGEND');
        // $this->unite =  $this->addZeros($this->session->get('HTTP_UNITE'), 8);
        // $this->profil = $this->session->get('HTTP_PROFIL');
    }


    #[Route('/parc/', name: 'parc')]
    public function afficher(ManagerRegistry $doctrine): Response
    {
        $this->setAppConst();

        $em = $doctrine->getManager();
        $vehicules = $em
            ->getRepository(Vehicule::class)
            ->findAll();

        //dd($vehicules);

        return $this->render('parc/afficher.html.twig', array_merge($this->getAppConst(), [
            'vehicules' => $vehicules,
        ]));
    }

    #[Route('/parc/ajouter')]
    public function ajouter(?Vehicule $vehicule, Request $request, ManagerRegistry $doctrine): Response
    {
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

        return $this->render('parc/ajouter.html.twig', array_merge($this->getAppConst(), [
            'form' => $form
        ]));
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
        foreach (['app.name', 'app.tagline', 'app.slug'] as $param) {
            $AppConstName = strToUpper(str_replace('.', '_', $param));
            $this->app_const[$AppConstName] = $this->getParameter($param);
        }
    }
}
