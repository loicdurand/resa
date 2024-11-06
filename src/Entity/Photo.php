<?php

namespace App\Entity;

use App\Repository\PhotoRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PhotoRepository::class)]
class Photo
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'photos')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Vehicule $vehicule_id = null;

    #[ORM\Column(length: 25, nullable: true)]
    private ?string $position = null;

    #[ORM\Column(length: 25)]
    private ?string $nom = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getVehiculeId(): ?Vehicule
    {
        return $this->vehicule_id;
    }

    public function setVehiculeId(?Vehicule $vehicule_id): static
    {
        $this->vehicule_id = $vehicule_id;

        return $this;
    }

    public function getPosition(): ?string
    {
        return $this->position;
    }

    public function setPosition(?string $position): static
    {
        $this->position = $position;

        return $this;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): static
    {
        $this->nom = $nom;

        return $this;
    }
}
