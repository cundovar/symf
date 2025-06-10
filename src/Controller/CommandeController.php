<?php

namespace App\Controller;

use App\Repository\CommandeRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;


#[Route('/commande')]
class CommandeController extends AbstractController
{
    #[Route('/', name: 'commande_index')]
    public function index(CommandeRepository $repo): Response
    {
        // $commandes = toutes les commandes de l'utilisateur connecté
        $commandes = $repo->findBy(['user' => $this->getUser()]);
        return $this->render('commande/index.html.twig', [
            'commandes' => $commandes,
        ]);
    }
}
