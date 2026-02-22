<?php

namespace App\Form\Product;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Positive;

class ProductLogisticsStepType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('weight', TextType::class, [
                'label' => 'Poids (kg)',
                'help' => 'Poids du produit en kilogrammes pour le calcul de frais de port',
                'required' => false,
                'attr' => [
                    'placeholder' => 'Ex: 2.5',
                    'class' => 'w-full px-4 py-2 border border-gray-300 rounded',
                ],
            ])
            ->add('dimensions', TextType::class, [
                'label' => 'Dimensions (L × l × h)',
                'help' => 'Dimensions du produit en centimètres. Format: 30 × 20 × 10',
                'required' => false,
                'attr' => [
                    'placeholder' => 'Ex: 30 × 20 × 10',
                    'class' => 'w-full px-4 py-2 border border-gray-300 rounded',
                ],
            ])
            ->add('stock', IntegerType::class, [
                'label' => 'Quantité en stock',
                'help' => 'Nombre d\'unités actuellement disponibles',
                'required' => false,
                'constraints' => [
                    new Positive(['message' => 'Le stock doit être positif']),
                ],
                'attr' => [
                    'placeholder' => '0',
                    'class' => 'w-full px-4 py-2 border border-gray-300 rounded',
                    'min' => '0',
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => null]);
    }
}
