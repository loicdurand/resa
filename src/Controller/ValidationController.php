<?php
// src/Controller/LuckyController.php
namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

use App\Entity\User;
use App\Entity\Vehicule;
use App\Entity\Reservation;
use App\Entity\StatutReservation;

use App\Service\MailService;
use App\Service\SsoService;

class ValidationController extends AbstractController
{
    private $app_const;
    private $requestStack, $session;
    public $params, $request;

    public function __construct(RequestStack $requestStack)
    {
        $this->request = Request::createFromGlobals();
        $this->requestStack = $requestStack;
        $this->session = $this->requestStack->getSession();
        // /* paramètres session */
        $this->params = [
            'nigend' => $this->session->get('HTTP_NIGEND'),
            'unite' => $this->session->get('HTTP_UNITE'),
            'profil' => $this->session->get('HTTP_PROFIL'),
            'departement' => $this->session->get('HTTP_DEPARTEMENT'),
        ];
    }

    #[Route('/validation', name: 'resa_validation')]
    public function validation(ManagerRegistry $doctrine, RequestStack $requestStack): Response
    {
        if (is_null($this->params['nigend']))
            return $this->redirectToRoute('resa_login');

        $this->setAppConst();

        $em = $doctrine->getManager();

        // On a besoin de connaître le "type" de valideur (CSAG, unité PJ ou Etat-Major)
        $filtre_validateur = "";

        $nigend = $this->params['nigend'];
        $user = $em->getRepository(User::class)
            ->findOneBy(['nigend' => $nigend]);
        $code_unite = $user->getUnite();

        $env_unites_pj = $_ENV['APP_UNITES_PJ'] ?? '';
        $raw_unites_pj = explode(',', $env_unites_pj);
        $unites_pj = [];
        foreach ($raw_unites_pj as $code) {
            $unites_pj[] = $this->addZeros($code, 8);
        }

        if ($user->getProfil() === "SOLC") {
            $filtre_validateur = 'SOLC';
        } else if (in_array($code_unite, $unites_pj)) {
            $filtre_validateur = "PJ";
        } else {
            $code_unite_CSAG = $this->addZeros($_ENV['APP_CSAG_CODE_UNITE'], 8);
            if ($code_unite == $code_unite_CSAG) {
                $filtre_validateur = "CSAG";
            } else {
                $filtre_validateur = "EM";
            }
        }

        $statut_en_attente = $em
            ->getRepository(StatutReservation::class)
            ->findOneBy(['code' => 'En attente']);

        $resas_en_attente = $em
            ->getRepository(Reservation::class)
            ->findBy(
                ['statut' => $statut_en_attente->getId()],
                ['date_debut' => 'ASC']
            );

        $resas = array_filter($resas_en_attente, function ($resa) use ($filtre_validateur) {

            $vl = $resa->getVehicule();
            $restriction = $vl->getRestriction();
            $restriction_code = $restriction->getCode();

            if ($filtre_validateur === "SOLC")
                return true;

            // $type_demande = $resa->getTypeDemande();
            // si valideur PJ --> uniquement les VLs dont le demandeur a selectionné "opérationnel"

            if ($filtre_validateur === "PJ") {
                if ($restriction_code !== "EM") {
                    return true;
                }
                // if ($type_demande->getCode() === "ope") {
                //     return true;
                // }
                return false;
            }
            // Si valideur EM --> uniquement les VLs qui ont la restriction "Etat-Major"
            // $vl = $resa->getVehicule();
            // $restriction = $vl->getRestriction();
            // $restriction_code = $restriction->getCode();

            if ($filtre_validateur === "EM") {
                if ($restriction_code === "EM") {
                    return true;
                }
                return false;
            }

            // Le valideur CSAG prend ce qu'il reste 
            return true;
        });

        return $this->render('validation/validation.html.twig', array_merge(
            $this->getAppConst(),
            $this->params,
            [
                'reservations' => $resas,
                'filtre_validateur' => $filtre_validateur
            ]
        ));
    }

    #[Route('/suivi', name: 'resa_suivi')]
    public function suivi(ManagerRegistry $doctrine, RequestStack $requestStack): Response
    {
        if (is_null($this->params['nigend']))
            return $this->redirectToRoute('resa_login');

        $this->setAppConst();

        $em = $doctrine->getManager();

        $nigend = $this->params['nigend'];
        $user = $em->getRepository(User::class)
            ->findOneBy(['nigend' => $nigend]);

        $resas = $em
            ->getRepository(Reservation::class)
            ->findAllAfterNow($user->getDepartement());

        $nigends = [];
        foreach ($resas as $resa) {
            $nigend = $resa->getUser();
            if (!array_key_exists($nigend, $nigends)) {
                $usr = $em->getRepository(User::class)->findOneBy(['nigend' => $nigend]);
                $mail = $usr->getMail();
                [$uid] = preg_split("/@/", $mail);
                $nigends[$nigend] = $uid;
            }
        }

        return $this->render('validation/suivi.html.twig', array_merge(
            $this->getAppConst(),
            $this->params,
            [
                'reservations' => $resas,
                'nigends' => $nigends
                // 'filtre_validateur' => $filtre_validateur
            ]
        ));
    }

    #[Route('/validation/vehicules', name: 'resa_vehicules', methods: ['POST'])]
    public function vehicules(ManagerRegistry $doctrine, RequestStack $requestStack)
    {
        $data = (array) json_decode($this->request->getContent());
        $id = $data['id'];
        $em = $doctrine->getManager();

        $vl_equiv = $em
            ->getRepository(Vehicule::class)
            ->getVehiculeEquiv($id);

        if ($this->getParameter('app.env') == 'dev')
            sleep(seconds: 1.5);

        return $this->json([
            'vl' => $vl_equiv
        ]);
    }

