<?php

// Le namespace déclare le "chemin" du fichier dans l'application
namespace App\Form;

// On importe les entités utilisées dans le formulaire
use App\Entity\Category; // Entité liée à la table "category"
use App\Entity\Produit;  // Entité principale modifiée par ce formulaire

// Ce type spécial permet de créer une liste déroulante avec des entités Doctrine
use Symfony\Bridge\Doctrine\Form\Type\EntityType;

// Classe de base à étendre pour créer un formulaire Symfony
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
// Interface utilisée pour construire le formulaire champ par champ
use Symfony\Component\Form\FormBuilderInterface;

// Classe permettant de configurer les options du formulaire (comme l'entité liée)
use Symfony\Component\OptionsResolver\OptionsResolver;

// Déclaration de la classe du formulaire
class ProductClassForm extends AbstractType
{
    /**
     * Méthode principale pour construire les champs du formulaire.
     * Symfony appelle automatiquement cette méthode.
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        // $builder est l'objet qui nous permet d'ajouter les champs un à un
        $builder
            // === Champs simples : Symfony choisit le bon type automatiquement ===
            ->add('nom')         // Champ texte (input type="text")
            ->add('description') // Champ textarea (zone de texte)
            ->add('stock')       // Champ nombre (input type="number")
           ->add('img', FileType::class, [
                       'label' => "Image (fichier)",
                       'required' => false,
                       'mapped' => false // ⚠️ Important si l’image est traitée manuellement dans le contrôleur
])                   
            ->add('prix')        // Champ nombre ou texte selon le contexte





            // === Champ spécial : catégorie liée au produit ===
            // Ce champ représente une relation entre Produit et Category
            // Dans l'entité Produit, tu as probablement : 
            // #[ORM\ManyToOne(targetEntity: Category::class)]
            // private ?Category $category = null;
            
            ->add('category', EntityType::class, [
                // Indique à Symfony que les choix viennent de l'entité Category
                'class' => Category::class,

                // 'choice_label' précise quelle propriété de l'objet Category sera affichée dans <option>
                'choice_label' => 'name', // ex : <option>Chaussures</option>

                // Symfony comprend qu’il faut générer un <select> car on utilise EntityType
                // Doctrine va automatiquement charger toutes les catégories de la base
                // et Symfony les affichera sous forme de <select><option>...</option></select>
            ])
        ;
    }

    /**
     * Cette méthode configure les options du formulaire.
     * Elle dit à Symfony à quelle entité ce formulaire est lié.
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        // L'option 'data_class' permet à Symfony de savoir que ce formulaire sert à modifier un Produit
        // Grâce à ça, Symfony pourra automatiquement remplir l’objet Produit
        // avec les données du formulaire (hydration)
        $resolver->setDefaults([
            'data_class' => Produit::class,
        ]);
    }
}
//  Qu’est-ce que OptionsResolver dans un formulaire Symfony ?
// 👉 C’est un outil qui permet de définir les options qu’un formulaire attend ou peut accepter.
// Tu peux l’imaginer comme un guide de configuration du formulaire :

// "Voici les réglages de base que Symfony doit connaître pour ce formulaire."