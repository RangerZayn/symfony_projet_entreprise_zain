<?php

namespace App\Form\Product;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Positive;

class ProductLicenseStepType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('license_key', TextType::class, [
                'label' => 'Clé de licence',
                'help' => 'Clé d\'activation ou de licence unique pour le produit numérique',
                'required' => false,
                'attr' => [
                    'placeholder' => 'Ex: PROD-2024-XXXXX',
                    'class' => 'w-full px-4 py-2 border border-gray-300 rounded',
                ],
            ])
            ->add('max_users', IntegerType::class, [
                'label' => 'Nombre d\'utilisateurs maximum',
                'help' => 'Nombre maximum de postes d\'utilisateurs autorisés avec une seule licence (laisser vide pour illimité)',
                'required' => false,
                'constraints' => [
                    new Positive(['message' => 'Le nombre doit être positif']),
                ],
                'attr' => [
                    'placeholder' => 'Ex: 5',
                    'class' => 'w-full px-4 py-2 border border-gray-300 rounded',
                    'min' => '1',
                ],
            ])
            ->add('download_url', TextType::class, [
                'label' => 'URL de téléchargement',
                'help' => 'Lien de téléchargement ou d\'accès au produit numérique',
                'required' => false,
                'attr' => [
                    'placeholder' => 'https://...',
                    'class' => 'w-full px-4 py-2 border border-gray-300 rounded',
                    'type' => 'url',
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => null]);
    }
}
