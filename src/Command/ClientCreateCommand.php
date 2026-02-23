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
    description: 'Create a new client interactively'
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

        // Firstname
        $question = new Question('Prénom: ');
        $firstname = $helper->ask($input, $output, $question);

        // Lastname
        $question = new Question('Nom: ');
        $lastname = $helper->ask($input, $output, $question);

        // Email
        $question = new Question('Email: ');
        $email = $helper->ask($input, $output, $question);

        // Phone (required)
        $question = new Question('Téléphone (format: 0XXXXXXXXX ou +33XXXXXXXXX): ');
        $phone = $helper->ask($input, $output, $question);

        // Address (required)
        $question = new Question('Adresse: ');
        $address = $helper->ask($input, $output, $question);

        $output->writeln('');

        // Create client
        $client = new Client();
        $client->setFirstname($firstname);
        $client->setLastname($lastname);
        $client->setEmail($email);
        
        // Format phone number
        $formattedPhone = $this->formatPhoneNumber($phone);
        $client->setPhoneNumber($formattedPhone);
        
        $client->setAddress($address);

        // Validate
        $errors = $this->validator->validate($client);
        if (count($errors) > 0) {
            $output->writeln('<error>Erreur de validation:</error>');
            foreach ($errors as $error) {
                $output->writeln('  <error>• ' . $error->getMessage() . '</error>');
            }
            return Command::FAILURE;
        }

        // Persist and flush
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
        // Remove all non-digit and + characters
        $cleaned = preg_replace('/[^\d\+]/', '', $phone);

        // Format to +33 if it starts with 0
        if (preg_match('/^0/', $cleaned)) {
            $cleaned = '+33' . substr($cleaned, 1);
        }
        // Add +33 prefix if it doesn't have it and has 9 digits
        elseif (!preg_match('/^\+/', $cleaned) && strlen($cleaned) == 9) {
            $cleaned = '+33' . $cleaned;
        }

        return $cleaned;
    }
}
