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
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Attribute\Route;

// Le contrôleur pour gérer tout ce qui concerne le panier
final class PanierController extends AbstractController
{
    // Affiche le contenu du panier de l'utilisateur
    #[Route('/panier', name: 'panier_index')]
    public function index(PanierRepository $repo, SessionInterface $session): Response
    {
        // // suprprimer sesion panier 
        // $session->remove('paniers');
        
        $total = 0;
        
        if ($this->getUser()) {
            // On récupère tous les paniers liés à l'utilisateur connecté
            $paniers = $repo->findBy(['user' => $this->getUser()]);
            // findBy cherche dans la table Panier toutes les lignes où la colonne user correspond à l'utilisateur connecté ($this->getUser()).
            // En clair : "Montre-moi tous les paniers appartenant à l'utilisateur connecté."
            
            // total prix des produits
            foreach ($paniers as $panier) {
                $total += $panier->getQuantite() * $panier->getProduit()->getPrix();
            }
        } else {
            // Utilisateur non connecté : panier dans la session
            $paniers = $session->get('paniers', []);
            foreach ($paniers as $item) {
                $total += $item['quantite'] * $item['produit']['prix'];
            }
        }

        // dump($paniers);
        // dump($session);

        // On affiche la vue avec les paniers
        return $this->render('panier/index.html.twig', [
            'lignes' => $paniers,
            'total' => $total,
          
        ]);
    }

    // Permet d'ajouter un produit dans le panier
    #[Route('/panier/ajouter/{id}', name: 'panier_ajouter')]
    public function ajouter(
        Produit $produit,
        Request $request,
        EntityManagerInterface $em,
        PanierRepository $repo,
        SessionInterface $session
    ): Response {
        $user = $this->getUser();
        $quantite = max(1, (int) $request->query->get('quantite', 1));

        if ($user) {
            $ligne = $repo->findOneBy(['user' => $user, 'produit' => $produit]);
            // findOneBy() pour vérifier s'il existe déjà une ligne

            if ($ligne) {
                // Si une ligne existe déjà, on ajoute la quantité
                $ligne->setQuantite($ligne->getQuantite() + $quantite);
            } else {
                // Sinon, on crée une nouvelle ligne
                $ligne = new Panier();
                $ligne->setUser($user)
                      ->setProduit($produit)
                      ->setQuantite($quantite);
                $em->persist($ligne);
            }

            $em->flush();
        } else {
            $panier = $session->get('paniers', []);

            if (isset($panier[$produit->getId()])) {
                // Si le produit existe déjà dans le panier, on augmente la quantité
                $panier[$produit->getId()]['quantite'] += $quantite;
            } else {
                // Sinon, on ajoute un nouvel article au panier
                $panier[$produit->getId()] = [
                    'produit' => [
                        'id' => $produit->getId(),
                        'nom' => $produit->getNom(),
                        'prix' => $produit->getPrix(),
                        'stock' => $produit->getStock(),
                    ],
                    'quantite' => $quantite,
                ];
            }

            $session->set('paniers', $panier);
        }

        $this->addFlash('success', 'Produit ajouté au panier.');

        return $this->redirect($request->headers->get('referer') ?? $this->generateUrl('panier_index'));
    }

    // Permet de retirer un produit du panier
    #[Route('/retirer/{id}', name: 'panier_retirer')]
    public function retirer(Panier $ligne, EntityManagerInterface $em): Response
    {
        $em->remove($ligne);
        $em->flush();

        return $this->redirectToRoute('panier_index');
    }

    #[Route('/panier/session/retirer/{id}', name: 'panier_session_retirer')]
public function retirerSession(int $id, SessionInterface $session, Request $request): Response
{
    $panier = $session->get('paniers', []);

    if (isset($panier[$id])) {
        unset($panier[$id]);
        $session->set('paniers', $panier);
    }

    $this->addFlash('success', 'Produit retiré du panier.');

    return $this->redirectToRoute('panier_index');
}

    // Vide complètement le panier de l'utilisateur
    #[Route('/vider', name: 'panier_vider')]
    public function vider(PanierRepository $repo, SessionInterface $session, EntityManagerInterface $em): Response
    {
        if ($this->getUser()) {
            // On récupère toutes les lignes du panier de l'utilisateur
            $lignes = $repo->findBy(['user' => $this->getUser()]);

            foreach ($lignes as $ligne) {
                $em->remove($ligne);
            }

            $em->flush();
        } else {
            $session->remove('paniers');
        }

        return $this->redirectToRoute('panier_index');
    }

    // Valide le panier et crée une commande
    #[Route('/panier/valider', name: 'panier_valider')]
    public function validerPanier(EntityManagerInterface $em, PanierRepository $panierRepo): Response
    {
        $user = $this->getUser();
        $panierLignes = $panierRepo->findBy(['user' => $user]);

        if (!$panierLignes) {
            $this->addFlash('error', 'Votre panier est vide.');
            return $this->redirectToRoute('panier_index');
        }

        $commande = new Commande();
        $commande->setUser($user);
        $commande->setCreatedAt(new \DateTimeImmutable());
        $commande->setStatut('en attente');

        foreach ($panierLignes as $lignePanier) {
            $produit = $lignePanier->getProduit();
            $quantite = $lignePanier->getQuantite();

            if ($quantite > $produit->getStock()) {
                $this->addFlash('error', 'Le stock du produit "' . $produit->getNom() . '" est insuffisant.');
                return $this->redirectToRoute('panier_index');
            }

            $produit->setStock($produit->getStock() - $quantite);

            $ligneCommande = new LigneCommande();
            $ligneCommande->setCommande($commande)
                          ->setProduit($produit)
                          ->setQuantite($quantite)
                          ->setPrix($produit->getPrix());

            $em->persist($ligneCommande);
            $em->remove($lignePanier);
        }

        $em->persist($commande);
        $em->flush();

        $this->addFlash('success', 'Commande validée avec succès !');
        return $this->redirectToRoute('commande_index');
    }

    // Modifie la quantité d'un produit dans le panier
    #[Route('/panier/modifier/{id}', name: 'panier_modifier_quantite', methods: ['POST'])]
    public function modifierQuantite(Panier $ligne, Request $request, EntityManagerInterface $em): Response
    {
        if ($this->getUser()) {
            $quantite = max(1, (int) $request->request->get('quantite'));

            $ligne->setQuantite($quantite);
            $em->flush();
        } 

        $this->addFlash('success', 'Quantité mise à jour.');
        return $this->redirectToRoute('panier_index');
    }

    // Modifie la quantité d'un produit dans le panier
    #[Route('/panier/modifier/session/{id}', name: 'panier_session_modifier_quantite', methods: ['POST'])]
    public function modifierQuantiteSession(int $id, Request $request, SessionInterface $session): Response
    {
        $panier = $session->get('paniers', []);
        $quantite = max(1, (int) $request->request->get('quantite'));

        if (isset($panier[$id])) {
            $panier[$id]['quantite'] = $quantite;
            $session->set('paniers', $panier);
        }

        $this->addFlash('success', 'Quantité mise à jour.');
        return $this->redirectToRoute('panier_index');
    }
}
