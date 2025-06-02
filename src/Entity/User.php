<?php

namespace App\Entity;

// Importation des classes nécessaires pour que Doctrine et Symfony puissent gérer l'entité
use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection; // Utilisé pour stocker les relations comme une "liste" d'objets
use Doctrine\Common\Collections\Collection; // Interface de base pour les collections Doctrine
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_USERNAME', fields: ['username'])]
#[UniqueEntity(fields: ['username'], message: 'There is already an account with this username')]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null; // Clé primaire auto-incrémentée, générée automatiquement par Doctrine

    #[ORM\Column(length: 180)]
    private ?string $username = null; // Nom d'utilisateur (doit être unique)

    #[ORM\Column]
    private array $roles = []; // Tableau des rôles (ex: ['ROLE_USER', 'ROLE_ADMIN'])

    #[ORM\Column]
    private ?string $password = null; // Mot de passe chiffré

    #[ORM\Column(length: 255)]
    private ?string $email = null;

    #[ORM\Column(length: 255)]
    private ?string $adresse = null;

    #[ORM\Column(length: 30)]
    private ?string $telephone = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $image = null;

    /**
     * 🔗 RELATION : UN utilisateur PEUT avoir PLUSIEURS paniers
     * - mappedBy = le nom de la propriété dans l'entité Panier qui fait référence à User.
     * - targetEntity = l'entité liée (Panier).
     * - orphanRemoval = true : si un panier est supprimé de la liste, il est aussi supprimé de la base.
     */
    #[ORM\OneToMany(mappedBy: 'user', targetEntity: Panier::class, orphanRemoval: true)]
    private Collection $paniers;

    /**
     * 🔗 RELATION : UN utilisateur PEUT avoir PLUSIEURS commandes
     * - mappedBy = champ dans Commande qui pointe vers User.
     */
    #[ORM\OneToMany(mappedBy: 'user', targetEntity: Commande::class)]
    private Collection $commandes;

    /**
     *  Constructeur
     * Doctrine attend que les relations soient initialisées comme des objets Collection.
     *  Ne pas laisser ces propriétés nulles, sinon erreur !
     * 
     *  ArrayCollection est une implémentation concrète de Collection.
     * Elle fonctionne comme un tableau, mais avec des méthodes utiles : add(), remove(), contains(), etc.
     */
    // ArrayCollection est une classe fournie par Doctrine, qui agit comme un tableau PHP amélioré.
    //  Elle est utilisée pour stocker et gérer des collections d'objets, notamment dans les relations entre entités.
//     Doctrine ne peut pas utiliser un simple tableau ([]) pour gérer des relations, car il a besoin de fonctionnalités supplémentaires pour :

// suivre les changements dans les entités liées ;

// gérer automatiquement les ajouts et suppressions ;

