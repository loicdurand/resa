<?php

namespace App\Entity;

use App\Repository\VehiculeRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: VehiculeRepository::class)]
class Vehicule
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $marque = null;

    #[ORM\Column(length: 255)]
    private ?string $modele = null;

    #[ORM\Column(length: 25, nullable: true)]
    private ?string $motorisation = null;

    #[ORM\Column(length: 25, nullable: true)]
    private ?string $finition = null;


    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $controle_technique = null;

    #[ORM\Column(nullable: true)]
    private ?int $nb_places = null;

    #[ORM\Column(length: 9)]
    private ?string $immatriculation = null;

    #[ORM\Column]
    private ?bool $serigraphie = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?GenreVehicule $genre = null;

    #[ORM\ManyToOne]
    private ?CategorieVehicule $categorie = null;

    #[ORM\ManyToOne]
    private ?CarburantVehicule $carburant = null;

    #[ORM\ManyToOne]
    private ?TransmissionVehicule $transmission = null;

    /**
     * @var Collection<int, Reservation>
     */
    #[ORM\OneToMany(targetEntity: Reservation::class, mappedBy: 'vehicule', orphanRemoval: true, cascade: ["persist"])]
    private Collection $reservations;

    /**
     * @var Collection<int, Photo>
     */
    #[ORM\OneToMany(targetEntity: Photo::class, mappedBy: 'vehicule', orphanRemoval: true)]
    private Collection $photos;

    #[ORM\Column(length: 8, nullable: false)]
    private ?string $couleur_vignette = null;

    #[ORM\Column(length: 1024, nullable: true)]
    private ?string $observation = null;

    #[ORM\Column(nullable: true)]
    private ?int $departement = null;

    #[ORM\ManyToOne(inversedBy: 'vehicules')]
    private ?Unite $unite = null;

    public function __construct()
    {
        $this->reservations = new ArrayCollection();
        $this->photos = new ArrayCollection();
        $this->couleur_vignette = $this->rand_dark_color();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getMarque(): ?string
    {
        return $this->marque;
    }

    public function setMarque(string $marque): static
    {
        $this->marque = strtoupper($marque);

        return $this;
    }

    public function getModele(): ?string
    {
        return $this->modele;
    }

    public function setModele(string $modele): static
    {
        $this->modele = $modele;

        return $this;
    }

    public function getMotorisation(): ?string
    {
        return $this->motorisation;
    }

    public function setMotorisation(?string $motorisation): static
    {
        $this->motorisation = $motorisation;

        return $this;
    }

    public function getFinition(): ?string
    {
        return $this->finition;
    }

    public function setFinition(?string $finition): static
    {
        $this->finition = $finition;

        return $this;
    }

    public function getControleTechnique(): ?\DateTimeInterface
    {
        return $this->controle_technique;
    }

    public function setControleTechnique(?\DateTimeInterface $controle_technique): static
    {
        $this->controle_technique = $controle_technique;

        return $this;
    }

    public function getNbPlaces(): ?int
    {
        return $this->nb_places;
    }

    public function setNbPlaces(?int $nb_places): static
    {
        $this->nb_places = $nb_places;

        return $this;
    }

    public function getImmatriculation(): ?string
    {
        return $this->immatriculation;
    }

    public function setImmatriculation(string $immatriculation): static
    {
        $this->immatriculation = $immatriculation;

        return $this;
    }

    public function isSerigraphie(): ?bool
    {
        return $this->serigraphie;
    }

    public function setSerigraphie(bool $serigraphie): static
    {
        $this->serigraphie = $serigraphie;

        return $this;
    }

    public function getGenre(): ?GenreVehicule
    {
        return $this->genre;
    }

    public function setGenre(?GenreVehicule $genre): static
    {
        $this->genre = $genre;

        return $this;
    }

    public function getCategorie(): ?CategorieVehicule
    {
        return $this->categorie;
    }

    public function setCategorie(?CategorieVehicule $categorie): static
    {
        $this->categorie = $categorie;

        return $this;
    }

    public function getCarburant(): ?CarburantVehicule
    {
        return $this->carburant;
    }

    public function setCarburant(?CarburantVehicule $carburant): static
    {
        $this->carburant = $carburant;

        return $this;
    }

    public function getTransmission(): ?TransmissionVehicule
    {
        return $this->transmission;
    }

    public function setTransmission(?TransmissionVehicule $transmission): static
    {
        $this->transmission = $transmission;

        return $this;
    }

    /**
     * @return Collection<int, Reservation>
     */
    public function getReservations(): Collection
    {
        foreach ($this->reservations as $resa) {
            $statut = $resa->getStatut();
            if ($statut->getCode() == 'Annulée')
                $this->removeReservation($resa);
        }
        return $this->reservations;
    }

    public function addReservation(Reservation $reservation): static
    {
        if (!$this->reservations->contains($reservation)) {
            $this->reservations->add($reservation);
            $reservation->setVehicule($this);
        }

        return $this;
    }

    public function removeReservation(Reservation $reservation): static
    {
        if ($this->reservations->removeElement($reservation)) {
            // set the owning side to null (unless already changed)
            if ($reservation->getVehicule() === $this) {
                $reservation->setVehicule(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Photo>
     */
    public function getPhotos(): Collection
    {
        return $this->photos;
    }

    public function addPhoto(Photo $photo): static
    {
        if (!$this->photos->contains($photo)) {
            $this->photos->add($photo);
            $photo->setVehicule($this);
        }

        return $this;
    }

    public function removePhoto(Photo $photo): static
    {
        if ($this->photos->removeElement($photo)) {
            // set the owning side to null (unless already changed)
            if ($photo->getVehicule() === $this) {
                $photo->setVehicule(null);
            }
        }

        return $this;
    }

    public function getCouleurVignette(): ?string
    {
        return $this->couleur_vignette;
    }

    public function setCouleurVignette(?string $couleur_vignette): static
    {
        if (is_null($couleur_vignette) || $couleur_vignette == '')
            $couleur_vignette = $this->rand_dark_color();
        $this->couleur_vignette = $couleur_vignette;

        return $this;
    }

    private function rand_color()
    {
        return sprintf('#%06X', mt_rand(0, 0xFFFFFF));
    }

    private function rand_dark_color()
    {
        $colour = $this->rand_color();
        [$r, $g, $b] = $this->HTMLToRGB($colour);
        if ($this->lightness($r, $g, $b) >= .5)
            return $this->rand_dark_color();
        else
            return $colour;
    }

    private function lightness($R = 255, $G = 255, $B = 255)
    {
        return (max($R, $G, $B) + min($R, $G, $B)) / 510.0; // HSL algorithm
    }

    private function HTMLToRGB($htmlCode)
    {
        if ($htmlCode[0] == '#')
            $htmlCode = substr($htmlCode, 1);

        if (strlen($htmlCode) == 3) {
            $htmlCode = $htmlCode[0] . $htmlCode[0] . $htmlCode[1] . $htmlCode[1] . $htmlCode[2] . $htmlCode[2];
        }

        $r = hexdec($htmlCode[0] . $htmlCode[1]);
        $g = hexdec($htmlCode[2] . $htmlCode[3]);
        $b = hexdec($htmlCode[4] . $htmlCode[5]);

        return [$r, $g, $b];
    }

    public function getObservation(): ?string
    {
        return $this->observation;
    }

    public function setObservation(?string $observation): static
    {
        $this->observation = $observation;

        return $this;
    }

    public function getDepartement(): ?int
    {
        return $this->departement;
    }

    public function setDepartement(?int $departement): static
    {
        $this->departement = $departement;

        return $this;
    }

    public function getUnite(): ?Unite
    {
        return $this->unite;
    }

    public function setUnite(?Unite $unite): static
    {
        $this->unite = $unite;

        return $this;
    }
}
