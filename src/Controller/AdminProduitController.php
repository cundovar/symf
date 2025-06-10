<?php

namespace App\Controller;

use App\Entity\Produit;
use App\Form\ProductClassForm;
use App\Repository\CategoryRepository;
use App\Repository\ProduitRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

// EntityManagerInterface	     Gère les opérations de persistance (sauvegarde, mise à jour, suppression, transaction)
// Repository	                 Gère les opérations de lecture/recherche (find, findBy, findOneBy, etc.)

final class AdminProduitController extends AbstractController
{
    /**
     * Affiche tous les produits avec leurs formulaires d'édition respectifs.
     */
    #[Route('/admin/produit', name: 'app_admin_produit')]
    public function index(Request $request, ProduitRepository $repo, EntityManagerInterface $em): Response


    {

        if (!$this->isGranted('ROLE_ADMIN')) {
            $this->addFlash('error', 'Accès refusé. Vous devez être administrateur.');
            return $this->redirectToRoute('home'); 
        }


        // Récupère tous les produits en base
        $produits = $repo->findAll();

        // Prépare un tableau pour stocker un formulaire par produit
        $formulaires = [];

        foreach ($produits as $produit) {
            // Crée un formulaire d'édition pour chaque produit
            $formulaires[$produit->getId()] = $this->createForm(ProductClassForm::class, $produit)->createView();
        }

        // Rend la vue avec les produits et leurs formulaires
        return $this->render('admin_produit/index.html.twig', [
            'produits' => $produits,
            'formEdit' => $formulaires,
        ]);
    }

