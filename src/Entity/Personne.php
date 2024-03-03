<?php

namespace App\Entity;

use App\Repository\PersonneRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PersonneRepository::class)]
class Personne
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    private ?string $nom = null;

    #[ORM\Column(length: 100)]
    private ?string $prenom = null;

    #[ORM\Column(length: 12)]
    private ?string $tel = null;

    #[ORM\Column(length: 255)]
    private ?string $email = null;

    #[ORM\OneToOne(inversedBy: 'personne', cascade: ['persist', 'remove'], fetch: 'EAGER')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Utilisateur $utilisateur = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Ville $ville = null;

    #[ORM\ManyToMany(targetEntity: Trajet::class, mappedBy: 'passagers', fetch: 'EAGER')]
    private Collection $trajetsPassager;

    #[ORM\ManyToMany(targetEntity: Voiture::class, inversedBy: 'personnes', cascade: ['persist', 'remove'], fetch: 'EAGER')]
    private Collection $voiture;

    #[ORM\OneToMany(targetEntity: Trajet::class, mappedBy: 'conducteur', orphanRemoval: true, fetch: 'EAGER')]
    private Collection $trajetsConducteur;

    public function __construct()
    {
        $this->trajetsPassager = new ArrayCollection();
        $this->voiture = new ArrayCollection();
        $this->trajetsConducteur = new ArrayCollection();
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

    public function getPrenom(): ?string
    {
        return $this->prenom;
    }

    public function setPrenom(string $prenom): static
    {
        $this->prenom = $prenom;

        return $this;
    }

    public function getTel(): ?string
    {
        return $this->tel;
    }

    public function setTel(string $tel): static
    {
        $this->tel = $tel;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    public function getUtilisateur(): ?Utilisateur
    {
        return $this->utilisateur;
    }

    public function setUtilisateur(Utilisateur $Utilisateur): static
    {
        $this->utilisateur = $Utilisateur;

        return $this;
    }

    public function getVille(): ?Ville
    {
        return $this->ville;
    }

    public function setVille(?Ville $ville): static
    {
        $this->ville = $ville;

        return $this;
    }

    /**
     * @return Collection<int, Trajet>
     */
    public function getTrajetsPassager(): Collection
    {
        return $this->trajetsPassager;
    }

    public function addTrajetsPassager(Trajet $trajetsPassager): static
    {
        if (!$this->trajetsPassager->contains($trajetsPassager)) {
            $this->trajetsPassager->add($trajetsPassager);
            $trajetsPassager->addPassager($this);
        }

        return $this;
    }

    public function removeTrajetsPassager(Trajet $trajetsPassager): static
    {
        if ($this->trajetsPassager->removeElement($trajetsPassager)) {
            $trajetsPassager->removePassager($this);
        }

        return $this;
    }

    /**
     * @return Collection<int, Voiture>
     */
    public function getVoiture(): Collection
    {
        return $this->voiture;
    }

    public function addVoiture(Voiture $voiture): static
    {
        if (!$this->voiture->contains($voiture)) {
            $this->voiture->add($voiture);
        }

        return $this;
    }

    public function removeVoiture(Voiture $voiture): static
    {
        $this->voiture->removeElement($voiture);

        return $this;
    }

    public function getTrajetsConducteur(): Collection
    {
        return $this->trajetsConducteur;
    }

    public function addTrajetsConducteur(Trajet $trajetsConducteur): static
    {
        if (!$this->trajetsConducteur->contains($trajetsConducteur)) {
            $this->trajetsConducteur->add($trajetsConducteur);
            $trajetsConducteur->setConducteur($this);
        }

        return $this;
    }

    public function removeTrajetsConducteur(Trajet $trajetsConducteur): static
    {
        if ($this->trajetsConducteur->removeElement($trajetsConducteur)) {
            // set the owning side to null (unless already changed)
            if ($trajetsConducteur->getConducteur() === $this) {
                $trajetsConducteur->setConducteur(null);
            }
        }

        return $this;
    }

    public function getTrajets(): array
    {
        $trajetsConducteur = $this->getTrajetsConducteur()->toArray();
        $trajetsPassager = $this->getTrajetsPassager()->toArray();
        return array_merge($trajetsConducteur, $trajetsPassager);
    }

    public function toJson(): array
    {
        return [
            'id' => $this->getId(),
            'nom' => $this->getNom(),
            'prenom' => $this->getPrenom(),
            'tel' => $this->getTel(),
            'email' => $this->getEmail(),
            'ville' => $this->getVille() ? $this->getVille()->toJson() : null,
            'utilisateur' => $this->getUtilisateur() ? $this->getUtilisateur()->toJson() : null,
        ];
    }

    public function toJsonAvecVoitures(): array
    {
        return [
            'id' => $this->getId(),
            'nom' => $this->getNom(),
            'prenom' => $this->getPrenom(),
            'tel' => $this->getTel(),
            'email' => $this->getEmail(),
            'ville' => $this->getVille() ? $this->getVille()->toJson() : null,
            'utilisateur' => $this->getUtilisateur() ? $this->getUtilisateur()->toJson() : null,
            'voiture' => $this->getVoitureJsonAvecMarque(),
        ];
    }

    public function toJsonAvecTrajets(): array
    {
        return [
            'id' => $this->getId(),
            'nom' => $this->getNom(),
            'prenom' => $this->getPrenom(),
            'tel' => $this->getTel(),
            'email' => $this->getEmail(),
            'ville' => $this->getVille() ? $this->getVille()->toJson() : null,
            'voiture' => $this->getVoitureJsonAvecMarque(),
            'utilisateur' => $this->getUtilisateur() ? $this->getUtilisateur()->toJson() : null,
            'trajetsConducteur' => $this->getTrajetsConducteur() ? $this->getTrajetsConducteur() : null,
            'trajetsPassager' => $this->getTrajetsPassagersJson(),
        ];
    }

    public function toJsonAvecDependances(): array
    {
        return [
            'id' => $this->getId(),
            'nom' => $this->getNom(),
            'prenom' => $this->getPrenom(),
            'tel' => $this->getTel(),
            'email' => $this->getEmail(),
            'ville' => $this->getVille() ? $this->getVille()->toJson() : null,
            'utilisateur' => $this->getUtilisateur() ? $this->getUtilisateur()->toJson() : null,
            'voiture' => $this->getVoitureJsonAvecMarque(),
            'trajetsConducteur' => $this->getTrajetsConducteurJson(),
            'trajetsPassager' => $this->getTrajetsPassagersJson(),
        ];
    }

    private function getVoitureJsonAvecMarque(): array
    {
        return array_map(function($voiture) {
            return $voiture->toJsonAvecMarque();
        }, $this->getVoiture()->toArray());
    }

    private function getTrajetsPassagersJson(): array
    {
        return array_map(function($trajet) {
            return $trajet->toJsonAvecVoitures();
        }, $this->getTrajetsPassager()->toArray());
    }

    private function getTrajetsConducteurJson(): array
    {
        return array_map(function($trajet) {
            return $trajet->toJsonSansConducteur();
        }, $this->getTrajetsConducteur()->toArray());
    }
}
