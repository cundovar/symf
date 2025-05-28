<?php

// src/Controller/PreferenceController.php
namespace App\Controller;

use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class PreferenceController extends AbstractController
{
    #[Route('/changer-theme', name: 'changer_theme')]
    public function changerTheme(Request $request): Response
    {
        // Lire le thème actuel
        $themeActuel = $request->cookies->get('theme_preference', 'light');

        // Alterner entre light et dark
        $nouveauTheme = $themeActuel === 'light' ? 'dark' : 'light';

        // Créer une réponse de redirection
      $url = $request->headers->get('referer') ?? $this->generateUrl('app_home');
$response = $this->redirect($url);


        // Créer un cookie qui dure 1 an
        $cookie = Cookie::create('theme_preference')
            ->withValue($nouveauTheme)
            ->withExpires(strtotime('+1 year'))
            ->withPath('/');

        // Ajouter le cookie dans la réponse
        $response->headers->setCookie($cookie);

        return $response;
    }
}
