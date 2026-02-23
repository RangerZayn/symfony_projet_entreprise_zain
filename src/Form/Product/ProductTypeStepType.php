<?php

namespace App\Form\Product;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProductTypeStepType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('product_type', ChoiceType::class, [
            'label' => 'Sélectionnez le type de produit',
            'help' => 'Choisissez si votre produit est physique (livraison nécessaire) ou numérique (téléchargeable)',
            'choices' => [
                '📦 Produit Physique (avec logistique)' => 'physical',
                '💾 Produit Numérique (licence/accès)' => 'digital',
            ],
            'expanded' => true,
            'multiple' => false,
            'attr' => ['class' => 'space-y-6'],
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => null,
        ]);
    }
}
