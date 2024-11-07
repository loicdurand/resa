<?php
// src/Controller/LuckyController.php
namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;


class AccueilController extends AbstractController
{
    private $app_const;

    #[Route('/')]
    public function accueil(): Response
    {
        $this->setAppConst();

        $number = random_int(0, 100);

        return $this->render('accueil/liste.html.twig', array_merge($this->getAppConst(), [
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
