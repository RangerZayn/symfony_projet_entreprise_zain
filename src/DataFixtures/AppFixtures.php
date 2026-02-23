<?php

namespace App\DataFixtures;

use App\Entity\User;
use App\Entity\Product;
use App\Entity\Client;
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

        // Create pet store products
        $petProducts = [
            [
                'name' => 'Croquettes Premium Chat Adulte',
                'description' => 'Croquettes haut de gamme pour chat adulte avec viande de poisson. Formule complète et équilibrée avec vitamines et minéraux. Sac de 2kg.',
                'price' => 24.99,
                'type' => 'physical',
            ],
            [
                'name' => 'Panier Confortable pour Chien',
                'description' => 'Panier rembourré en mousse haute densité pour petit et moyen chien. Dimensions: 80x60cm. Lavable en machine.',
                'price' => 45.50,
                'type' => 'physical',
            ],
            [
                'name' => 'Aquarium LED 120L',
                'description' => 'Aquarium complet avec filtration, chauffage et éclairage LED 24h. Capacité: 120 litres. Parfait pour poissons tropicaux.',
                'price' => 189.99,
                'type' => 'physical',
            ],
            [
                'name' => 'Collier GPS pour Chat/Chien',
                'description' => 'Collier de suivi GPS waterproof avec batterie 5 jours. Application mobile pour localisation en temps réel. Compatible tous colliers.',
                'price' => 2500.00,
                'type' => 'physical',
            ],
            [
                'name' => 'Jouets Interactifs pour Lapin',
                'description' => 'Lot de 5 jouets en bois naturel et matière tissée pour stimuler l\'activité du lapin. Sécurisé et 100% naturel.',
                'price' => 19.99,
                'type' => 'physical',
            ],
            [
                'name' => 'Guide Complet du Soigneur Animalier',
                'description' => 'Ouvrage de référence sur le soin des animaux domestiques. PDF téléchargeable + accès vidéos exclusives. Format numérique.',
                'price' => 12.99,
                'type' => 'digital',
            ],
            [
                'name' => 'Licence Formation Eleveur Certifié',
                'description' => 'Certification numérique pour éleveurs animaliers. Cours complets, documents, certificat reconnu. Accès illimité.',
                'price' => 299.00,
                'type' => 'digital',
            ],
            [
                'name' => 'Brosse de Toilettage Premium Chien',
                'description' => 'Brosse double face avec picots en acier inoxydable. Ergonomique et confortable. Idéale pour tous types de pelage.',
                'price' => 34.99,
                'type' => 'physical',
            ],
            [
                'name' => 'Cage Oiseaux Spacieuse',
                'description' => 'Grande cage pour oiseaux avec deux portes, perchoirs et mangeoires. Dimensions: 92x60x58cm. Acier robuste.',
                'price' => 89.50,
                'type' => 'physical',
            ],
            [
                'name' => 'Logiciel Gestion Clinique Vétérinaire',
                'description' => 'Logiciel professionnel de gestion des animaux patients. Dossiers numériques, facturation, rappels rendez-vous. Licence annuelle.',
                'price' => 1500.00,
                'type' => 'digital',
            ],
        ];

        foreach ($petProducts as $productData) {
            $product = new Product();
            $product->setName($productData['name']);
            $product->setDescription($productData['description']);
            $product->setPrice($productData['price']);
            $product->setProductType($productData['type']);
            $manager->persist($product);
        }

        // Create pet store clients
        $clients = [
            [
                'firstname' => 'Marie',
                'lastname' => 'Fontaine',
                'email' => 'marie.fontaine@email.com',
                'phone' => '+33612345678',
                'address' => '42 Rue des Animaux, 75015 Paris',
            ],
            [
                'firstname' => 'Jean-Louis',
                'lastname' => 'Dupont',
                'email' => 'jean.dupont@email.com',
                'phone' => '+33798765432',
                'address' => '10 Avenue du Chat, 13000 Marseille',
            ],
            [
                'firstname' => 'Sophie',
                'lastname' => 'Martin',
                'email' => 'sophie.martin@email.com',
                'phone' => '+33645678901',
                'address' => '25 Boulevard de la Faune, 69000 Lyon',
            ],
            [
                'firstname' => 'Pierre',
                'lastname' => 'Bernard',
                'email' => 'pierre.bernard@email.com',
                'phone' => '+33723456789',
                'address' => '99 Route du Chien, 59000 Lille',
            ],
            [
                'firstname' => 'Isabelle',
                'lastname' => 'Laurent',
                'email' => 'isabelle.laurent@email.com',
                'phone' => '+33689012345',
                'address' => '15 Impasse de l\'Oiseau, 33000 Bordeaux',
            ],
            [
                'firstname' => 'François',
                'lastname' => 'Rousseau',
                'email' => 'francois.rousseau@email.com',
                'phone' => '+33756789012',
                'address' => '77 Chemin du Lapin, 31000 Toulouse',
            ],
            [
                'firstname' => 'Nathalie',
                'lastname' => 'Garnier',
                'email' => 'nathalie.garnier@email.com',
                'phone' => '+33634567890',
                'address' => '8 Place du Poisson, 06000 Nice',
            ],
            [
                'firstname' => 'Cédric',
                'lastname' => 'Mercier',
                'email' => 'cedric.mercier@email.com',
                'phone' => '+33712345678',
                'address' => '50 Square du Hamster, 67000 Strasbourg',
            ],
            [
                'firstname' => 'Valérie',
                'lastname' => 'Petit',
                'email' => 'valerie.petit@email.com',
                'phone' => '+33678901234',
                'address' => '33 Rue de la Tortue, 44000 Nantes',
            ],
            [
                'firstname' => 'Michel',
                'lastname' => 'Lefevre',
                'email' => 'michel.lefevre@email.com',
                'phone' => '+33790123456',
                'address' => '22 Avenue du Perroquet, 38000 Grenoble',
            ],
        ];

        foreach ($clients as $clientData) {
            $client = new Client();
            $client->setFirstname($clientData['firstname']);
            $client->setLastname($clientData['lastname']);
            $client->setEmail($clientData['email']);
            $client->setPhoneNumber($clientData['phone']);
            $client->setAddress($clientData['address']);
            $manager->persist($client);
        }

        $manager->flush();
    }
}
