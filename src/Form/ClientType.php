<?php

namespace App\Form;

use App\Entity\Client;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

class ClientType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('firstname', TextType::class, [
                'label' => 'Prénom',
            ])
            ->add('lastname', TextType::class, [
                'label' => 'Nom de Famille',
            ])
            ->add('email', EmailType::class, [
                'label' => 'Email',
            ])
            ->add('phoneNumber', TelType::class, [
                'label' => 'Téléphone',
                'required' => true,
            ])
            ->add('address', TextareaType::class, [
                'label' => 'Adresse',
                'required' => true,
            ]);

        // Add transformer for phone number formatting
        $builder->get('phoneNumber')->addModelTransformer(new class implements DataTransformerInterface {
            public function transform($value): mixed
            {
                if (!$value) {
                    return '';
                }
                // Format: +33 6 12 34 56 78
                // Remove all non-digit and + characters
                $cleaned = preg_replace('/[^\d\+]/', '', $value);
                
                // Format with spaces: +33 X XX XX XX XX
                if (preg_match('/^\+(\d{2})(\d)(\d{2})(\d{2})(\d{2})(\d{2})$/', $cleaned, $matches)) {
                    return '+' . $matches[1] . ' ' . $matches[2] . ' ' . $matches[3] . ' ' . $matches[4] . ' ' . $matches[5] . ' ' . $matches[6];
                }
                return $cleaned;
            }

            public function reverseTransform($value): mixed
            {
                if (!$value) {
                    return null;
                }
                // Remove all spaces and special chars, keep only digits and +
                $cleaned = preg_replace('/[^\d\+]/', '', $value);

                // Format to +33 if it starts with 0
                if (preg_match('/^0/', $cleaned)) {
                    $cleaned = '+33' . substr($cleaned, 1);
                }
                // Add +33 prefix if it doesn't have it
                elseif (!preg_match('/^\+/', $cleaned)) {
                    $cleaned = '+33' . $cleaned;
                }

                return $cleaned;
            }
        });
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Client::class,
        ]);
    }
}
