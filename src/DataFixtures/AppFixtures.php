<?php

namespace App\DataFixtures;

use App\Entity\User;
use App\Entity\Product;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    private UserPasswordHasherInterface $passwordHasher;

    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
    }

    public function load(ObjectManager $manager): void
    {
        // Create Admin User
        $admin = new User();
        $admin->setEmail('admin@example.com');
        $admin->setFirstname('Admin');
        $admin->setLastname('User');
        $admin->setRoles(['ROLE_ADMIN']);
        $hashedPassword = $this->passwordHasher->hashPassword($admin, 'adminpassword');
        $admin->setPassword($hashedPassword);
        $manager->persist($admin);

        // Create Manager User
        $managerUser = new User();
        $managerUser->setEmail('manager@example.com');
        $managerUser->setFirstname('Manager');
        $managerUser->setLastname('User');
        $managerUser->setRoles(['ROLE_MANAGER']);
        $hashedPassword = $this->passwordHasher->hashPassword($managerUser, 'managerpassword');
        $managerUser->setPassword($hashedPassword);
        $manager->persist($managerUser);

        // Create Standard User
        $user = new User();
        $user->setEmail('user@example.com');
        $user->setFirstname('Standard');
        $user->setLastname('User');
        $user->setRoles(['ROLE_USER']);
        $hashedPassword = $this->passwordHasher->hashPassword($user, 'userpassword');
        $user->setPassword($hashedPassword);
        $manager->persist($user);

        // Create some products
        for ($i = 0; $i < 10; $i++) {
            $product = new Product();
            $product->setName('Pet Product ' . $i);
            $product->setDescription('This is a great product for your pet!');
            $product->setPrice(mt_rand(10, 100));
            $manager->persist($product);
        }

        $manager->flush();
    }
}
