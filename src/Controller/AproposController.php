<?php

namespace App\Controller;

use App\Repository\AproposRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class AproposController extends AbstractController
{
    #[Route('/apropos', name: 'apropos')]
    public function index(AproposRepository $repo): Response
    {

      
        $apropos = $repo->find(5);


        return $this->render('apropos/index.html.twig', [
            'apropos' => $apropos,
        ]);
    }
}
