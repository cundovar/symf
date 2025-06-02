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
    #[Route('/panier/ajouter/{id}', name: 'panier_ajouter')]
    public function ajouter(
        Produit $produit,
        Request $request,
        EntityManagerInterface $em,
        PanierRepository $repo
    ): Response {
        $user = $this->getUser();
        // On récupère la quantité demandée (minimum 1 si vide ou incorrect)
        $quantite = max(1, (int) $request->query->get('quantite', 1));

        // On vérifie si le produit est déjà dans le panier de l'utilisateur
        $ligne = $repo->findOneBy(['user' => $user, 'produit' => $produit]);

        if ($ligne) {
            // Si le produit est déjà là, on augmente la quantité
            $ligne->setQuantite($ligne->getQuantite() + $quantite);
        } else {
            // Sinon, on crée une nouvelle ligne de panier
            $ligne = new Panier();
            $ligne->setUser($user)
                  ->setProduit($produit)
                  ->setQuantite($quantite);
            $em->persist($ligne); // On prépare pour l'enregistrement
        }

        $em->flush(); // On enregistre dans la base de données

        $this->addFlash('success', 'Produit ajouté au panier.');
        // Redirige vers la page précédente
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
