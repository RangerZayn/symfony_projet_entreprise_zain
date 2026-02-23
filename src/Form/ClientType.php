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

        // Ajout d'un DataTransformer pour formater le numéro de téléphone
        $builder->get('phoneNumber')->addModelTransformer(new class implements DataTransformerInterface {
            public function transform($value): mixed
            {
                if (!$value) {
                    return '';
                }
                // Format: +33 6 12 34 56 78
                // Retire tous les espaces et caractères spéciaux, ne garder que les chiffres et le +
                $cleaned = preg_replace('/[^\d\+]/', '', $value);
                
                // Format avec espaces: +33 X XX XX XX XX
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
                // Retire tous les espaces et caractères spéciaux, ne garder que les chiffres et le +
                $cleaned = preg_replace('/[^\d\+]/', '', $value);

                // Format avec +33: si le numéro commence par 0, remplacer par +33
                if (preg_match('/^0/', $cleaned)) {
                    $cleaned = '+33' . substr($cleaned, 1);
                }
                // Ajouter +33 si le numéro commence par 6 ou 7 et ne commence pas déjà par +33
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
