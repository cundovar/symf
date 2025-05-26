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

final class PanierController extends AbstractController
{
     #[Route('/panier', name: 'panier_index')]
    public function index(PanierRepository $repo): \Symfony\Component\HttpFoundation\Response
    {
        $panier = $repo->findBy(['user' => $this->getUser()]);
        return $this->render('panier/index.html.twig', ['lignes' => $panier]);
    }

#[Route('/panier/ajouter/{id}', name: 'panier_ajouter')]
public function ajouter(
    Produit $produit,
    Request $request,
    EntityManagerInterface $em,
    PanierRepository $repo
): Response {
    $user = $this->getUser();
    $quantite = max(1, (int) $request->query->get('quantite', 1));

    $ligne = $repo->findOneBy(['user' => $user, 'produit' => $produit]);

    if ($ligne) {
        $ligne->setQuantite($ligne->getQuantite() + $quantite);
    } else {
        $ligne = new Panier();
        $ligne->setUser($user)
              ->setProduit($produit)
              ->setQuantite($quantite);
        $em->persist($ligne);
    }

    $em->flush();

  $this->addFlash('success', 'sup');
       return $this->redirect($request->headers->get('referer') );
}

    #[Route('/retirer/{id}', name: 'panier_retirer')]
    public function retirer(Panier $ligne, EntityManagerInterface $em): \Symfony\Component\HttpFoundation\RedirectResponse
    {
        $em->remove($ligne);
        $em->flush();

        return $this->redirectToRoute('panier_index');
    }

    #[Route('/vider', name: 'panier_vider')]
    public function vider(PanierRepository $repo, EntityManagerInterface $em): \Symfony\Component\HttpFoundation\RedirectResponse
    {
        $lignes = $repo->findBy(['user' => $this->getUser()]);
        foreach ($lignes as $ligne) {
            $em->remove($ligne);
        }
        $em->flush();

        return $this->redirectToRoute('panier_index');
    }


 #[Route('/panier/valider', name: 'panier_valider')]
public function validerPanier(
    EntityManagerInterface $em,
    PanierRepository $panierRepo
): Response {
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

        // Vérification finale de stock (sécurité backend)
        if ($quantite > $produit->getStock()) {
            $this->addFlash('error', 'Le stock du produit "' . $produit->getNom() . '" est insuffisant.');
            return $this->redirectToRoute('panier_index');
        }

        // Mise à jour du stock produit
        $produit->setStock($produit->getStock() - $quantite);

        $ligneCommande = new LigneCommande();
        $ligneCommande->setCommande($commande);
        $ligneCommande->setProduit($produit);
        $ligneCommande->setQuantite($quantite);
        $ligneCommande->setPrix($produit->getPrix());

        $em->persist($ligneCommande);
        $em->remove($lignePanier); // vider le panier
    }

    $em->persist($commande);
    $em->flush();

    $this->addFlash('success', 'Commande validée avec succès !');
    return $this->redirectToRoute('commande_index');
}
#[Route('/panier/modifier/{id}', name: 'panier_modifier_quantite', methods: ['POST'])]
public function modifierQuantite(Panier $ligne, Request $request, EntityManagerInterface $em): Response
{
    $quantite = max(1, (int) $request->request->get('quantite'));
    $ligne->setQuantite($quantite);
    $em->flush();

    $this->addFlash('success', 'Quantité mise à jour.');
    return $this->redirectToRoute('panier_index');
}
 
}
