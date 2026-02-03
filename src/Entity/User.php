<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
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

    /**
     * @var Collection<int, Token>
     */
    #[ORM\OneToMany(targetEntity: Token::class, mappedBy: 'user', orphanRemoval: true)]
    private Collection $tokens;

    #[ORM\Column(nullable: true)]
    private ?int $departement = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $mail = null;

    #[ORM\Column(nullable: true)]
    private ?bool $banned = null;

    #[ORM\Column(nullable: true)]
    private ?bool $em_uniquement = null;

    public function __construct()
    {
        $this->tokens = new ArrayCollection();
    }

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

    /**
     * @return Collection<int, Token>
     */
    public function getTokens(): Collection
    {
        return $this->tokens;
    }

    public function addToken(Token $token): static
    {
        if (!$this->tokens->contains($token)) {
            $this->tokens->add($token);
            $token->setUser($this);
        }

        return $this;
    }

    public function removeToken(Token $token): static
    {
        if ($this->tokens->removeElement($token)) {
            // set the owning side to null (unless already changed)
            if ($token->getUser() === $this) {
                $token->setUser(null);
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

    public function getMail(): ?string
    {
        return $this->mail;
    }

    public function setMail(?string $mail): static
    {
        $this->mail = $mail;

        return $this;
    }

    public function isBanned(): ?bool
    {
        return $this->banned;
    }

    public function setBanned(?bool $banned): static
    {
        $this->banned = $banned;

        return $this;
    }

    public function isEmUniquement(): ?bool
    {
        return $this->em_uniquement;
    }

    public function setEmUniquement(?bool $em_uniquement): static
    {
        $this->em_uniquement = $em_uniquement;

        return $this;
    }
}
