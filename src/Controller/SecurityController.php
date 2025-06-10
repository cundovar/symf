<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{
    #[Route(path: '/login', name: 'login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        // $error contiendra le message d'erreur si une erreur d'authentification est survenue
        $error = $authenticationUtils->getLastAuthenticationError();

        // $lastUsername contiendra le dernier nom d'utilisateur saisi par l'utilisateur via la méthode getLastUsername()
        $lastUsername = $authenticationUtils->getLastUsername();
          // lastUsername et error seront transmis à la vue
        return $this->render('security/login.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error,
        ]);
    }

    #[Route(path: '/logout', name: 'logout')]
    public function logout(): void
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }
}
