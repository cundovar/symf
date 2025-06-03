<?php

namespace App\Controller;

use App\Entity\Commande;
use App\Entity\LigneCommande;
use App\Entity\Panier;
use App\Entity\Produit;
use App\Repository\PanierRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

// Le contrôleur pour gérer tout ce qui concerne le panier
final class PanierController extends AbstractController
{
    // Affiche le contenu du panier de l'utilisateur
    #[Route('/panier', name: 'panier_index')]
    public function index(PanierRepository $repo): Response
    {
        // On récupère tous les paniers liés à l'utilisateur connecté
        $panier = $repo->findBy(['user' => $this->getUser()]);
//         findBy cherche dans la table Panier toutes les lignes où la colonne user correspond à l'utilisateur connecté ($this->getUser()).
//       En clair :
// "Montre-moi tous les paniers appartenant à l'utilisateur connecté."

        // On affiche la vue avec les paniers
        return $this->render('panier/index.html.twig', ['lignes' => $panier]);
    }

    // Permet d'ajouter un produit dans le panier
  // Route pour accéder à l'action : /panier/ajouter/{id}
// {id} correspond à l'identifiant du produit à ajouter au panier
#[Route('/panier/ajouter/{id}', name: 'panier_ajouter')]
public function ajouter(
    // Symfony va automatiquement chercher le Produit correspondant à l'id dans l'URL
    Produit $produit,
    // Représente la requête HTTP (GET, POST...) pour accéder aux paramètres envoyés par l'utilisateur
    Request $request,
    // Permet d'interagir avec la base de données (INSERT, UPDATE, DELETE)
    EntityManagerInterface $em,
    // Permet de récupérer des lignes du panier liées à l'utilisateur dans la base de données
    PanierRepository $repo
): Response {
    // Récupère l'utilisateur actuellement connecté
    $user = $this->getUser();

    // On récupère la quantité demandée dans l'URL via la query string (ex: ?quantite=2)
    // Si aucune quantité n'est fournie ou si ce n'est pas un nombre valide, on prend 1
    $quantite = max(1, (int) $request->query->get('quantite', 1));

    // On cherche si une ligne de panier existe déjà pour ce produit et cet utilisateur
    // findOneBy cherche une seule entité en fonction des critères passés
    $ligne = $repo->findOneBy(['user' => $user, 'produit' => $produit]);
//     Détail de findOneBy()
// Méthode : findOneBy(array $criteria)

// But : Récupérer un seul enregistrement dans la base de données qui correspond aux critères donnés.

// Paramètre $criteria : Un tableau associatif où :

// La clé est le nom d'un champ ou d'une propriété de l'entité.

// La valeur est ce qu'on veut chercher.

// Doctrine va transformer ce tableau en une requête SQL de type :

// sql
// Copier
// Modifier
// SELECT * FROM <table> WHERE user_id = :user AND produit_id = :produit LIMIT 1;

    if ($ligne) {
        // Si une ligne existe déjà (le produit est déjà dans le panier),
        // on ajoute simplement la quantité demandée à la quantité existante
        $ligne->setQuantite($ligne->getQuantite() + $quantite);
    } else {
        // Sinon (le produit n'est pas encore dans le panier),
        // on crée une nouvelle ligne de panier
        $ligne = new Panier();
        // On associe l'utilisateur à cette ligne
        $ligne->setUser($user)
              // On associe le produit à cette ligne
              ->setProduit($produit)
              // On définit la quantité
              ->setQuantite($quantite);
        // On indique à Doctrine qu'on veut sauvegarder cette nouvelle ligne
        $em->persist($ligne);
    }

    // Envoie toutes les modifications faites avec persist() à la base de données
    $em->flush();

    // Ajoute un message flash pour informer l'utilisateur que le produit a bien été ajouté
    $this->addFlash('success', 'Produit ajouté au panier.');

    // Redirige l'utilisateur vers la page d'où il vient (la page précédente)
    return $this->redirect($request->headers->get('referer'));
}


    // Permet de retirer un produit du panier
    #[Route('/retirer/{id}', name: 'panier_retirer')]
    public function retirer(Panier $ligne, EntityManagerInterface $em): Response
    {
        // Supprime la ligne du panier
        $em->remove($ligne);
        $em->flush();

        return $this->redirectToRoute('panier_index');
    }

    // Vide complètement le panier de l'utilisateur
    #[Route('/vider', name: 'panier_vider')]
    public function vider(PanierRepository $repo, EntityManagerInterface $em): Response
    {
        // On récupère toutes les lignes du panier de l'utilisateur
        $lignes = $repo->findBy(['user' => $this->getUser()]);

        // On supprime chaque ligne une par une
        foreach ($lignes as $ligne) {
            $em->remove($ligne);
        }

        $em->flush(); // Enregistre les suppressions

        return $this->redirectToRoute('panier_index');
    }

    // Valide le panier et crée une commande
    #[Route('/panier/valider', name: 'panier_valider')]
    public function validerPanier(
        EntityManagerInterface $em,
        PanierRepository $panierRepo
    ): Response {
        $user = $this->getUser();
        // On récupère les lignes du panier
        $panierLignes = $panierRepo->findBy(['user' => $user]);

        // Si le panier est vide
        if (!$panierLignes) {
            $this->addFlash('error', 'Votre panier est vide.');
            return $this->redirectToRoute('panier_index');
        }

        // Création de la commande
        $commande = new Commande();
        $commande->setUser($user);
        $commande->setCreatedAt(new \DateTimeImmutable());
        $commande->setStatut('en attente');

        // Pour chaque ligne de panier
        foreach ($panierLignes as $lignePanier) {
            $produit = $lignePanier->getProduit();
            $quantite = $lignePanier->getQuantite();

            // Vérification du stock (sécurité pour éviter triche)
            if ($quantite > $produit->getStock()) {
                $this->addFlash('error', 'Le stock du produit "' . $produit->getNom() . '" est insuffisant.');
                return $this->redirectToRoute('panier_index');
            }

            // Mise à jour du stock
            $produit->setStock($produit->getStock() - $quantite);

            // Création d'une ligne de commande
            $ligneCommande = new LigneCommande();
            $ligneCommande->setCommande($commande);
            $ligneCommande->setProduit($produit);
            $ligneCommande->setQuantite($quantite);
            $ligneCommande->setPrix($produit->getPrix());

            $em->persist($ligneCommande); // Prépare l'enregistrement de la ligne
            $em->remove($lignePanier);    // Supprime la ligne du panier
        }

        $em->persist($commande); // Prépare l'enregistrement de la commande
        $em->flush(); // Sauvegarde tout en base

        $this->addFlash('success', 'Commande validée avec succès !');
        return $this->redirectToRoute('commande_index');
    }

    // Modifie la quantité d'un produit dans le panier
    #[Route('/panier/modifier/{id}', name: 'panier_modifier_quantite', methods: ['POST'])]
    public function modifierQuantite(Panier $ligne, Request $request, EntityManagerInterface $em): Response
    {
        // On récupère la nouvelle quantité depuis le formulaire
        $quantite = max(1, (int) $request->request->get('quantite'));

        // Mise à jour de la quantité
        $ligne->setQuantite($quantite);
        $em->flush(); // Enregistre la mise à jour

        $this->addFlash('success', 'Quantité mise à jour.');
        return $this->redirectToRoute('panier_index');
    }
}