    /**
     * Met à jour un produit existant via un formulaire POST.
     */    /**
     * Cette méthode permet de modifier un produit existant dans la base de données.
     * Elle est appelée lorsqu’un formulaire est soumis en POST depuis la page d’admin.
     * 
     * @param Request $request → objet qui contient toutes les informations de la requête HTTP (données du formulaire, méthode utilisée, etc.)
     * @param Produit $produit → objet automatiquement injecté par Symfony (grâce à l’ID passé dans l’URL)
     * @param EntityManagerInterface $em → outil fourni par Doctrine pour modifier la base de données
     * @return Response → retourne une réponse HTTP vers le navigateur
     *  structure simplifier de $request 
     * $request = new Request(
     *$query = $_GET,
     *$request = $_POST,
     *$attributes = [],
     *$cookies = $_COOKIE,
     *$files = $_FILES,
     *$server = $_SERVER
     *;
     * 
     * 
     */
    #[Route('admin/produits/update/{id}', name: 'produit_update', methods: ['POST'])]
    public function update(Request $request, Produit $produit, EntityManagerInterface $em): Response
    {
        if (!$this->isGranted('ROLE_ADMIN')) {
            $this->addFlash('danger', 'Accès refusé. Vous devez être administrateur.');
            return $this->redirectToRoute('home'); 
        }
        // On utilise la méthode createForm() qui vient de la classe AbstractController (héritée par notre contrôleur)
        // Elle sert à créer un objet Form Symfony basé sur une classe de formulaire (ici ProductClassForm)
        // On lui passe 2 arguments : 
        // - ProductClassForm::class → la classe PHP qui décrit le formulaire
        // - $produit → l’objet Produit à modifier (lié au formulaire)
        $form = $this->createForm(ProductClassForm::class, $produit);

        // dd($form->createView());

        // $form est maintenant un objet de type FormInterface, il peut gérer des données entrantes

        // handleRequest() signifie "traite la requête"
        // Il vérifie si le formulaire a été soumis, et s’il y a des données POST dans $request
        // Il va automatiquement remplir l’objet $produit avec les données du formulaire (nom, description, etc.)
        // handleRequest($request)
// Cette méthode prépare le formulaire en le liant à la requête HTTP (GET, POST, etc.)

// Elle fait trois choses :

// Elle regarde si la requête contient des données de formulaire (généralement en POST)

// Si oui, elle remplit automatiquement l’objet lié au formulaire (Produit, User, etc.)

// Elle marque le formulaire comme "soumis" en interne si des données ont été envoyées

// Mais ! 👉 elle ne renvoie rien (pas de true ou false).


        $form->handleRequest($request);

        // On vérifie deux choses :
        // 1. $form->isSubmitted() → le formulaire a été envoyé (via POST)
        // 2. $form->isValid() → les données envoyées respectent les règles définies dans la classe ProductClassForm
//         Cette méthode permet de vérifier après coup si handleRequest() a détecté une soumission.

// Elle retourne :

// true si le formulaire a été envoyé (ex : via POST avec les bons champs)

// false sinon

// Donc handleRequest() prépare, et isSubmitted() vérifie après préparation.


        if ($form->isSubmitted() && $form->isValid()) {

            // Ici on prépare la gestion de l'image envoyée via le champ "img" du formulaire

            // On déclare une variable avec une annotation spéciale pour aider PHP :
            /** @var UploadedFile|null $imageFile */
            // Cela signifie que $imageFile peut contenir soit :
            // - un objet UploadedFile (fichier envoyé)
            // - ou null (aucun fichier envoyé)

            // $form->get('img') → on accède au champ "img" du formulaire
            // ->getData() → on récupère la valeur (c’est un fichier dans ce cas)
            $imageFile = $form->get('img')->getData();

            // On vérifie si un fichier image a été envoyé (non nul)
            if ($imageFile) {
                // uniqid() → fonction PHP qui génère une chaîne unique (ex : 656b3ef2c6a9b)
                // $imageFile->guessExtension() → devine automatiquement l’extension (jpg, png, etc.)
                // Le point (.) sert à concaténer les deux chaînes pour former un nom de fichier complet
                $newFilename = uniqid() . '.' . $imageFile->guessExtension();

                try {
                    // $this->getParameter() → méthode Symfony pour lire un paramètre défini dans services.yaml
                    // Ici, on lit la valeur de "images_directory" (chemin vers le dossier public/images)

                    // move() → méthode de l’objet UploadedFile
                    // Elle déplace le fichier depuis le dossier temporaire vers le bon dossier sur le serveur
                    $imageFile->move(
                        $this->getParameter('images_directory'), // Dossier de destination
                        $newFilename // Nom du fichier à enregistrer
                    );

                    // $produit->setImg($newFilename)
                    // On met à jour la propriété "img" du produit avec le nom du nouveau fichier
                    $produit->setImg($newFilename);
                } catch (FileException $e) {
                    // Si une erreur se produit lors du déplacement du fichier, on affiche un message temporaire à l’utilisateur
                    // addFlash() est une méthode de Symfony pour afficher des messages dans les vues
                    $this->addFlash('error', 'Erreur lors du téléchargement de l\'image.');
                }
            }

            // $em->flush() → dit à Doctrine d’écrire toutes les modifications en base de données
            // Il regarde tous les objets qui ont été modifiés (ex : $produit) et les met à jour en SQL
            $em->flush();
            // persist() n'est pas necessaire ici car Doctrine a deja l'objet $produit
        }

        // Après la modification, on redirige l’utilisateur vers la page principale d’administration
        // On utilise du JavaScript car la réponse est peut-être dans une modale
        return new Response('<script>window.location.href="' . $this->generateUrl('app_admin_produit') . '";</script>');
    }


