<?php

namespace App\Repository; // Déclare le namespace du repository

use App\Entity\Produit; // Importe l'entité Produit
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository; // Importe le ServiceEntityRepository de Doctrine
use Doctrine\Persistence\ManagerRegistry; // Importe ManagerRegistry pour interagir avec l'EntityManager

/**
 * @extends ServiceEntityRepository<Produit>
 * 
 * Le repository étend ServiceEntityRepository spécifique à l'entité Produit.
 */
class ProduitRepository extends ServiceEntityRepository
{
    // Le constructeur
    public function __construct(ManagerRegistry $registry)
    {
        // Appelle le constructeur parent avec la classe de l'entité Produit
        parent::__construct($registry, Produit::class);
    }

    /**
     * Retourne toutes les catégories distinctes
     */
    public function findDistinctCategories(): array
    {
        // Crée un QueryBuilder sur l'entité Produit aliasé 'p'
        return $this->createQueryBuilder('p')
            // Sélectionne DISTINCT les id et name de la catégorie associée
         ->select('DISTINCT c.id, c.name')
            //Évite les doublons dans les résultats.

// S'il y a plusieurs produits dans la même catégorie, on récupère chaque catégorie une seule fois.

            // Fait une jointure entre Produit et Category (relation ManyToOne) aliasé 'c'
            ->join('p.category', 'c')
            // Trie les résultats par nom de catégorie de façon croissante
            ->orderBy('c.name', 'ASC') // Trie les résultats par ordre croissant (ASC = Ascending).
            //  Possibilités dans orderBy :
            //  'ASC' : Croissant (A-Z, 0-9).

            //   'DESC' : Décroissant (Z-A, 9-0).

            // // Génère la requête
            ->getQuery()
            // Exécute et retourne le résultat sous forme de tableau
            ->getResult();
    }

    /**
     * Trouver les produits les moins chers
     */
    public function findCheapestProducts(int $limit = 5): array
    {
        // Crée un QueryBuilder sur Produit aliasé 'p'
        return $this->createQueryBuilder('p')
            // Trie par prix croissant
            ->orderBy('p.prix', 'ASC')
            // Limite le nombre de résultats retournés
            ->setMaxResults($limit)
            // Génère la requête
            ->getQuery()
            // Exécute et retourne le résultat sous forme de tableau
            ->getResult();
    }

    /**
     * Trouver les produits par une tranche de prix
     */
    public function findProductsByPriceRange(float $min, float $max): array
    {
        // Crée un QueryBuilder sur Produit aliasé 'p'
        return $this->createQueryBuilder('p')
            // Filtre les produits dont le prix est entre min et max
            ->where('p.prix BETWEEN :min AND :max')
            // Lie la valeur min au paramètre :min
            ->setParameter('min', $min)
            // Lie la valeur max au paramètre :max
            ->setParameter('max', $max)
            // Trie par prix croissant
            ->orderBy('p.prix', 'ASC')
            // Génère la requête
            ->getQuery()
            // Exécute et retourne le résultat sous forme de tableau
            ->getResult();
    }

    /**
     * Trouver les produits par mot-clé (dans le nom)
     */
    public function findProductsByKeyword(string $keyword): array
    {
        // Crée un QueryBuilder sur Produit aliasé 'p'
        return $this->createQueryBuilder('p')
            // Filtre avec une clause LIKE sur le nom du produit
            ->where('p.nom LIKE :keyword')
            // Lie le paramètre :keyword avec des % pour un LIKE SQL
            ->setParameter('keyword', '%' . $keyword . '%')
            // Trie les résultats par nom croissant
            ->orderBy('p.nom', 'ASC')
            // Génère la requête
            ->getQuery()
            // Exécute et retourne le résultat sous forme de tableau
            ->getResult();
    }

    /**
     * Compter le nombre total de produits
     */
    public function countAllProducts(): int
    {
        // Crée un QueryBuilder sur Produit aliasé 'p'
        return (int) $this->createQueryBuilder('p')
            // Sélectionne un COUNT du nombre d'ID
            ->select('COUNT(p.id)')
            // Génère la requête
            ->getQuery()
            // Exécute et retourne une seule valeur scalaire
            ->getSingleScalarResult();
    }
}