    #[Route('/validation/valid', name: 'resa_valid', methods: ['POST'])]
    public function valid(ManagerRegistry $doctrine, RequestStack $requestStack)
    {
        $data = (array) json_decode($this->request->getContent());
        $id = $data['id'];
        $em = $doctrine->getManager();

        $reservation = $em->getRepository(Reservation::class)
            ->findOneBy(['id' => $id]);
        $mailer = new MailService($em);
        $mail = $mailer->mailForValidation($reservation);
        if ($this->getParameter('app.env') == 'prod') {
            // Envoi du mail via le SSO
            try {
                SsoService::mail(
                    $mail->getSubject(),
                    $mail->getBody(),
                    $mail->getRecipients(),
                    false
                );
                // change le destinataire du mail pour le valideur
                $mail->setValideursAsRecipient($mail->getValideurType($reservation), "CSAG_EN_COPIE");
                SsoService::mail(
                    "[Copie]: " . $mail->getSubject(),
                    $mail->getBody(),
                    $mail->getRecipients(),
                    false
                );
            } catch (\Throwable $th) {
                //throw $th;
            }
        } else {
            sleep(seconds: 1.5);
        }

        $statut_valide = $em
            ->getRepository(StatutReservation::class)
            ->findOneBy(['code' => 'Confirmée']);

        $reservation = $em->getRepository(Reservation::class)
            ->findOneBy(['id' => $id]);

        $reservation->setStatut($statut_valide);
        $em->persist($reservation);
        $em->flush();

        return $this->json([
            'id' => $reservation->getId(),
            'statut' => $reservation->getStatut()->getCode()
        ]);
    }

    #[Route('/validation/modif', name: 'resa_validation_modif', methods: ['POST'])]
    public function modif(ManagerRegistry $doctrine, RequestStack $requestStack)
    {
        $data = (array) json_decode($this->request->getContent());
        $id = $data['id'];
        $vehicule_id = $data['vl'];
        $em = $doctrine->getManager();

        $reservation = $em->getRepository(Reservation::class)
            ->findOneBy(['id' => $id]);
        $mailer = new MailService($em);
        $mail = $mailer->mailForEchangeVL($reservation);
        if ($this->getParameter('app.env') == 'prod') {
            // Envoi du mail via le SSO
            SsoService::mail(
                $mail->getSubject(),
                $mail->getBody(),
                $mail->getRecipients(),
                false
            );
            // change le destinataire du mail pour le valideur
            $mail->setValideursAsRecipient($mail->getValideurType($reservation), "CSAG_EN_COPIE");
            SsoService::mail(
                "[Copie]: " . $mail->getSubject(),
                $mail->getBody(),
                $mail->getRecipients(),
                false
            );
        } else {
            sleep(seconds: 1.5);
        }

        $statut_valide = $em
            ->getRepository(StatutReservation::class)
            ->findOneBy(['code' => 'Confirmée']);

        $vl = $em
            ->getRepository(Vehicule::class)
            ->findOneBy(['id' => $vehicule_id]);

        $reservation = $em->getRepository(Reservation::class)
            ->findOneBy(['id' => $id]);

        $reservation->setStatut($statut_valide);
        $reservation->setVehicule($vl);

        $em->persist($reservation);
        $em->flush();

        return $this->json([
            'id' => $reservation->getId(),
            'statut' => $reservation->getStatut()->getCode()
        ]);
    }

    #[Route('/validation/suppr', name: 'resa_suppr', methods: ['POST'])]
    public function suppr(ManagerRegistry $doctrine, RequestStack $requestStack)
    {
        $data = (array) json_decode($this->request->getContent());
        $id = $data['id'];
        $em = $doctrine->getManager();

        $statut_annulee = $em
            ->getRepository(StatutReservation::class)
            ->findOneBy(['code' => 'Annulée']);

        $reservation = $em->getRepository(Reservation::class)
            ->findOneBy(['id' => $id]);

        $mailer = new MailService($em);
        $mail = $mailer->mailForInvalidation($reservation);
        if ($this->getParameter('app.env') == 'prod') {
            // Envoi du mail via le SSO
            SsoService::mail(
                $mail->getSubject(),
                $mail->getBody(),
                $mail->getRecipients(),
                false
            );
            // change le destinataire du mail pour le valideur
            $mail->setValideursAsRecipient($mail->getValideurType($reservation));
            SsoService::mail(
                "[Copie]: " . $mail->getSubject(),
                $mail->getBody(),
                $mail->getRecipients(),
                false
            );
        } else {
            sleep(seconds: 1.5);
        }

        $reservation->setStatut($statut_annulee);
        $em->persist($reservation);
        $em->flush();

        return $this->json([
            'id' => $reservation->getId(),
            'statut' => $reservation->getStatut()->getCode()
        ]);
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
                'app.token_gives_full_access',
                'app.unites_em'
            ] as $param
        ) {
            $AppConstName = strToUpper(str_replace('.', '_', $param));
            $this->app_const[$AppConstName] = $this->getParameter($param);
        }
    }

    private function addZeros($str, $maxlen = 2)
    {
        $str = '' . $str;
        while (strlen($str) < $maxlen)
            $str = "0" . $str;
        return $str;
    }
}
