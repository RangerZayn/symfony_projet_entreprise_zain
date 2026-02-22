<?php

namespace App\Form\Product;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\EqualTo;

class ProductPriceConfirmationStepType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('price_confirmed', CheckboxType::class, [
                'label' => '⚠️  Je confirme que le prix de ce produit est correct et désiré',
                'help' => 'Ce produit a un prix élevé (≥ 1000€). Veuillez confirmer que ce prix est intentionnel.',
                'required' => true,
                'constraints' => [
                    new EqualTo([
                        'value' => true,
                        'message' => 'Vous devez confirmer le prix avant de continuer',
                    ]),
                ],
                'attr' => [
                    'class' => 'mr-2 w-4 h-4',
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => null]);
    }
}
