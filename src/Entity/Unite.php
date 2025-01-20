<?php

namespace App\Entity;

use App\Repository\UniteRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: UniteRepository::class)]
class Unite
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?int $code_unite = null;

    #[ORM\Column(length: 25)]
    private ?string $nom_court = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $nom_long = null;

    /**
     * @var Collection<int, HoraireOuverture>
     */
    #[ORM\OneToMany(targetEntity: HoraireOuverture::class, mappedBy: 'code_unite', orphanRemoval: true)]
    private Collection $horaires_ouverture;

    #[ORM\Column(nullable: true)]
    private ?int $departement = null;

    public function __construct()
    {
        $this->horaires_ouverture = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCodeUnite(): ?int
    {
        return $this->code_unite;
    }

    public function setCodeUnite(int $code_unite): static
    {
        $this->code_unite = $code_unite;

        return $this;
    }

    public function getNomCourt(): ?string
    {
        return $this->nom_court;
    }

    public function setNomCourt(string $nom_court): static
    {
        $this->nom_court = $nom_court;

        return $this;
    }

    public function getNomLong(): ?string
    {
        return $this->nom_long;
    }

    public function setNomLong(?string $nom_long): static
    {
        $this->nom_long = $nom_long;

        return $this;
    }

    /**
     * @return Collection<int, HoraireOuverture>
     */
    public function getHorairesOuverture(): Collection
    {
        return $this->horaires_ouverture;
    }

    public function addHorairesOuverture(HoraireOuverture $horairesOuverture): static
    {
        if (!$this->horaires_ouverture->contains($horairesOuverture)) {
            $this->horaires_ouverture->add($horairesOuverture);
            $horairesOuverture->setCodeUnite($this);
        }

        return $this;
    }

    public function removeHorairesOuverture(HoraireOuverture $horairesOuverture): static
    {
        if ($this->horaires_ouverture->removeElement($horairesOuverture)) {
            // set the owning side to null (unless already changed)
            if ($horairesOuverture->getCodeUnite() === $this) {
                $horairesOuverture->setCodeUnite(null);
            }
        }

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
}
