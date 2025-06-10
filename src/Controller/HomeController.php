<?php

namespace App\Controller;

use App\Entity\Category;
use App\Entity\Produit;
use App\Repository\ProduitRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class HomeController extends AbstractController
{
    #[Route('/', name: 'home')]
    public function index(Request $request,ProduitRepository $repo,EntityManagerInterface $em): Response
    {
        
        dump(['Méthode' => get_class_methods($request) ]);
        dump(['Méthode' => get_class_methods(AbstractController::class) ]);
        dump(['Méthode' => get_class_methods(ProduitRepository::class) ]);
        $session = $request->getSession(); 
          dump($session->all()); 
        
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
        
        
        $produitss = [];
        $produits = $repo->findAll();
        $unProduit = $repo->find(5); // SELECT * FROM produit WHERE id = 1
        $countProduits = $repo->count(); // SELECT COUNT(*) FROM produit et resultatt est 
        $findByProduits = $repo->findBy(['prix' => 15]); // SELECT * FROM produit WHERE prix = 100
        $selectedProduitSearch = null;
        // Accéder à la session
        $session = $request->getSession(); // recuperation de l'objet session
        dump($unProduit);
        dump($countProduits);
        dump($findByProduits);
        $session->set('test1',"valeur2");
        
        dump($session);
        
    // commencemant du form select avec la fonction find()  façon dynamique
$selectedProduit=null;
if ($request->isMethod('POST')) {

    // recuperation du type de formulaire
    $formType = $request->request->get('form');

    // si la valeur de formType est select_produit
    if ($formType === 'select_produit') {
        $idProduit = $request->request->get('produit');
        $selectedProduit = $em->getRepository(Produit::class)->find($idProduit);
    }

    // si la valeur de formType est search_nom
    if ($formType === 'search_nom') {
        $search = $request->request->get('search');
        $selectedProduit = $em->getRepository(Produit::class)->findOneBy([
            'nom' => $search
        ]);
    }

    // si la valeur de formType est select_categorie
    if ($formType === 'select_categorie') {
          $categorieId = $request->request->get('categorie');
          if ($categorieId) {
              // Attention ici il faut récupérer l'objet Category via l'id
              $category = $em->getRepository(Category::class)->find($categorieId);
              if ($category) {
                // Appel à la méthode du repository
                // category est le nom du champ dans la table produit
                // $category est l'objet Category récupéré
                  $produitss = $repo->findBy([
                      'category' => $category
                    ]);
                }
            }
        }
if ($formType === 'search_nom') {
    $search = $request->request->get('search');
    if ($search) {
        $selectedProduitSearch = $repo->findOneBy([
            'nom' => $search
        ]);
    }
}

    }
    $categories = $repo->findDistinctCategories();

        return $this->render('home/index.html.twig', [
           'session'=>$session,
           'produits' => $produits,
           'unProduit' => $unProduit,
           'countProduits' => $countProduits,
           'findByProduits' => $findByProduits,
           'selectedProduit'=> $selectedProduit,
           'categories' => $categories,
           'produitss' => $produitss,
           'selectedProduitSearch' => $selectedProduitSearch

        ]);


        
    }
  

 
}
