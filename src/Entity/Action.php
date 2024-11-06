<?php

namespace App\Entity;

use App\Repository\ActionRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ActionRepository::class)]
class Action
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 25)]
    private ?string $nom = null;

    #[ORM\Column(length: 255)]
    private ?string $libelle = null;

    /**
     * @var Collection<int, Role>
     */
    #[ORM\OneToMany(targetEntity: Role::class, mappedBy: 'action')]
    private Collection $permission;

    /**
     * @var Collection<int, Permission>
     */
    #[ORM\OneToMany(targetEntity: Permission::class, mappedBy: 'action')]
    private Collection $permissions;

    public function __construct()
    {
        $this->permission = new ArrayCollection();
        $this->permissions = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getLibelle(): ?string
    {
        return $this->libelle;
    }

    public function setLibelle(string $libelle): static
    {
        $this->libelle = $libelle;

        return $this;
    }

    /**
     * @return Collection<int, Role>
     */
    public function getPermission(): Collection
    {
        return $this->permission;
    }

    public function addPermission(Role $permission): static
    {
        if (!$this->permission->contains($permission)) {
            $this->permission->add($permission);
            $permission->setAction($this);
        }

        return $this;
    }

    public function removePermission(Role $permission): static
    {
        if ($this->permission->removeElement($permission)) {
            // set the owning side to null (unless already changed)
            if ($permission->getAction() === $this) {
                $permission->setAction(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Permission>
     */
    public function getPermissions(): Collection
    {
        return $this->permissions;
    }

}
