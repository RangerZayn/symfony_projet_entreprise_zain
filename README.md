# Backoffice Animalerie - Symfony 7.4

Système complet de gestion de backoffice pour une animalerie (pet store), avec gestion des utilisateurs, produits et clients.

**Prérequis:** PHP >=8.2, Composer, MySQL/MariaDB

## Installation

### 1. Cloner le projet

```bash
git clone <repository-url>
cd symfony_projet_entreprise_zain
```

### 2. Installer les dépendances

```bash
composer install
```

### 3. Configurer la base de données

Créer un fichier `.env.local` à la racine du projet :

```env
DATABASE_URL="mysql://root:password@127.0.0.1:3306/app?serverVersion=10.11&charset=utf8mb4"
```

Ou si vous utilisez XAMPP sans mot de passe :

```env
DATABASE_URL="mysql://root:@127.0.0.1:3306/app?serverVersion=10.11&charset=utf8mb4"
```

### 4. Créer la base de données et exécuter les migrations

```bash
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate
```

### 5. Charger les données de test

```bash
php bin/console doctrine:fixtures:load
```

### 6. Builder les assets Tailwind

```bash
php bin/console tailwind:build
```

### 7. Accéder au projet

Ouvrir votre navigateur et accéder à **http://symfony_base.local**

## Comptes de test

Trois utilisateurs sont créés automatiquement via les fixtures :

- **Administrateur** : `admin@example.com` / `adminpassword` (ROLE_ADMIN)
- **Manager** : `manager@example.com` / `managerpassword` (ROLE_MANAGER)
- **Utilisateur** : `user@example.com` / `userpassword` (ROLE_USER)

Libre aussi à vous d'en créer d'autres.

## Fonctionnalités implémentées

### 👤 Gestion des Utilisateurs
- Liste, création, modification et suppression d'utilisateurs
- Attribution de rôles (ADMIN, MANAGER, USER)
- Hachage sécurisé des mots de passe
- Validation des emails uniques
- Accès réservé aux administrateurs

### 📦 Gestion des Produits
- Catalogue complet avec liste paginée
- Création multi-étapes selon le type de produit (physique/digital)
- Édition avec conservation du type de produit
- Suppression avec confirmation
- Export CSV de tous les produits
- Import de produits via fichier CSV
- Validation des prix (avec seuils de confirmation)
- Accès disponible à tous les utilisateurs

### 👥 Gestion des Clients (Animalerie)
- Liste complète des clients avec tri par nom
- Création de nouveaux clients avec validation complète
- Modification des informations clients
- Suppression de clients
- Validation des emails uniques
- Formatage automatique des numéros de téléphone (+33)
- Historique de création (date/heure)
- Accès réservé aux managers et administrateurs

### 🔐 Sécurité et Contrôle d'accès
- Système de voter personnalisé pour chaque module
- Authentification par email et mot de passe
- Rôles granulaires (ADMIN, MANAGER, USER)
- Permissions basées sur les rôles et les voters
- Données utilisateur affichées dans l'en-tête

### 🎨 Interface utilisateur
- Tableau de bord centralisé avec statistiques rapides
- Barre latérale responsive avec navigation par rôle
- Formulaires validés côté client et serveur
- Messages d'erreur en rouge avec contraintes visuelles
- Styling cohérent avec Tailwind CSS v4.1.11
- Indicateurs visuels pour les actions (création, modification, suppression)

### 🛠️ Outils CLI

#### Créer un client interactif
```bash
php bin/console app:client:create
```

#### Importer des produits depuis CSV
```bash
php bin/console app:product:import chemin_du_fichier/produits.csv
```

Format CSV attendu :
```
id,name,description,price
id1,Produit 1,Description du produit,29.99
id2,Produit 2,Autre description,49.99
```

## Structure du projet

```
src/
├── Controller/        # Contrôleurs (Users, Products, Clients, Security)
├── Entity/           # Entités Doctrine (User, Product, Client)
├── Form/            # Types de formulaire
├── Repository/      # Repositories personnalisés
├── Security/Voter/  # Voters pour le contrôle d'accès
├── Command/         # Commandes CLI
├── Service/         # Services métier (ProductCsvExporter)
└── DataFixtures/    # Données de test
templates/
├── base.html.twig           # Layout principal
├── user/                    # Templates utilisateurs
├── product/                 # Templates produits
├── client/                  # Templates clients
└── security/                # Pages d'authentification
```

## Permissions par rôle

| Fonctionnalité | ROLE_USER | ROLE_MANAGER | ROLE_ADMIN |
|---|:---:|:---:|:---:|
| Voir le dashboard | ✅ | ✅ | ✅ |
| Voir les produits | ✅ | ✅ | ✅ |
| Créer/modifier/suppimer produits | ❌ | ❌ | ✅ |
| Exporter produits CSV | ❌ | ❌ | ✅ |
| Voir les clients | ❌ | ✅ | ✅ |
| Créer/modifier clients | ❌ | ✅ | ✅ |
| Supprimer clients | ❌ | ❌ | ✅ |
| Voir/créer utilisateurs | ❌ | ❌ | ✅ |
| Modifier/supprimer utilisateurs | ❌ | ❌ | ✅ |

## Technologies utilisées

- **Framework** : Symfony 7.4
- **Base de données** : MySQL/MariaDB avec Doctrine ORM
- **Frontend** : Tailwind CSS v4.1.11
- **JavaScript** : Stimulus pour l'interactivité
- **Validation** : Symfony Validator avec contraintes personnalisées
- **Sécurité** : Voters, hachage bcrypt, CSRF tokens

## Démonstration

Une vidéo intitulée **demonstartion.mp4** se trouvant à la racine du projet vous permet de voir l'aperçu rapide du backoffice.

## Documentation supplémentaire

- [Symfony Documentation](https://symfony.com/doc/7.4/index.html)
- [Doctrine ORM](https://www.doctrine-project.org/)
- [Tailwind CSS](https://tailwindcss.com/)

---

AIT BAMMOU Zain-Alabaidine BUT3 Informatique - Groupe 2 RA