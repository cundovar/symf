<?php

namespace App\Entity;

// Une commande (Commande) peut contenir plusieurs produits, et chaque produit peut être commandé dans plusieurs commandes. Cela s'appelle une relation many-to-many, mais ici elle est enrichie (c’est-à-dire qu’on doit stocker plus que les seules références entre les entités).

// ➡️ Dans ce cas, on introduit une entité associative appelée LigneCommande pour représenter :

// Le produit commandé (Produit)

// La quantité

// Le prix unitaire au moment de la commande

// Une remise éventuelle

// Une référence vers la commande


use App\Repository\CommandeRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CommandeRepository::class)]
class Commande
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'commandes')]
    private ?User $user = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(length: 255)]
    private ?string $statut = null;

    /**
     * @var Collection<int, LigneCommande>
     */
    #[ORM\OneToMany(targetEntity: LigneCommande::class, mappedBy: 'commande')]
    private Collection $ligneCommandes;

    public function __construct()
    {
        $this->ligneCommandes = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getStatut(): ?string
    {
        return $this->statut;
    }

    public function setStatut(string $statut): static
    {
        $this->statut = $statut;

        return $this;
    }

    /**
     * @return Collection<int, LigneCommande>
     */
    public function getLigneCommandes(): Collection
    {
        return $this->ligneCommandes;
    }

/**
 * Ajoute une ligne de commande à cette commande.
 *

 */
public function addLigneCommande(LigneCommande $ligneCommande): static
{
    // Vérifie si la ligne de commande n'est pas déjà présente dans la collection
    if (!$this->ligneCommandes->contains($ligneCommande)) {
        // Ajoute la ligne de commande à la collection
        $this->ligneCommandes->add($ligneCommande);

        // Définit la propriété "commande" de la ligne pour qu'elle pointe vers cette commande
        // Cela permet de maintenir la relation bidirectionnelle cohérente
        $ligneCommande->setCommande($this);
    }

    // Retourne l'objet courant (this) pour permettre l'appel en chaîne
    return $this;
}

/**
 * Supprime une ligne de commande de cette commande.
 *

 */
public function removeLigneCommande(LigneCommande $ligneCommande): static
{
    // Supprime la ligne de commande de la collection
    // La méthode removeElement retourne true si l'élément a été supprimé
    if ($this->ligneCommandes->removeElement($ligneCommande)) {

        // Vérifie si la ligne de commande pointe encore vers cette commande
        // (au cas où la relation n'aurait pas déjà été modifiée)
        if ($ligneCommande->getCommande() === $this) {
            // Détruit la relation côté LigneCommande
            // Cela évite que la ligne continue à pointer vers une commande inexistante
            $ligneCommande->setCommande(null);
        }
    }

    // Retourne l'objet 
    return $this;
}

}
