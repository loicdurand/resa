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
use App\Entity\HoraireOuverture;
use App\Entity\Permission;
use App\Entity\TransmissionVehicule;
use App\Entity\Vehicule;
use App\Form\VehiculeType;
use PhpParser\Node\Expr\Cast\Object_;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;



class AccueilController extends AbstractController
{
    private $app_const;

    #[Route('/')]
    public function accueil(ManagerRegistry $doctrine): Response
    {
        $this->setAppConst();

        $em = $doctrine->getManager();
        $vehicules = $em
            ->getRepository(Vehicule::class)
            ->findAll();

        $horaires = $em
            ->getRepository(HoraireOuverture::class)
            ->findAll();


        $dates = [];
        $dates_fin = [];
        $now = new \DateTime('now');
        $max = new \DateTime('now');
        $max->modify('+ 3 months');
        $max_date = $max->format("Y-m-d");
        $max->modify('+ 3 weeks');
        $ok = false;
        for ($i = 0; $now->format("Y-m-d") !== $max->format("Y-m-d"); $i++) {
            $fr_date = $this->FR($now->format('Y-m-d'));
            $atelier_ouvert = $this->getHorairesByDay(substr($fr_date, 0, 2), $horaires);
            if ($now->format("Y-m-d") === $max_date)
                $ok = true;

            if (!$ok)
                $dates[] = [
                    'en' => $now->format('Y-m-d'),
                    'fr' => $i === 0 ? 'Aujourd\'hui' : ($i === 1 ? 'Demain' : $fr_date),
                    'short' => $i === 0 ? 'Auj.' : ($i === 1 ? 'Demain' :/*preg_replace('#\s.*#', ' ', $fr_date).*/ $now->format('d/m')),
                    'horaires' => $atelier_ouvert
                ];

            $dates_fin[] = [
                'en' => $now->format('Y-m-d'),
                'fr' => $i === 0 ? 'Aujourd\'hui' : ($i === 1 ? 'Demain' : $fr_date),
                'short' => $i === 0 ? 'Auj.' : ($i === 1 ? 'Demain' :/*preg_replace('#\s.*#', ' ', $fr_date).*/ $now->format('d/m')),
                'horaires' => $atelier_ouvert
            ];
            $now->modify('+ 1 days');
        }

        return $this->render('accueil/accueil.html.twig', array_merge($this->getAppConst(), [
            'vehicules' => $vehicules,
            'dates' => $dates,
            'dates_fin' => $dates_fin
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
        foreach (['app.name', 'app.tagline', 'app.slug', 'app.max_nb_mois_reservation'] as $param) {
            $AppConstName = strToUpper(str_replace('.', '_', $param));
            $this->app_const[$AppConstName] = $this->getParameter($param);
        }
    }

    private function FR(String $date)
    {
        $days = ['Dimanche', 'Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi'];
        $months = ['', 'Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin', 'Juillet', 'Août', 'Septembre', 'Octobre', 'Novembre', 'Décembre'];
        $dt = new \DateTime($date . ' 08:00:00');
        $dow = intval($dt->format('w'));
        $d  = $dt->format('d');
        $m = intval($dt->format('m'));
        $Y = $dt->format('Y');
        return $days[$dow] . ' ' . $d . ' ' . $months[$m] . ' ' . $Y;
    }

    private function getHorairesByDay(String $day, array $horaires)
    {
        $out = [];
        $day = strtoupper($day);
        foreach ($horaires as $horaire) {
            if ($day == $horaire->getJour()) {
                $out[$horaire->getCreneau()] = $horaire->getDebut() . '-' . $horaire->getFin();
            }
        }

        return $out;
    }
}