// synchroniser les deux côtés d'une relation bidirectionnelle.
    public function __construct()
    {
        $this->paniers = new ArrayCollection(); // Liste vide de paniers au début
       // Cette ligne initialise la propriété $paniers avec une nouvelle instance vide de ArrayCollection.
        // Cela signifie que l’objet User démarre avec aucun panier, mais est prêt à en recevoir.


        $this->commandes = new ArrayCollection(); // Liste vide de commandes
//         Idem : la propriété $commandes est aussi initialisée comme une collection vide de Commande.
//         Doctrine pourra ensuite y ajouter ou retirer des objets Commande sans problème.
    }




    // Getter pour l'ID
    public function getId(): ?int
    {
        return $this->id;
    }

    // Getter/Setter pour le username
    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(string $username): static
    {
        $this->username = $username;
        return $this;
    }

    // Requis par Symfony pour identifier l'utilisateur
    public function getUserIdentifier(): string
    {
        return (string) $this->username;
    }

    // Retourne les rôles avec au moins ROLE_USER par défaut
    public function getRoles(): array
    {
        $roles = $this->roles;
        $roles[] = 'ROLE_USER';
        return array_unique($roles);
    }

    public function setRoles(array $roles): static
    {
        $this->roles = $roles;
        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;
        return $this;
    }

    // Méthode à implémenter même si vide, pour effacer les données sensibles (utile si on les stocke temporairement)
    public function eraseCredentials(): void
    {
        // Exemple : $this->plainPassword = null;
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

    public function getAdresse(): ?string
    {
        return $this->adresse;
    }

    public function setAdresse(string $adresse): static
    {
        $this->adresse = $adresse;
        return $this;
    }

    public function getTelephone(): ?string
    {
        return $this->telephone;
    }

    public function setTelephone(string $telephone): static
    {
        $this->telephone = $telephone;
        return $this;
    }

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(?string $image): static
    {
        $this->image = $image;
        return $this;
    }

    /**
     * Retourne la collection de paniers liée à l'utilisateur
     * ⚠️ On retourne un objet Collection, pas un tableau PHP standard
     */
    public function getPaniers(): Collection
    {
        return $this->paniers;
    }

    /**
     * Ajoute un panier à la collection de l'utilisateur
     * 🔁 On met aussi à jour l'autre côté de la relation (Panier -> User)
     */
// Cette fonction permet d'ajouter un panier à l'utilisateur.
// Elle prend un objet Panier en paramètre et renvoie l'utilisateur (this).
public function addPanier(Panier $panier): static
{
    // Si le panier n'est pas déjà dans la liste des paniers de l'utilisateur
    if (!$this->paniers->contains($panier)) {
        // On ajoute le panier à la liste
        $this->paniers->add($panier);

        // On indique aussi au panier quel est son utilisateur
        // (important pour que la relation fonctionne dans les deux sens)
        $panier->setUser($this);
    }

    // On retourne l'utilisateur pour pouvoir enchaîner d'autres appels (ex: $user->addPanier($panier)->addPanier($autrePanier);)
    return $this;
}


//     Pourquoi addPanier et pas setPanier ?

// Différence entre add et set :
// set → sert en général à remplacer une valeur (ou une seule entité).

// Exemple : $user->setEmail('email@example.com'); — on définit un email (1 seul).

// Si on faisait setPanier, cela voudrait dire : "je donne UN seul panier à l'utilisateur et j'écrase l'ancien si besoin".

// add → veut dire ajouter à une liste ou une collection sans écraser.

// Ici, un utilisateur peut avoir plusieurs paniers.

// Donc on ajoute chaque nouveau panier dans une collection (ex: une ArrayCollection Doctrine).

    /**
     * Supprime un panier
     * 🧹 Si orphanRemoval = true, Doctrine supprime aussi le panier en base
     */
    public function removePanier(Panier $panier): static
    {
        if ($this->paniers->removeElement($panier)) {
            if ($panier->getUser() === $this) {
                $panier->setUser(null); // on "délie" le panier de l'utilisateur
            }
        }

        return $this;
    }

    /**
     * Retourne la collection de commandes
     */
    public function getCommandes(): Collection
    {
        return $this->commandes;
    }

    /**
     * Ajoute une commande à l'utilisateur et met à jour l'inverse
     */

//      public : accessible depuis l’extérieur de la classe.

// function addPanier(Panier $panier) : méthode qui reçoit un  
  //objet de type Panier en paramètre.

// : static : signifie que la méthode retourne l’instance actuelle de l’objet User, pour permettre le chainage fluide :
    public function addCommande(Commande $commande): static
    {
        if (!$this->commandes->contains($commande)) {
            $this->commandes->add($commande);
            $commande->setUser($this); // lien inverse
        }

        return $this;
    }

    /**
     * Supprime une commande
     */
    public function removeCommande(Commande $commande): static
    {
        if ($this->commandes->removeElement($commande)) {
            if ($commande->getUser() === $this) {
                $commande->setUser(null);
            }
        }

        return $this;
    }

    // Déclaration oubliée/inutile ici car non utilisée ailleurs. À retirer ou corriger.
    private Collection $panierLignes;

    /**
     * Calcule le total d'articles dans tous les paniers de l'utilisateur
     * 💡 Cette méthode suppose que chaque panier contient une méthode getQuantite()
     */
    public function getNombreArticlesPanier(): int
    {
        $total = 0;
        foreach ($this->paniers as $ligne) {
            $total += $ligne->getQuantite(); // Ajoute la quantité de chaque ligne du panier
        }
        return $total;
    }
}
