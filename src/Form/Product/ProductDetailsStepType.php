<?php

namespace App\Form\Product;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\Positive;

class ProductDetailsStepType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Nom du produit',
                'help' => 'Nom unique et descriptif du produit',
                'constraints' => [
                    new NotBlank(['message' => 'Le nom du produit est obligatoire']),
                    new Length([
                        'min' => 3,
                        'max' => 255,
                        'minMessage' => 'Le nom doit faire au moins 3 caractères',
                        'maxMessage' => 'Le nom ne peut pas dépasser 255 caractères',
                    ]),
                ],
                'attr' => [
                    'placeholder' => 'Ex: Chaise ergonomique premium',
                    'class' => 'w-full px-4 py-2 border border-gray-300 rounded',
                ],
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description du produit',
                'help' => 'Description détaillée des caractéristiques et avantages',
                'required' => false,
                'attr' => [
                    'rows' => 4,
                    'placeholder' => 'Décrivez votre produit en détail...',
                    'class' => 'w-full px-4 py-2 border border-gray-300 rounded',
                ],
            ])
            ->add('price', MoneyType::class, [
                'label' => 'Prix (EUR)',
                'help' => 'Prix unitaire en euros. Un prix ≥ 1000€ nécessitera une confirmation.',
                'currency' => 'EUR',
                'constraints' => [
                    new NotBlank(['message' => 'Le prix est obligatoire']),
                    new Positive(['message' => 'Le prix doit être positif']),
                ],
                'attr' => [
                    'placeholder' => '0.00',
                    'class' => 'w-full px-4 py-2 border border-gray-300 rounded',
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => null]);
    }
}
