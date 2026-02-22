<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email', EmailType::class, [
                'label' => 'Email',
                'constraints' => [
                    new NotBlank(['message' => 'Email is required']),
                    new Email(['message' => 'Invalid email']),
                ],
            ])
            ->add('firstname', TextType::class, [
                'label' => 'First Name',
                'constraints' => [
                    new NotBlank(['message' => 'First name is required']),
                ],
            ])
            ->add('lastname', TextType::class, [
                'label' => 'Last Name',
                'constraints' => [
                    new NotBlank(['message' => 'Last name is required']),
                ],
            ])
            ->add('roles', ChoiceType::class, [
                'label' => 'Roles',
                'choices' => [
                    'User' => 'ROLE_USER',
                    'Manager' => 'ROLE_MANAGER',
                    'Admin' => 'ROLE_ADMIN',
                ],
                'multiple' => true,
                'expanded' => true,
            ]);

        // Add password field only for new users
        if (!$options['edit_mode']) {
            $builder->add('plainPassword', PasswordType::class, [
                'label' => 'Password',
                'mapped' => false,
                'constraints' => [
                    new NotBlank(['message' => 'Password is required']),
                    new Length(min: 6, minMessage: 'Password must be at least 6 characters long'),
                ],
            ]);
        } else {
            $builder->add('plainPassword', PasswordType::class, [
                'label' => 'New Password (leave blank to keep current)',
                'mapped' => false,
                'required' => false,
                'constraints' => [
                    new Length(min: 6, minMessage: 'Password must be at least 6 characters long'),
                ],
            ]);
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
            'edit_mode' => false,
        ]);
    }
}
