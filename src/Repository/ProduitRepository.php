<?php

namespace App\Repository;

use App\Entity\Produit;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;


// cette class est le repository de la class Produit
// elle permet de faire des requetes personalisées sur la base de données ( chercher les produit par prix,nom date)
// cette class ne genere pas directement de SQL 
     // elle utilise des fonctions comme find ou findBy....
     // c'est Doctrine qui transforme ces requetes en SQL

          // exemple 
          //$repository->findAll ();-> SELECT * FROM produit 
          // $repository->find($id);-> SELECT * FROM produit WHERE id = $id
          // $repository->findBy(['prix' => $prix]);-> SELECT * FROM produit WHERE prix = $prix




/**
 * @extends ServiceEntityRepository<Produit>
 */
class ProduitRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Produit::class);
    }

    //    /**
    //     * @return Produit[] Returns an array of Produit objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('p')
    //            ->andWhere('p.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('p.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Produit
    //    {
    //        return $this->createQueryBuilder('p')
    //            ->andWhere('p.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
