<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: UserRepository::class)]
class User
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 8)]
    private ?string $nigend = null;

    #[ORM\Column(length: 8)]
    private ?string $unite = null;

    #[ORM\Column(length: 25, nullable: true)]
    private ?string $profil = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNigend(): ?string
    {
        return $this->nigend;
    }

    public function setNigend(string $nigend): static
    {
        $this->nigend = $this->addZeros($nigend, 8);

        return $this;
    }

    public function getUnite(): ?string
    {
        return $this->unite;
    }

    public function setUnite(string $unite): static
    {
        $this->unite = $this->addZeros($unite, 8);

        return $this;
    }

    public function getProfil(): ?string
    {
        return $this->profil;
    }

    public function setProfil(?string $profil): static
    {
        $this->profil = $profil;

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
