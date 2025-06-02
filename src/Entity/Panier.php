<?php

namespace App\Entity;

use App\Repository\PanierRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PanierRepository::class)]
class Panier
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?int $quantite = null;
   // Indique qu'il s'agit d'une relation "plusieurs paniers pour un seul utilisateur".
// Chaque panier appartient à UN utilisateur, mais un utilisateur peut avoir PLUSIEURS paniers.
    #[ORM\ManyToOne(inversedBy: 'paniers')]
    // Définit la colonne dans la base de données pour faire la liaison (clé étrangère).
// nullable: false signifie que le panier DOIT avoir un utilisateur, ce n'est pas optionnel.
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    #[ORM\ManyToOne(inversedBy: 'paniers')]
    private ?Produit $produit = null;

    public function getId(): ?int { return $this->id; }

    public function getQuantite(): ?int { return $this->quantite; }

    public function setQuantite(int $quantite): static
    {
        $this->quantite = $quantite;
        return $this;
    }

    public function getUser(): ?User { return $this->user; }

    public function setUser(?User $user): static
    {
        $this->user = $user;
        return $this;
    }

    public function getProduit(): ?Produit { return $this->produit; }

    public function setProduit(?Produit $produit): static
    {
        $this->produit = $produit;
        return $this;
    }
}
