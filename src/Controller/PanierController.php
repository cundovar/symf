<?php

namespace App\Controller;

use App\Entity\Commande;
use App\Entity\LigneCommande;
use App\Entity\Panier;
use App\Entity\Produit;
use App\Repository\PanierRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class PanierController extends AbstractController
{
     #[Route('/', name: 'panier_index')]
    public function index(PanierRepository $repo): \Symfony\Component\HttpFoundation\Response
    {
        $panier = $repo->findBy(['user' => $this->getUser()]);
        return $this->render('panier/index.html.twig', ['lignes' => $panier]);
    }

        #[Route('/ajouter/{id}', name: 'panier_ajouter')]
    public function ajouter(Produit $produit, EntityManagerInterface $em, PanierRepository $repo): \Symfony\Component\HttpFoundation\RedirectResponse
    {
        $user = $this->getUser();
        $ligne = $repo->findOneBy(['user' => $user, 'produit' => $produit]);

        if ($ligne) {
            $ligne->setQuantite($ligne->getQuantite() + 1);
        } else {
            $ligne = new Panier();
            $ligne->setUser($user);
            $ligne->setProduit($produit);
            $ligne->setQuantite(1);
            $em->persist($ligne);
        }

        $em->flush();

        return $this->redirectToRoute('panier_index');
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

    // 1. Créer la commande
    $commande = new Commande();
    $commande->setUser($user);
    $commande->setCreatedAt(new \DateTimeImmutable());
    $commande->setStatut('en attente');

    // 2. Ajouter chaque ligne du panier comme LigneCommande
    foreach ($panierLignes as $lignePanier) {
        $ligneCommande = new LigneCommande();
        $ligneCommande->setCommande($commande);
        $ligneCommande->setProduit($lignePanier->getProduit());
        $ligneCommande->setQuantite($lignePanier->getQuantite());
        $ligneCommande->setPrix($lignePanier->getProduit()->getPrix());

        $em->persist($ligneCommande);
        $em->remove($lignePanier); // vider le panier
    }

    $em->persist($commande);
    $em->flush();

    $this->addFlash('success', 'Commande validée avec succès !');
    return $this->redirectToRoute('commande_index');
}

 
}
