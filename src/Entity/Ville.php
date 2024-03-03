<?php

namespace App\Entity;

use App\Repository\VilleRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: VilleRepository::class)]
class Ville
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    private ?string $nom = null;

    #[ORM\Column(length: 5)]
    private ?string $codePostal = null;

    #[ORM\OneToMany(targetEntity: Trajet::class, mappedBy: 'villeDepart')]
    private Collection $trajetsParVilleDepart;

    #[ORM\OneToMany(targetEntity: Trajet::class, mappedBy: 'villeArriver')]
    private Collection $trajetsParVilleArriver;

    public function __construct()
    {
        $this->trajetsParVilleDepart = new ArrayCollection();
        $this->trajetsParVilleArriver = new ArrayCollection();
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

    public function getCodePostal(): ?string
    {
        return $this->codePostal;
    }

    public function setCodePostal(string $codePostal): static
    {
        $this->codePostal = $codePostal;

        return $this;
    }

    /**
     * @return Collection<int, Trajet>
     */
    public function getTrajetsParVilleDepart(): Collection
    {
        return $this->trajetsParVilleDepart;
    }

    public function addTrajet(Trajet $trajet): static
    {
        if (!$this->trajetsParVilleDepart->contains($trajet)) {
            $this->trajetsParVilleDepart->add($trajet);
            $trajet->setVilleDepart($this);
        }

        return $this;
    }

    public function removeTrajet(Trajet $trajet): static
    {
        if ($this->trajetsParVilleDepart->removeElement($trajet)) {
            // set the owning side to null (unless already changed)
            if ($trajet->getVilleDepart() === $this) {
                $trajet->setVilleDepart(null);
            }
        }

        return $this;
    }

        /**
     * @return Collection<int, Trajet>
     */
    public function getTrajetsParVilleArriver(): Collection
    {
        return $this->trajetsParVilleArriver;
    }

    public function addTrajetParVilleArriver(Trajet $trajet): static
    {
        if (!$this->trajetsParVilleArriver->contains($trajet)) {
            $this->trajetsParVilleArriver->add($trajet);
            $trajet->setVilleArriver($this);
        }

        return $this;
    }

    public function removeTrajetParVilleArriver(Trajet $trajet): static
    {
        if ($this->trajetsParVilleArriver->removeElement($trajet)) {
            // set the owning side to null (unless already changed)
            if ($trajet->getVilleArriver() === $this) {
                $trajet->setVilleArriver(null);
            }
        }

        return $this;
    }

    public function toJson(): array
    {
        return [
            'id' => $this->getId(),
            'nom' => $this->getNom(),
            'codePostal' => $this->getCodePostal(),
        ];
    }
    public function toJsonAvecTrajets(): array
    {
        return [
            'id' => $this->getId(),
            'nom' => $this->getNom(),
            'codePostal' => $this->getCodePostal(),
            'trajetsParVilleDepart' => $this->getTrajetsJson($this->getTrajetsParVilleDepart()),
            'trajetsParVilleArriver' => $this->getTrajetsJson($this->getTrajetsParVilleArriver()),
        ];
    }

    private function getTrajetsJson(Collection $trajets): array
    {
        $trajetsJson = [];
        foreach ($trajets as $trajet) {
            $trajetsJson[] = $trajet->toJson();
        }
        return $trajetsJson;
    }
    
}
