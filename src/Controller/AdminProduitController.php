<?php

// Le namespace est comme un dossier virtuel pour organiser ton code PHP
namespace App\Controller;

// On "importe" les classes nécessaires à ce fichier
use App\Entity\Produit; // Représente un produit en base de données
use App\Form\ProductClassForm; // Le formulaire Symfony pour Produit

// Les "Repository" permettent d'accéder aux données depuis la base
use App\Repository\CategoryRepository;
use App\Repository\ProduitRepository;

// EntityManagerInterface = outil principal de Doctrine pour gérer la base (ajouter, modifier, supprimer)
use Doctrine\ORM\EntityManagerInterface;

// Classe Symfony de base pour créer un contrôleur
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
// Request contient toutes les infos de la requête HTTP (formulaire soumis, méthode GET ou POST, URL, etc.)
use Symfony\Component\HttpFoundation\Request;

// Response est ce qu’on retourne au navigateur : une page HTML, une redirection, etc.
use Symfony\Component\HttpFoundation\Response;

// Permet de définir les routes avec des attributs PHP 8+ (nouveau système depuis Symfony 6)
use Symfony\Component\Routing\Attribute\Route;

// On déclare une classe "contrôleur", qui est le coeur d'une page en Symfony
// "final" = personne ne pourra hériter de cette classe plus tard
final class AdminProduitController extends AbstractController
{
    /**
     * Cette méthode affiche la page d'administration des produits
     * Elle affiche tous les produits et prépare un formulaire d'édition vide (ex : dans une modale Bootstrap)
     */
    #[Route('/admin/produit', name: 'app_admin_produit')]
    public function index(Request $request, ProduitRepository $repo, EntityManagerInterface $em): Response
    {
        // $repo est automatiquement injecté par Symfony : c'est le service ProduitRepository
        // On appelle la méthode findAll() avec -> (car $repo est un objet)
        // Cela va chercher tous les produits enregistrés en base de données
        $produits = $repo->findAll();

        // On crée un nouvel objet Produit vide
     

        // On crée un formulaire Symfony basé sur la classe ProductClassForm
        // Le 2e argument est l'objet lié : ici un produit vide
        
        // On prépare un tableau où chaque produit aura son propre formulaire
        $formulaires = [];

        // Pour chaque produit, on génère un formulaire lié à ce produit
        foreach ($produits as $produit) {
            $formulaires[$produit->getId()] = $this->createForm(ProductClassForm::class, $produit)->createView();
        }

        // On passe les produits et les formulaires à la vue Twig
        return $this->render('admin_produit/index.html.twig', [
            'produits' => $produits,
            'formEdit' => $formulaires, // tableau des formulaires par ID de produit
        ]);
        // On retourne une réponse HTML en rendant un fichier Twig (vue)
        // On envoie deux variables à Twig : 
        // - les produits à afficher
        // - le formulaire prêt à être affiché avec form_widget()
    
    }

    /**
     * Cette méthode modifie un produit existant via un formulaire POST
     */
    #[Route('admin/produits/update/{id}', name: 'produit_update', methods: ['POST'])]
    public function update(Request $request, Produit $produit, EntityManagerInterface $em): Response
    {
        // Symfony injecte automatiquement l'objet Produit correspondant à l'ID dans l'URL

        // On crée un formulaire Symfony lié à l'objet Produit existant
        $form = $this->createForm(ProductClassForm::class, $produit);

        // On demande à Symfony de lire les données POST envoyées par le formulaire
        // Il va automatiquement remplir l'objet Produit avec les nouvelles données
        $form->handleRequest($request);
          

 
        // Si le formulaire est bien soumis ET que les données sont valides
        if ($form->isSubmitted() && $form->isValid()) {



            /** @var UploadedFile|null $imageFile */
            $imageFile = $form->get('img')->getData();

            if ($imageFile) {
                // Génère un nom de fichier unique avec extension
                $newFilename = uniqid() . '.' . $imageFile->guessExtension();

                try {
                    // Déplace l’image dans le dossier /public/images/
                    $imageFile->move(
                        $this->getParameter('images_directory'),
                        $newFilename
                    );

                    // Met à jour le nom de l’image dans le produit
                    $produit->setImg($newFilename);
                } catch (FileException $e) {
                    // Message d’erreur si le déplacement échoue
                    $this->addFlash('error', 'Erreur lors du téléchargement de l\'image.');
                }
            }






            // flush() demande à Doctrine d'enregistrer les modifications dans la base de données
            $em->flush();
        }

        // Redirection vers la page d’administration des produits après la modification
        return $this->redirectToRoute('app_admin_produit');
    }

    /**
     * Méthode qui crée un nouveau produit manuellement, sans formulaire Symfony.
     * On lit directement les données envoyées en POST via l’objet Request.
     */
    #[Route('/admin/produit/new', name: 'produit_new_manual')]
    public function newManual(Request $request, EntityManagerInterface $em, CategoryRepository $categoryRepo): Response
    {

        dump($request);

        // Vérifie si :
        // - la requête est de type POST (formulaire soumis)
        // - tous les champs nécessaires sont présents
        if (
            $request->isMethod('POST') && // Vérifie que le formulaire est bien envoyé (en POST)
            $request->request->get('nom') &&           // get('nom') lit la valeur du champ <input name="nom">
            $request->request->get('description') &&
            $request->request->get('prix') &&
            $request->request->get('category')
        ) {
            // On crée un nouveau produit vide
            $produit = new Produit();

            // On remplit l'objet Produit avec les données du formulaire
            // $request->request est un objet de type ParameterBag, qui contient les valeurs envoyées en POST
            $produit->setNom($request->request->get('nom')); // Exemple : <input name="nom" value="Chaise">
            $produit->setDescription($request->request->get('description'));
            $produit->setPrix((float)$request->request->get('prix')); // On convertit en float pour éviter une erreur
            /** @var UploadedFile|null $imageFile */
            $imageFile = $request->files->get('img');
            if ($imageFile) {
                // Génère un nom de fichier unique avec extension
                $newFilename = uniqid() . '.' . $imageFile->guessExtension();
                try {
                    // Déplace l’image dans le dossier /public/images/
                    $imageFile->move(
                        $this->getParameter('images_directory'),
                        $newFilename
                    );
                    // Met à jour le nom de l’image dans le produit
                    $produit->setImg($newFilename);
                } catch (FileException $e) {
                    // Message d’erreur si le déplacement échoue
                    $this->addFlash('error', 'Erreur lors du téléchargement de l\'image.');
                }
            }

            // On récupère l'ID de la catégorie sélectionnée dans le menu déroulant
            $categoryId = $request->request->get('category');

            // On utilise le repository pour chercher la catégorie en base de données
            $category = $categoryRepo->find($categoryId);

            // On associe cette catégorie au produit
            $produit->setCategory($category);

            // Doctrine prépare le produit pour l’enregistrer
            $em->persist($produit);

            // Et Doctrine envoie les données dans la base
            $em->flush();

            // On ajoute un message temporaire (flash) pour dire que l’opération s’est bien passée
            $this->addFlash('success', 'Produit ajouté avec succès !');

            // On redirige vers la même page pour éviter de re-soumettre le formulaire en rechargeant
            return $this->redirectToRoute('produit_new_manual');
        }

        // Si on n’a pas encore soumis le formulaire :
        // On récupère la liste des catégories disponibles pour les afficher dans le <select>
        $categories = $categoryRepo->findAll();

        // On affiche la page Twig du formulaire manuel, avec les catégories à choisir
        return $this->render('admin_produit/new.html.twig', [
            'categories' => $categories,
        ]);
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
