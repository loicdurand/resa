<?php

namespace App\Entity;

use App\Repository\HoraireOuvertureRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: HoraireOuvertureRepository::class)]
class HoraireOuverture
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'horaires_ouverture')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Atelier $code_unite = null;

    #[ORM\Column(length: 2)]
    private ?string $jour = null;

    #[ORM\Column(length: 5)]
    private ?string $creneau = null;

    #[ORM\Column(length: 5)]
    private ?string $debut = null;

    #[ORM\Column(length: 5)]
    private ?string $fin = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCodeUnite(): ?Atelier
    {
        return $this->code_unite;
    }

    public function setCodeUnite(?Atelier $code_unite): static
    {
        $this->code_unite = $code_unite;

        return $this;
    }

    public function getJour(): ?string
    {
        return $this->jour;
    }

    public function setJour(string $jour): static
    {
        $this->jour = $jour;

        return $this;
    }

    public function getCreneau(): ?string
    {
        return $this->creneau;
    }

    public function setCreneau(string $creneau): static
    {
        $this->creneau = $creneau;

        return $this;
    }

    public function getDebut(): ?string
    {
        return $this->debut;
    }

    public function setDebut(string $debut): static
    {
        $this->debut = $debut;

        return $this;
    }

    public function getFin(): ?string
    {
        return $this->fin;
    }

    public function setFin(string $fin): static
    {
        $this->fin = $fin;

        return $this;
    }
}
