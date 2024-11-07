<?php
// src/Controller/LuckyController.php
namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

use Doctrine\Persistence\ManagerRegistry;
use App\Entity\Role;
use App\Entity\Action;
use App\Entity\Permission;


class ParcController extends AbstractController
{
    private $app_const;

    #[Route('/parc/afficher')]
    public function afficher(ManagerRegistry $doctrine): Response
    {
        $this->setAppConst();

        $em = $doctrine->getManager();
        $roles = $em
            ->getRepository(Role::class)
            ->findAll();

        //dd($roles[0]->getPermissions()[0]->getAction()->getNom());


        return $this->render('parc/afficher.html.twig', array_merge($this->getAppConst(), [
            'roles' => $roles,
            'number' =>13,
            'page'=> 'lucky/number.html.twig'
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
