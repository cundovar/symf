<?php

declare(strict_types=1); // Active le typage strict : cela évite des erreurs de type inattendues

namespace App\Entity;

use App\Repository\ProduitRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

// Cette classe représente une entité Doctrine, c’est-à-dire une table dans la base de données.
// Elle est liée à la table "produit" via le repository ProduitRepository.
#[ORM\Entity(repositoryClass: ProduitRepository::class)]
class Produit
{
    // Identifiant unique du produit, clé primaire dans la base de données.
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    // Nom du produit (VARCHAR 255 en base de données).
    #[ORM\Column(length: 255)]
    private ?string $nom = null;

    // Le produit appartient à une catégorie.
    // C’est une relation ManyToOne (plusieurs produits peuvent avoir la même catégorie).
    #[ORM\ManyToOne(targetEntity: Category::class, inversedBy: 'produits')]
    private ?Category $category = null;
    // pas Collection car  chaque produit est lié à une seule catégorie.

    // Description du produit (VARCHAR 255).
    #[ORM\Column(length: 255)]
    private ?string $description = null;

    // Nom du fichier image associé au produit (peut être null).
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $img = null;

    // Prix du produit (type float).
    #[ORM\Column]
    private ?float $prix = null;

    // Liste des paniers contenant ce produit.
    // Relation OneToMany : un produit peut être dans plusieurs paniers.
    #[ORM\OneToMany(targetEntity: Panier::class, mappedBy: 'produit')]
    private Collection $paniers; // Collection est une interface donc avec des methodes vide
//     Doctrine ne peut pas utiliser des tableaux PHP (array) pour ses relations car ils sont trop limités.

// $paniers est un objet de type Collection (souvent un ArrayCollection)
// $panier est un objet de type Panier, une seule instance

// Avec Collection, on a plein de méthodes pratiques comme :

// add()

// removeElement()

// contains()

// filter()

// map()

// etc.

    // Liste des lignes de commande liées à ce produit.
    // orphanRemoval supprime les lignes orphelines automatiquement.
    // cascade remove supprime les lignes quand le produit est supprimé.
    #[ORM\OneToMany(targetEntity: LigneCommande::class, mappedBy: 'produit', orphanRemoval: true, cascade: ['remove'])]
    private Collection $ligneCommandes;

    // Stock disponible pour ce produit (peut être null).
    #[ORM\Column(nullable: true)]
    private ?int $stock = null;

    // Constructeur : initialise les collections de relations (important pour éviter les erreurs).
    public function __construct()
    {
        $this->paniers = new ArrayCollection();
        $this->ligneCommandes = new ArrayCollection();
    }

    // --- Getters et setters pour accéder et modifier les propriétés ---

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
     * Retourne la liste des paniers contenant ce produit.
     * @return Collection<int, Panier>
     */
    public function getPaniers(): Collection
    {
        return $this->paniers;
    }

    /**
     * Ajoute un panier à la liste si ce n’est pas déjà fait.
     */
    public function addPanier(Panier $panier): static
    {
        if (!$this->paniers->contains($panier)) {
            $this->paniers->add($panier);
            $panier->setProduit($this); // Met à jour l’autre côté de la relation
        }

        return $this;
    }

    /**
     * Supprime un panier de la liste.
     */
    public function removePanier(Panier $panier): static
    {
        if ($this->paniers->removeElement($panier)) {
            if ($panier->getProduit() === $this) {
                $panier->setProduit(null); // Déconnecte l'association
            }
        }

        return $this;
    }

//     $this->paniers est une Collection d’objets Panier.
// removeElement() est une méthode de Doctrine (via ArrayCollection) qui :
// Retire l’objet $panier de la collection s’il est présent.
// Retourne true si ça a fonctionné, sinon false.
// On casse le lien côté inverse, c’est-à-dire dans le Panier.
// On met la propriété produit du panier à null, pour dire :
// « Ce panier ne contient plus aucun produit. »
// 💡 Doctrine a besoin que les deux côtés soient mis à jour dans une relation bidirectionnelle (OneToMany ↔ ManyToOne),
//  sinon la base ne sera pas synchronisée correctement.




    /**
     * Retourne la liste des lignes de commande contenant ce produit.
     * @return Collection<int, LigneCommande>
     */
    public function getLigneCommandes(): Collection
    {
        return $this->ligneCommandes;
    }

    /**
     * Ajoute une ligne de commande à la liste.
     */
    public function addLigneCommande(LigneCommande $ligneCommande): static
    {
        if (!$this->ligneCommandes->contains($ligneCommande)) {
            $this->ligneCommandes->add($ligneCommande);
            $ligneCommande->setProduit($this);
        }

        return $this;
    }

    /**
     * Supprime une ligne de commande de la liste.
     */
    // public function addLigneCommande(LigneCommande $ligneCommande): static
// C’est une méthode publique (accessible depuis l’extérieur de la classe).

// Elle prend un objet LigneCommande en paramètre.

// Elle retourne static : cela signifie qu’elle retourne l’objet courant ($this), ce qui permet de chaîner les appels (ex : $produit->addLigneCommande(...)->setPrix(10.5);).

// if (!$this->ligneCommandes->contains($ligneCommande)) {
// $this->ligneCommandes est une collection d’objets LigneCommande (instanciée dans le constructeur avec ArrayCollection).

// On vérifie si cette ligne de commande n’est pas déjà dans la collection.

// Cela évite d’ajouter deux fois le même objet.

// $this->ligneCommandes->add($ligneCommande);
// On ajoute la ligne de commande à la collection du produit.

// Cela met à jour la relation côté Produit (le côté "One").

// $ligneCommande->setProduit($this);
// Ici, on met à jour la relation inverse, c’est-à-dire le côté "Many" de la relation.

// Chaque LigneCommande contient une propriété produit, donc on lui dit : « Ton produit, c’est moi ($this) ».

// Cela garantit que Doctrine connaîtra les deux côtés de la relation.

// 👉 En Doctrine, il est important de gérer les deux côtés d'une relation bidirectionnelle, sinon les changements ne seront pas correctement persistés.

// return $this;
// On retourne l’instance courante (Produit), pour permettre le chaînage fluide (fluent interface).
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


// Méthodes courantes de ArrayCollection
// Voici les méthodes les plus utiles que tu retrouveras dans les projets Symfony :


// add($element)	Ajoute un élément dans la collection
// removeElement($element)	Supprime un élément s’il est présent
// contains($element)	Vérifie si l’élément est dans la collection
// isEmpty()	Vérifie si la collection est vide
// toArray()	Retourne un tableau PHP classique
// filter(Closure $p)	Retourne une sous-collection selon une condition
// map(Closure $f)	Applique une fonction à chaque élément
// first() / last()	Retourne le premier ou dernier élément
