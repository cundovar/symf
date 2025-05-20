<?php
// un namespace permet de classer logiquement les classes de la même application
namespace App\Entity;

// on importe la class repository associée à notre entité Produit
use App\Repository\ProduitRepository;

// on importe les annotations Doctrine
use Doctrine\ORM\Mapping as ORM;


// on  indique que cette class est une entité Doctrine et qu'elle est liee au repository ProduitRepository
#[ORM\Entity(repositoryClass: ProduitRepository::class)]
class Produit
{
    #[ORM\Id]  // clé primaire de l'entité
    #[ORM\GeneratedValue] // auto-increment
    #[ORM\Column]// la colonne de l'entité
    private ?int $id = null; // propriété privé de la class avec typage int et valeur par defaut null 

    #[ORM\Column(length: 255)] // colonne name de type string de longueur 255
    private ?string $nom = null;

    // getter et setter

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
}
