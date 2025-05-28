<?php

namespace App\Controller;

use App\Repository\ProduitRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class HomeController extends AbstractController
{
    #[Route('/', name: 'home')]
    public function index(Request $request,ProduitRepository $repo): Response
    {

             dump(['Méthode' => get_class_methods($request) ]);
        dump(['Méthode' => get_class_methods(AbstractController::class) ]);
        dump(['Méthode' => get_class_methods(ProduitRepository::class) ]);


        dump([
    'methodes' => get_class_methods($repo)
]);

            dump([
        'Méthode' => $request->getMethod(),
        'URL complète' => $request->getUri(),
        'Chemin URL' => $request->getPathInfo(),
        'Paramètres GET' => $request->query->all(),
        'Paramètres POST' => $request->request->all(),
        'Cookies' => $request->cookies->all(),
        'Fichiers' => $request->files->all(),
        'En-têtes' => $request->headers->all(),
        'IP client' => $request->getClientIp(),
        'Est AJAX ?' => $request->isXmlHttpRequest(),
    ]);

     /**
     * __construct()
     * - Constructeur de base injecté automatiquement par Symfony.
     * - Permet d'initialiser le repository avec l'EntityManager.
     */
    

    /**
     * createQueryBuilder(string $alias): QueryBuilder
     * - Crée un QueryBuilder pour construire dynamiquement une requête DQL.
     * - Exemple : $repo->createQueryBuilder('p')->where('p.stock > 0')->getQuery()->getResult();
     */
    

    /**
     * createResultSetMappingBuilder(string $alias): ResultSetMappingBuilder
     * - Crée un mappage personnalisé entre les résultats SQL natifs et une entité.
     * - Utile pour des requêtes SQL brutes avec EntityManager::createNativeQuery().
     */
    

    /**
     * find($id): ?Entity
     * - Récupère une entité par son ID primaire.
     * - Exemple : $repo->find(42);
     */
   

    /**
     * findBy(array $criteria, ?array $orderBy = null, ?int $limit = null, ?int $offset = null): array
     * - Trouve un ensemble d’entités correspondant aux critères donnés.
     * - Exemple : $repo->findBy(['category' => 'Meubles'], ['prix' => 'ASC']);
     */
 

    /**
     * findOneBy(array $criteria): ?Entity
     * - Même logique que findBy, mais retourne un seul résultat.
     * - Exemple : $repo->findOneBy(['slug' => 'canape-velours']);
     */
 

    /**
     * count(array $criteria = []): int
     * - Compte le nombre d’entités correspondant à un critère.
     * - Exemple : $repo->count(['disponible' => true]);
     */
    

    /**
     * __call(string $method, array $arguments)
     * - Intercepte les appels à des méthodes non définies (ex: findByNom).
     * - Doctrine les génère automatiquement si leur nom est basé sur un champ.
     */
  

    /**
     * matching(Criteria $criteria): Collection
     * - Permet d’utiliser l’API Criteria (objet orienté filtre).
     * - Exemple : $repo->matching($criteria);
     */
   

    /**
     * findAll(): array
     * - Récupère toutes les entités de la table liée.
     * - Exemple : $repo->findAll();
     */
   

    /**
     * getClassName(): string
     * - Retourne le nom complet de la classe de l'entité gérée.
     * - Exemple : "App\Entity\Produit"
     */
 

        $produits = $repo->findAll();
          // Accéder à la session
    $session = $request->getSession(); // recuperation de l'objet session
    
    $session->set('test1',"valeur2");

    dump($session);



    dump($produits);
    



        return $this->render('home/index.html.twig', [
           'session'=>$session,
           'produits' => $produits,
        ]);
    }
    #[Route('/apropos', name: 'apropos')]
    public function apropos(): Response
    {
        return $this->render('apropos/index.html.twig');
    }
  

 
}
