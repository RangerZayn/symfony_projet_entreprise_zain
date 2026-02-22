<?php

namespace App\Command;

use App\Entity\Product;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'app:product:import',
    description: 'Importe des produits à partir d\'un fichier CSV',
)]
class ProductImportCommand extends Command
{
    private EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em)
    {
        parent::__construct();
        $this->em = $em;
    }

    protected function configure()
    {
        $this->addArgument('path', InputArgument::REQUIRED, 'Path to CSV file');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $path = $input->getArgument('path');
        if (!file_exists($path)) {
            $output->writeln('<error>Fichier non trouvé: ' . $path . '</error>');
            return Command::FAILURE;
        }

        if (($handle = fopen($path, 'r')) === false) {
            $output->writeln('<error>Impossible d\'ouvrir le fichier</error>');
            return Command::FAILURE;
        }

        $header = fgetcsv($handle);
        while (($row = fgetcsv($handle)) !== false) {
            $data = array_combine($header, $row);
            $product = new Product();
            $product->setName($data['name'] ?? '');
            $product->setDescription($data['description'] ?? '');
            $product->setPrice($data['price'] ?? 0);
            $this->em->persist($product);
        }

        fclose($handle);
        $this->em->flush();
        $output->writeln('<info>Importation terminée!</info>');
        return Command::SUCCESS;
    }
}