    /**
     * Crée un nouveau produit à partir d’un formulaire HTML classique (hors Symfony Form).
     */
    #[Route('/admin/produit/new', name: 'produit_new_manual')]
    public function newManual(Request $request, EntityManagerInterface $em, CategoryRepository $categoryRepo): Response
    {
        if (!$this->isGranted('ROLE_ADMIN')) {
            $this->addFlash('danger', 'Accès refusé. Vous devez être administrateur.');
            return $this->redirectToRoute('home'); 
        }

        dump(['Méthode' => get_class_methods($request) ]);
        dump(['Méthode' => get_class_methods(AbstractController::class) ]);
            // dans cette methode je ne creer pas de formulaire je creer un produit directement


        // Vérifie que la requête est en POST et que tous les champs nécessaires sont présents
        if (
            $request->isMethod('POST') &&
            $request->request->get('nom') &&
            $request->request->get('description') &&
            $request->request->get('prix') &&
            $request->request->get('category') &&
            $request->request->get('stock')
        ) {
            $produit = new Produit();
            $produit->setNom($request->request->get('nom'));
            $produit->setDescription($request->request->get('description'));
            $produit->setPrix((float)$request->request->get('prix'));
            $produit->setStock((int)$request->request->get('stock'));

            // Gestion de l'image envoyée
            /** @var UploadedFile|null $imageFile */
            $imageFile = $request->files->get('img');
            if ($imageFile) {
                // nous creons un variable $newFilename qui contiendra le nom du fichier de l'image
                //uniqid() → fonction PHP qui génère une chaîne unique (ex : 656b3ef2c6a9b)
                // $imageFile->guessExtension() → devine automatiquement l’extension (jpg, png, etc.)
                // Le point (.) sert à concaténer les deux chaînes pour former un nom de fichier complet
                $newFilename = uniqid() . '.' . $imageFile->guessExtension();
                try {
                    $imageFile->move(
                        $this->getParameter('images_directory'),
                        $newFilename
                    );
                    $produit->setImg($newFilename);
                } catch (FileException $e) {
                    $this->addFlash('danger', 'Erreur lors du téléchargement de l\'image.');
                }
            }

            // Liaison du produit avec la catégorie choisie
            $category = $categoryRepo->find($request->request->get('category'));
            $produit->setCategory($category);

            // Sauvegarde du produit

            //persist() prépare Doctrine à gérer un nouvel objet (ex : un nouveau produit qui n’existe pas encore en base).

            // Mais attention :

            // persist() ne fait rien tout seul.

            // Il faut obligatoirement appeler flush() après pour que Doctrine exécute la requête SQL INSERT.
            $em->persist($produit);
            $em->flush();

            $this->addFlash('success', 'Produit ajouté avec succès !');

            return $this->redirectToRoute('produit_new_manual');
        }

        // Si le formulaire n’a pas encore été soumis, on affiche le formulaire
        $categories = $categoryRepo->findAll();

        return $this->render('admin_produit/new.html.twig', [
            'categories' => $categories,
        ]);
    }

    /**
     * Supprime un produit et son image associée.
     */
    #[Route('/admin/produit/delete/{id}', name: 'produit_delete', methods: ['POST'])]
    public function delete(Produit $produit, EntityManagerInterface $em): Response
    {
        if (!$this->isGranted('ROLE_ADMIN')) {
            $this->addFlash('danger', 'Accès refusé. Vous devez être administrateur.');
            return $this->redirectToRoute('home'); 
        }
        // Récupère le nom de l’image
        $image = $produit->getImg();

        if ($image) {
            $imagePath = $this->getParameter('images_directory') . '/' . $image;

            // Supprime le fichier image du système de fichiers
            if (file_exists($imagePath)) {
                unlink($imagePath);
            }
        }

        // Supprime l'entité Produit de la base de données
        $em->remove($produit);
        $em->flush();

        $this->addFlash('success', 'Produit supprimé avec succès.');

        return $this->redirectToRoute('app_admin_produit');
    }
}



// 💬 Comment expliquer ça à des apprenants débutants
// Tu peux dire :

// 🧑‍🏫 "Dans Symfony, un formulaire sert à deux choses différentes :

// D'abord, on doit le préparer pour l'afficher à l’écran avec les champs déjà remplis (dans index()).

// Ensuite, quand l’utilisateur clique sur 'Valider', Symfony récupère ce formulaire (dans update()) pour modifier l'objet lié.

// C’est pour ça qu’on voit le formulaire à deux endroits : une fois pour l'afficher, une fois pour le traiter."**

// 🎯 L’idée clé à transmettre
// 🔁 Symfony ne peut pas deviner tout seul quel formulaire va être affiché ou soumis.
// On doit préparer le formulaire dans une fonction, puis le traiter dans une autre.

// 📦 Avec une analogie simple
// ✉️ Imagine que tu reçois un formulaire papier à remplir :

// Le prof imprime le formulaire (c’est index() → affichage)

// L’élève le remplit et rend le formulaire (c’est update() → traitement)

// 🛠 Exemple à leur montrer
// php
// Copier le code
// // Dans index() : on prépare les formulaires pour les afficher
// foreach ($produits as $produit) {
//     $formulaires[$produit->getId()] = $this->createForm(ProductClassForm::class, $produit)->createView();
// }
// php
// Copier le code
// // Dans update() : on reprend le produit lié à l’ID, et on lui applique les nouvelles données du formulaire
// $form = $this->createForm(ProductClassForm::class, $produit);
// $form->handleRequest($request);
