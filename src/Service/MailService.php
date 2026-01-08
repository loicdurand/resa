<?php

namespace App\Service;

use App\Service\SsoService;
use App\Service\LdapService;
use App\Entity\User;
use App\Entity\Reservation;
use Doctrine\Persistence\ObjectManager;


class MailService
{

  private $manager;
  private $subject;
  private $body;
  private array $recipients;

  private const IS_CSAG = "IS_CSAG";
  private const IS_JUD = "IS_JUD";
  private const IS_EM = "IS_EM";

  public function __construct(ObjectManager $manager)
  {
    $this->manager = $manager;
    return $this;
  }

  public function mailForReservation(Reservation $reservation)
  {
    $vehicule = $reservation->getVehicule();
    $type_demande = $reservation->getTypeDemande();

    $recipient = "";
    if ($vehicule->getRestriction()->getCode() === 'EM') {
      $recipient = $this::IS_EM;
    } else if ($vehicule->getRestriction()->getCode() === 'NON_OPE') {
      $recipient = $this::IS_CSAG;
    } else {
      $recipient = $type_demande->getCode() === 'ope' ?
        $this::IS_JUD :
        $this::IS_CSAG;
    }

    $this
      ->setRecipients($recipient)
      ->setSubject("Nouvelle demande de réservation effectuée sur le site Résa971")
      ->setBody("Une nouvelle demande de réservation a été effectuée.\n\n" .
        "DÉTAILS DE LA DEMANDE\n" .
        ($reservation->getId() ? "ID de la réservation : " . $reservation->getId() . "\n" : "") .
        "Véhicule : " . $reservation->getVehicule()->getMarque() . " " . $reservation->getVehicule()->getModele() . " " . $reservation->getVehicule()->getImmatriculation() . "\n" .
        "Date de début : " . $reservation->getDateDebut()->format('d/m/Y H:i') . "\n" .
        "Date de fin : " . $reservation->getDateFin()->format('d/m/Y H:i') . "\n" .
        "Utilisateur : " . $reservation->getUser() . "\n");
    return $this;
  }

  public function getRecipients()
  {
    return $this->recipients;
  }

  public function setRecipients($type)
  {
    $user_repo = $this->manager
      ->getRepository(User::class);
    $validateurs = [];
    $this->recipients = [];

    if ($type === $this::IS_EM) {
      $env_unites_em = $_ENV['APP_UNITES_EM'] ?? '';
      $raw_unites_em = explode(',', $env_unites_em);
      $unites_em = [];
      foreach ($raw_unites_em as $code_unite) {
        $unites_em[] = $this->addZeros($code_unite, 8);
      }
      $validateurs = $user_repo->findBy([
        'profil' => 'VDT',
        'unite' => $unites_em
      ]);
    } else {
      if ($type == $this::IS_JUD) {
        $env_unites_pj = $_ENV['APP_UNITES_PJ'] ?? '';
        $raw_unites_pj = explode(',', $env_unites_pj);
        $unites_pj = [];
        foreach ($raw_unites_pj as $code_unite) {
          $unites_pj[] = $this->addZeros($code_unite, 8);
        }

        $validateurs = $user_repo->findBy([
          'profil' => 'VDT',
          'unite' => $unites_pj
        ]);
      } else {
        $validateurs = $user_repo->findBy([
          'profil' => ['VDT', 'CSAG'],
          'unite' => $this->addZeros($_ENV['APP_CSAG_CODE_UNITE'], 8)
        ]);
      }
    }

    foreach ($validateurs as $validateur) {
      $mail = $validateur->getMail();
      if (!is_null($mail))
        $this->recipients[] = $mail;
    }

    return $this;
  }

  public function getSubject()
  {
    return $this->subject;
  }

  public function setSubject($subject)
  {
    $this->subject = $subject;
    return $this;
  }

  public function getBody()
  {
    return $this->body;
  }

  public function setBody(string $body)
  {
    $this->body = $body;
    return $this;
  }

  private function addZeros($str, $maxlen = 2)
  {
    $str = '' . $str;
    while (strlen($str) < $maxlen)
      $str = "0" . $str;
    return $str;
  }
}
