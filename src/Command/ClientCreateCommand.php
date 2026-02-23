<?php

namespace App\Command;

use App\Entity\Client;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[AsCommand(
    name: 'app:client:create',
    description: 'Crée un nouveau client via la ligne de commande',
)]
class ClientCreateCommand extends Command
{
    private EntityManagerInterface $em;
    private ValidatorInterface $validator;

    public function __construct(EntityManagerInterface $em, ValidatorInterface $validator)
    {
        parent::__construct();
        $this->em = $em;
        $this->validator = $validator;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $helper = $this->getHelper('question');

        $output->writeln('<info>Création d\'un nouveau client</info>');
        $output->writeln('');

        // Prénom
        $question = new Question('Prénom: ');
        $firstname = $helper->ask($input, $output, $question);

        // Nom  
        $question = new Question('Nom: ');
        $lastname = $helper->ask($input, $output, $question);

        // Email
        $question = new Question('Email: ');
        $email = $helper->ask($input, $output, $question);

        // Téléphone (requis)
        $question = new Question('Téléphone (format: 0XXXXXXXXX ou +33XXXXXXXXX): ');
        $phone = $helper->ask($input, $output, $question);

        // Address (requis)
        $question = new Question('Adresse: ');
        $address = $helper->ask($input, $output, $question);

        $output->writeln('');

        // Création du client
        $client = new Client();
        $client->setFirstname($firstname);
        $client->setLastname($lastname);
        $client->setEmail($email);
        
        // Formatter le numéro de téléphone avant de le définir
        $formattedPhone = $this->formatPhoneNumber($phone);
        $client->setPhoneNumber($formattedPhone);
        
        $client->setAddress($address);

        // Valider le client
        $errors = $this->validator->validate($client);
        if (count($errors) > 0) {
            $output->writeln('<error>Erreur de validation:</error>');
            foreach ($errors as $error) {
                $output->writeln('  <error>• ' . $error->getMessage() . '</error>');
            }
            return Command::FAILURE;
        }

        // Persister le client en base de données
        try {
            $this->em->persist($client);
            $this->em->flush();
        } catch (\Exception $e) {
            $output->writeln('<error>Erreur lors de la sauvegarde: ' . $e->getMessage() . '</error>');
            return Command::FAILURE;
        }

        $output->writeln('<info>✓ Client créé avec succès!</info>');
        $output->writeln('  ID: ' . $client->getId());
        $output->writeln('  Nom: ' . $client->getFullName());
        $output->writeln('  Email: ' . $client->getEmail());
        $output->writeln('  Téléphone: ' . $client->getPhoneNumber());

        return Command::SUCCESS;
    }

    private function formatPhoneNumber(string $phone): string
    {
        // Retirer tous les caractères non numériques et les +
        $cleaned = preg_replace('/[^\d\+]/', '', $phone);

        // Formatter à +33 si le numéro commence par 0
        if (preg_match('/^0/', $cleaned)) {
            $cleaned = '+33' . substr($cleaned, 1);
        }
        // Ajouter le préfixe +33 si le numéro n'a pas de préfixe et contient 9 chiffres
        elseif (!preg_match('/^\+/', $cleaned) && strlen($cleaned) == 9) {
            $cleaned = '+33' . $cleaned;
        }

        return $cleaned;
    }
}
