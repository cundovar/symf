<?php

namespace App\Entity;

use App\Repository\ProduitRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

// Cette classe est une entité Doctrine et est liée à la table produit.
// Elle utilise le repository ProduitRepository pour accéder aux données.
#[ORM\Entity(repositoryClass: ProduitRepository::class)]
class Produit
{
    // Clé primaire auto-incrémentée
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    // Champ nom de type string (VARCHAR 255)
    #[ORM\Column(length: 255)]
    private ?string $nom = null;

    // Relation ManyToOne (chaque produit appartient à une seule catégorie)
    // 'inversedBy' indique la propriété dans Category qui contient la collection de produits
    #[ORM\ManyToOne(targetEntity: Category::class, inversedBy: 'produits')]
    private ?Category $category = null;

    // Description du produit
    #[ORM\Column(length: 255)]
    private ?string $description = null;

    // Nom du fichier image (stocké sous forme de texte)
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $img = null;

    // Prix du produit
    #[ORM\Column]
    private ?float $prix = null;

    // Relation OneToMany avec Panier : un produit peut être dans plusieurs paniers
    #[ORM\OneToMany(targetEntity: Panier::class, mappedBy: 'produit')]
    private Collection $paniers;

    // Relation OneToMany avec LigneCommande : un produit peut apparaître dans plusieurs lignes de commande
    #[ORM\OneToMany(targetEntity: LigneCommande::class, mappedBy: 'produit', orphanRemoval: true, cascade: ['remove'])]
    private Collection $ligneCommandes;

    #[ORM\Column(nullable: true)]
    private ?int $stock = null;

    public function __construct()
    {
        $this->paniers = new ArrayCollection();
        $this->ligneCommandes = new ArrayCollection();
    }

    // Getters & setters
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

    public function getCategory(): ?Category
    {
        return $this->category;
    }

    public function setCategory(?Category $category): static
    {
        $this->category = $category;
        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;
        return $this;
    }

    public function getImg(): ?string
    {
        return $this->img;
    }

    public function setImg(?string $img): static
    {
        $this->img = $img;
        return $this;
    }

    public function getPrix(): ?float
    {
        return $this->prix;
    }

    public function setPrix(float $prix): static
    {
        $this->prix = $prix;
        return $this;
    }

    /**
     * @return Collection<int, Panier>
     */
    public function getPaniers(): Collection
    {
        return $this->paniers;
    }

    public function addPanier(Panier $panier): static
    {
        if (!$this->paniers->contains($panier)) {
            $this->paniers->add($panier);
            $panier->setProduit($this);
        }

        return $this;
    }

    public function removePanier(Panier $panier): static
    {
        if ($this->paniers->removeElement($panier)) {
            if ($panier->getProduit() === $this) {
                $panier->setProduit(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, LigneCommande>
     */
    public function getLigneCommandes(): Collection
    {
        return $this->ligneCommandes;
    }

    public function addLigneCommande(LigneCommande $ligneCommande): static
    {
        if (!$this->ligneCommandes->contains($ligneCommande)) {
            $this->ligneCommandes->add($ligneCommande);
            $ligneCommande->setProduit($this);
        }

        return $this;
    }

    public function removeLigneCommande(LigneCommande $ligneCommande): static
    {
        if ($this->ligneCommandes->removeElement($ligneCommande)) {
            if ($ligneCommande->getProduit() === $this) {
                $ligneCommande->setProduit(null);
            }
        }

        return $this;
    }

    public function getStock(): ?int
    {
        return $this->stock;
    }

    public function setStock(?int $stock): static
    {
        $this->stock = $stock;

        return $this;
    }
}
