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
    private $em;

    public function __construct(ManagerRegistry $doctrine)
    {
        $this->em = $em = $doctrine->getManager();
    }

    #[Route('/')]
    public function accueil(): Response
    {
        $this->setAppConst();

        $vehicules = $this->em
            ->getRepository(Vehicule::class)
            ->findAll();

        $categories = [];
        $transmissions = [];
        $category_ids = [];
        $transmission_ids = [];
        foreach ($vehicules as $vl) {
            $cat = $vl->getCategorie();
            $cat_id = $cat->getId();
            $trans = $vl->getTransmission();
            $trans_id = $trans->getId();

            if (!in_array($cat_id, $category_ids)) {
                $category_ids[] = $cat_id;
                $categories[] = $cat;
            }
            if (!in_array($trans_id, $transmission_ids)) {
                $transmission_ids[] = $trans_id;
                $transmissions[] = [
                    'code' => $trans->getCode(),
                    'libelle' => $trans->getLibelle()
                ];
            }
        }

        $horaires = $this->em
            ->getRepository(HoraireOuverture::class)
            ->findAll();

        $dates = [];
        $dates_fin = [];
        $timezone = new \DateTimeZone('America/Guadeloupe');
        $now = new \DateTime('now', $timezone);
        $max = new \DateTime('now', $timezone);
        $max->modify($this->app_const['APP_LIMIT_RESA']);
        $max_date = $max->format("Y-m-d");
        $max->modify($this->app_const['APP_MAX_RESA_DURATION']);
        $max->modify('- 1 days');
        $ok = false;
        for ($i = 0; $now->format("Y-m-d") !== $max->format("Y-m-d"); $i++) {
            $fr_date =  $this->FR($now->format('Y-m-d'));
            $atelier_ouvert = $this->getHorairesByDay(substr($fr_date, 0, 2), $horaires);
            if ($now->format("Y-m-d") === $max_date) {
                $ok = true;
            }

            if (!$ok) {
                $dates[] = [
                    'en' => $now->format('Y-m-d'),
                    'fr' => $i === 0 ? 'Aujourd\'hui' : ($i === 1 ? 'Demain' : $fr_date),
                    'short' => $i === 0 ? 'Auj.' : ($i === 1 ? 'Demain' : /*preg_replace('#\s.*#', ' ', $fr_date).*/ $now->format('d/m')),
                    'horaires' => $atelier_ouvert
                ];
            }

            $dates_fin[] = [
                'en' => $now->format('Y-m-d'),
                'fr' => $i === 0 ? 'Aujourd\'hui' : ($i === 1 ? 'Demain' : $fr_date),
                'short' => $i === 0 ? 'Auj.' : ($i === 1 ? 'Demain' : /*preg_replace('#\s.*#', ' ', $fr_date).*/ $now->format('d/m')),
                'horaires' => $atelier_ouvert
            ];
            $now->modify('+ 1 days');
        }

        $last_date = [];
        for ($i = count($dates) - 1; $i > 0; $i--) {
            if (count($dates_fin[$i]['horaires']) > 0) {
                $last_date = $dates_fin[$i];
                $i = 0;
            }
        }

        return $this->render('accueil/accueil.html.twig', array_merge($this->getAppConst(), [
            'vehicules' => $vehicules,
            'categories' => $categories,
            'transmissions' => $transmissions,
            'dates' => $dates,
            'dates_fin' => $dates_fin,
            'last_date' => $last_date
        ]));
    }

    #[Route(path: '/reserver/{vl_id}')]
    #[Route(path: '/reserver/{vl_id}/{from}/{to}')]
    public function reserver(string $vl_id, string $from = '', string $to = ''): Response
    {
        $this->setAppConst();

        if ($from === '') {
            $timezone = new \DateTimeZone('America/Guadeloupe');
            $now = new \DateTime('now', $timezone);
            $tmp = new \DateTime('now', $timezone);
            $max = new \DateTime($tmp->format('Y-m-d') . ' 23:59:59');
            $max->modify($this->app_const['APP_LIMIT_RESA']);
            $from =  $now->format('Y-m-d H:i:s');
            $to = $max->format('Y-m-d H:i:s');
        }

        $vehicule = $this->em
            ->getRepository(Vehicule::class)
            ->findOneBy(['id' => $vl_id]);

        return $this->render('accueil/reserver.html.twig', array_merge($this->getAppConst(), [
            'vehicule' => $vehicule
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
        //dd($this->getParameter('app.max_resa_duration'));
        foreach (['app.name', 'app.tagline', 'app.slug', 'app.limit_resa', 'app.max_resa_duration'] as $param) {
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
