<?php

namespace App\Entity;

use App\Repository\VoitureRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: VoitureRepository::class)]
class Voiture
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $modele = null;

    #[ORM\Column(nullable: true)]
    private ?int $places = null;

    #[ORM\Column(length: 9)]
    private ?string $immatriculation = null;

    #[ORM\ManyToOne(inversedBy: 'voitures')]
    private ?Marque $marque = null;

    #[ORM\ManyToMany(targetEntity: Personne::class, mappedBy: 'voiture')]
    private Collection $personnes;

    public function __construct()
    {
        $this->personnes = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getModele(): ?string
    {
        return $this->modele;
    }

    public function setModele(?string $modele): static
    {
        $this->modele = $modele;

        return $this;
    }

    public function getPlaces(): ?int
    {
        return $this->places;
    }

    public function setPlaces(?int $places): static
    {
        $this->places = $places;

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

    public function getMarque(): ?Marque
    {
        return $this->marque;
    }

    public function setMarque(?Marque $marque): static
    {
        $this->marque = $marque;

        return $this;
    }

    /**
     * @return Collection<int, Personne>
     */
    public function getPersonnes(): Collection
    {
        return $this->personnes;
    }

    public function addPersonne(Personne $personne): static
    {
        if (!$this->personnes->contains($personne)) {
            $this->personnes->add($personne);
            $personne->addVoiture($this);
        }

        return $this;
    }

    public function removePersonne(Personne $personne): static
    {
        if ($this->personnes->removeElement($personne)) {
            $personne->removeVoiture($this);
        }

        return $this;
    }

    public function toJson(): array
    {
        return [
            'id' => $this->getId(),
            'modele' => $this->getModele(),
            'places' => $this->getPlaces(),
            'immatriculation' => $this->getImmatriculation(),
        ];
    }

    public function toJsonAvecMarque(): array
    {
        return [
            'id' => $this->getId(),
            'modele' => $this->getModele(),
            'places' => $this->getPlaces(),
            'immatriculation' => $this->getImmatriculation(),
            'marque' => $this->getMarque() ? $this->getMarque()->toJson() : null,
        ];
    }

    public function toJsonAvecMarqueEtConducteurs(): array
    {
        $conducteursJson = [];
        foreach ($this->getPersonnes() as $personne) {
            $conducteursJson[] = $personne->toJson();
        }

        return [
            'id' => $this->getId(),
            'modele' => $this->getModele(),
            'places' => $this->getPlaces(),
            'immatriculation' => $this->getImmatriculation(),
            'marque' => $this->getMarque() ? $this->getMarque()->toJson() : null,
            'conducteurs' => $this->getConducteursJson(),
        ];
    }

    private function getConducteursJson(): array
    {
        $conducteursJson = [];
        foreach ($this->getPersonnes() as $personne) {
            $conducteursJson[] = $personne->toJson();
        }

        return $conducteursJson;
    }
}
