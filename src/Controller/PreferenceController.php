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
        // recuperation depuis le cookie theme_preference
        $themeActuel = $request->cookies->get('theme_preference', 'light');

        // Alterner entre light et dark
        $nouveauTheme = $themeActuel === 'light' ? 'dark' : 'light';

        // Créer une réponse de redirection
        // recuperation de la page precedente ou page d'accueil
         // quand utiliser request->headers : quand on veut recuperer des informations de la requete HTTP
         // difference entre $request->headers et $request->request :
         // $request->headers c'est un objet qui contient toutes les informations de la requête HTTP (données du formulaire, méthode utilisée, etc.)
         // $request->request c'est un objet qui contient toutes les informations du formulaire
        
        
        // get('referer') c'est la page precedente
      $url = $request->headers->get('referer') ?? $this->generateUrl('app_home');
      // redirection vers la page precedente ou page d'accueil
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
