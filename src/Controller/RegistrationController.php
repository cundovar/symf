<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationForm;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

class RegistrationController extends AbstractController
{
    // route qui affiche dans l'URL et sont nom pour l'appeler dans les vues
    #[Route('/inscription', name: 'inscription')]
    // Request $request : représente les requetes HTTP (GET POST PUT DELETE)
    // UserPasswordHasherInterface $userPasswordHasher: représente le hashage des mots de passes
    // EntityManagerInterface $entityManager : PERMET DE SAUVARDER LES ENTITÉ DANS LA BASE DE DONNEES





    public function register(Request $request, UserPasswordHasherInterface $userPasswordHasher, EntityManagerInterface $entityManager): Response
    {

        //  creation d'un objet user vide 
        $user = new User();
        // ici on appell le formulaire 
        // creation du formulaire 
        // RegistrationForm::class   class qui definila structure  du formulaire

        // $user : l'objet auquel les champs du formulaire seront attacher
        // symfony relie automatiquement  les champs avec les get setter de lobjet $user
        // exemple champs "email"=> $user->setEmail($email)

        $form = $this->createForm(RegistrationForm::class, $user);

        // analyse la requete HTTP 

        // -> si POST  -> on recupere les donnees du formulaire
        // -> si GET -> on affiche le formulaire


        $form->handleRequest($request);


        // verifie si le formulaire a ete soumis et  valide selon les contrinte re registerForm
        if ($form->isSubmitted() && $form->isValid()) {

            // champs temporaire uniquement pour le hashage du mot de passe
            /** @var string $plainPassword */
            $plainPassword = $form->get('plainPassword')->getData();
             

            // encode the plain password
            $user->setPassword($userPasswordHasher->hashPassword($user, $plainPassword));

            $entityManager->persist($user); // prepare l'enregitrement de l'objet
            $entityManager->flush(); // execute la requete SQL pour inserern l'objet à la base 

            // do anything else you need here, like send an email

            return $this->redirectToRoute('login'); // redirection vers la page de login
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form,
         
        ]);
    }
}
