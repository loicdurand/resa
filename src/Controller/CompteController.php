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


class CompteController extends AbstractController
{
    private $app_const;

    #[Route('/compte')]
    public function compte(ManagerRegistry $doctrine): Response
    {
        $this->setAppConst();

        $em = $doctrine->getManager();
        $roles = $em
            ->getRepository(Role::class)
            ->findAll();

        foreach ($roles as $role) {
            $perms = $em
                ->getRepository(Permission::class)
                ->findByRole($role->getId());

            foreach ($perms as $perm) {
                $role->addPermission(permission: $perm);
            }
        }

        dd($roles);


        return $this->render('compte/compte.html.twig', array_merge($this->getAppConst(), [
            'number' => $number,
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
