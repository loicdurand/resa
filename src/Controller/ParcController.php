<?php
// src/Controller/LuckyController.php
namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

use Doctrine\Persistence\ManagerRegistry;
use App\Entity\Role;
use App\Entity\Action;
use App\Entity\GenreVehicule;
use App\Entity\Permission;
use App\Entity\Vehicule;
use App\Form\VehiculeType;

class ParcController extends AbstractController
{
    private $app_const;

    #[Route('/parc/afficher')]
    public function afficher(ManagerRegistry $doctrine): Response
    {
        $this->setAppConst();

        $em = $doctrine->getManager();
        $vehicules = $em
            ->getRepository(Vehicule::class)
            ->findAll();

        $genre = $em
        ->getRepository(GenreVehicule::class)
        ->findOneBy(['code'=>'VP']);
        $vl = new Vehicule();
        $vl->setGenre($genre);

        $form = $this->createForm(VehiculeType::class, $vl);

        //dd($vehicules);

        return $this->render('parc/afficher.html.twig', array_merge($this->getAppConst(), [
            'vehicules' => $vehicules,
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
