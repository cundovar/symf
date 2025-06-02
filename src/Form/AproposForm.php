<?php

namespace App\Form;

use App\Entity\Apropos;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AproposForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('titre', null, [
                'label' => 'Titre',
                'row_attr' => ['class' => 'mb-4'],
                'label_attr' => ['class' => 'block text-gray-700 font-bold mb-2'],
                'attr' => [
                    'class' => 'shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline',
                    'placeholder' => 'Entrez un titre',
                ],
            ])
            ->add('texte', TextareaType::class, [
                'label' => 'Texte principal',
                'row_attr' => ['class' => 'mb-4'],
                'label_attr' => ['class' => 'block text-gray-700 font-bold mb-2'],
                'attr' => [
                    'class' => 'shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline',
                    'rows' => 5,
                    'placeholder' => 'Entrez le texte principal',
                ],
            ])
            ->add('texte2', TextareaType::class, [
                'label' => 'Texte secondaire',
                'row_attr' => ['class' => 'mb-4'],
                'label_attr' => ['class' => 'block text-gray-700 font-bold mb-2'],
                'attr' => [
                    'class' => 'shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline',
                    'rows' => 5,
                    'placeholder' => 'Entrez le texte secondaire',
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Apropos::class,
        ]);
    }
}
