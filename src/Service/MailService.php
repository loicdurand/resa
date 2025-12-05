<?php

namespace App\Service;

use App\Service\SsoService;
use App\Entity\User;
use App\Entity\Reservation;
use Doctrine\Persistence\ObjectManager;


class MailService
{

  private $manager;
  private $subject;
  private $body;
  private array $recipients;
  private $throwExceptionIfExpired = false;
  private $csag_code_unite = null;

  private const IS_CSAG = "IS_CSAG";

  public function __construct(ObjectManager $manager)
  {
    $this->manager = $manager;
    $this->setCSAGCodeUnite();
    return $this;
  }

  public function mailForReservation(Reservation $reservation)
  {
    $vehicule = $reservation->getVehicule();
    $vehicule_unite = $vehicule->getUnite();
    $code_unite = $vehicule_unite->getCodeUnite();
    if ($this->isCSAG($code_unite)) {
      $this->setRecipients($this::IS_CSAG);
    }


    $this->setSubject("Nouvelle réservation effectuée sur le site Résa971");
    $this->setBody("Une nouvelle réservation a été effectuée.\n\n" .
      "Détails de la réservation :\n" .
      "ID de la réservation : " . $reservation->getId() . "\n" .
      "Véhicule : " . $reservation->getVehicule()->getMarque() . " " . $reservation->getVehicule()->getModele() . "\n" .
      "Date de début : " . $reservation->getDateDebut()->format('d/m/Y H:i') . "\n" .
      "Date de fin : " . $reservation->getDateFin()->format('d/m/Y H:i') . "\n" .
      "Utilisateur : " . $reservation->getUser() . "\n");
    return $this;
  }

  public function getCSAGCodeUnite()
  {
    if (is_null($this->csag_code_unite)) {
      $this->setCSAGCodeUnite();
    }
    return $this->csag_code_unite;
  }

  public function setCSAGCodeUnite()
  {
    $this->csag_code_unite = $_ENV['APP_CSAG_CODE_UNITE'];
  }

  public function getRecipients()
  {
    return $this->recipients;
  }

  public function setRecipients($type)
  {
    $this->recipients = [];
    if ($type === $this::IS_CSAG) {
      $validateurs = $this->manager
        ->getRepository(User::class)
        ->findBy([
          'profil' => 'VDT',
          'unite' => $this->getCSAGCodeUnite()
        ]);
      foreach ($validateurs as $validateur) {
        $mail = $validateur->getMail();
        if (!is_null($mail))
          $this->recipients[] = $mail;
      }
    }
  }

  public function getSubject()
  {
    return $this->subject;
  }

  public function setSubject($subject)
  {
    $this->subject = $subject;
  }

  public function getBody()
  {
    return $this->body;
  }

  public function setBody(string $body)
  {
    $this->body = $body;
  }

  private function isCSAG($code_unite)
  {
    return +$code_unite === +$this->getCSAGCodeUnite();
  }
}
