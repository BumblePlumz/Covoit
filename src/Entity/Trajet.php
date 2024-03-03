<?php

namespace App\Entity;

use App\Repository\TrajetRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TrajetRepository::class)]
class Trajet
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?int $kms = null;

    #[ORM\Column(type: Types::TIME_MUTABLE)]
    private ?\DateTimeInterface $heureDepart = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTimeInterface $dateDepart = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $statut = null;

    #[ORM\ManyToOne(inversedBy: 'trajets')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Ville $villeDepart = null;

    #[ORM\ManyToOne(inversedBy: 'trajets')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Ville $villeArriver = null;

    #[ORM\ManyToMany(targetEntity: Personne::class, inversedBy: 'trajetsPassager')]
    private Collection $passagers;

    #[ORM\ManyToOne(inversedBy: 'trajetsConducteur')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Personne $conducteur = null;

    public function __construct()
    {
        $this->passagers = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getKms(): ?int
    {
        return $this->kms;
    }

    public function setKms(int $kms): static
    {
        $this->kms = $kms;

        return $this;
    }

    public function getHeureDepart(): ?\DateTimeInterface
    {
        return $this->heureDepart;
    }

    public function setHeureDepart(\DateTimeInterface $heureDepart): static
    {
        $this->heureDepart = $heureDepart;

        return $this;
    }

    public function getDateDepart(): ?\DateTimeInterface
    {
        return $this->dateDepart;
    }

    public function setDateDepart(\DateTimeInterface $dateDepart): static
    {
        $this->dateDepart = $dateDepart;

        return $this;
    }

    public function getStatut(): ?string
    {
        return $this->statut;
    }

    public function setStatut(?string $statut): static
    {
        $this->statut = $statut;

        return $this;
    }

    public function getVilleDepart(): ?Ville
    {
        return $this->villeDepart;
    }

    public function setVilleDepart(?Ville $villeDepart): static
    {
        $this->villeDepart = $villeDepart;

        return $this;
    }

    public function getVilleArriver(): ?Ville
    {
        return $this->villeArriver;
    }

    public function setVilleArriver(?Ville $villeArriver): static
    {
        $this->villeArriver = $villeArriver;

        return $this;
    }

    public function getConducteur(): ?Personne
    {
        return $this->conducteur;
    }

    public function setConducteur(?Personne $conducteur): static
    {
        $this->conducteur = $conducteur;

        return $this;
    }

    /**
     * @return Collection<int, Personne>
     */
    public function getPassagers(): Collection
    {
        return $this->passagers;
    }

    public function addPassager(Personne $passager): static
    {
        if (!$this->passagers->contains($passager)) {
            $this->passagers->add($passager);
        }

        return $this;
    }

    public function removePassager(Personne $passager): static
    {
        $this->passagers->removeElement($passager);

        return $this;
    }

    public function isPlacesDisponible(): bool
    {
        $voiture = $this->getConducteur()->getVoiture()->first();
        $places = $voiture->getPlaces() - 1;
        return ($places - count($this->getPassagers()) ) > 0;
    }

    public function toJson(): array
    {
        return [
            'id' => $this->getId(),
            'kms' => $this->getKms(),
            'heureDepart' => $this->getHeureDepart()->format('H:i:s'),
            'dateDepart' => $this->getDateDepart()->format('Y-m-d'),
            'statut' => $this->getStatut(),
            'villeDepart' => $this->getVilleDepart()->toJson(),
            'villeArriver' => $this->getVilleArriver()->toJson(),
            'conducteur' => $this->getConducteur()->toJson(),
            'passagers' => $this->getPassagersJson(),
        ];
    }

    public function toJsonSansConducteur(): array
    {
        return [
            'id' => $this->getId(),
            'kms' => $this->getKms(),
            'heureDepart' => $this->getHeureDepart()->format('H:i:s'),
            'dateDepart' => $this->getDateDepart()->format('Y-m-d'),
            'statut' => $this->getStatut(),
            'villeDepart' => $this->getVilleDepart()->toJson(),
            'villeArriver' => $this->getVilleArriver()->toJson(),
            'passagers' => $this->getPassagersJson(),
        ];
    }

    public function toJsonAvecVoitures(): array
    {
        return [
            'id' => $this->getId(),
            'kms' => $this->getKms(),
            'heureDepart' => $this->getHeureDepart()->format('H:i:s'),
            'dateDepart' => $this->getDateDepart()->format('Y-m-d'),
            'statut' => $this->getStatut(),
            'villeDepart' => $this->getVilleDepart()->toJson(),
            'villeArriver' => $this->getVilleArriver()->toJson(),
            'conducteur' => $this->getConducteur()->toJsonAvecVoitures(),
            'passagers' => $this->getPassagersJson(),
        ];
    }

    private function getPassagersJson(): array
    {
        $passagersJson = [];
        foreach ($this->getPassagers() as $passager) {
            $passagersJson[] = $passager->toJson();
        }
        return $passagersJson;
    }

    private function getConducteurJson(): array
    {
        return array_map(function($conducteur) {
            return $conducteur->toJson();
        }, [$this->getConducteur()]);
    }
}
