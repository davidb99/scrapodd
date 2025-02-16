<?php

namespace App\Command;

use App\Entity\Bookmaker;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:import-bookmakers',
    description: 'Importe et/ou met à jour les bookmakers depuis un fichier CSV en se basant sur l\'ID du CSV.',
)]
class ImportBookmakersCommand extends Command
{
    private EntityManagerInterface $em;
    private string $csvPath;
    public function __construct(EntityManagerInterface $em)
    {
        parent::__construct();
        $this->em = $em;
        $this->csvPath = $_ENV['DATA_PATH'] . '/bookmakers.csv';
    }

    protected function configure(): void
    {}

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        // Test de l'existence du fichier CSV
        if (!file_exists($this->csvPath)) {
            $io->error("Fichier CSV introuvable : {$this->csvPath}");
            return Command::FAILURE;
        }

        // Ouverture du fichier CSV en lecture
        $handle = fopen($this->csvPath, 'r');
        if (!$handle) {
            $io->error("Impossible d'ouvrir le fichier CSV.");
            return Command::FAILURE;
        }

        // Récupération des entêtes du csv
        $headers = fgetcsv($handle);
        if ($headers === false) {
            $io->error("Erreur de lecture des en-têtes du CSV.");
            return Command::FAILURE;
        }
        $headers = array_map('strtolower', $headers);

        $rowCount = 0;
        $inserted = 0;
        $updated = 0;

        // Récupération du repository
        $bookmakerRepository = $this->em->getRepository(Bookmaker::class);

        // On parcourt le csv pour importer et/ou mettre à jour les données
        while (($data = fgetcsv($handle)) !== false) {

            $rowCount++;

            // Récupération des données du CSV
            $row = array_combine($headers, $data);
            if ($row === false) {
                $io->warning("Erreur à la ligne $rowCount, ligne ignorée.");
                continue;
            }
            $csvId = (int)trim($row['id'] ?? '');
            $name = trim($row['name'] ?? '');
            $slug = trim($row['slug'] ?? '');
            $website = trim($row['website'] ?? '');

            // Vérification de la présence des données sur la ligne
            if (empty($csvId) || empty($name) || empty($slug) || empty($website)) {
                $io->warning("Ligne $rowCount incomplète (id, name, slug ou website manquant).");
                continue;
            }

            // Tentative de récupération du bookmaker en BDD en se basant sur le csv_id
            $bookmaker = $bookmakerRepository->findOneBy(['csv_id' => $csvId]);

            // Mise à jour du bookmaker s'il existe en base et que les données ont changé
            if ($bookmaker) {
                $changed = false;
                if ($bookmaker->getName() !== $name) {
                    $bookmaker->setName($name);
                    $changed = true;
                }
                if ($bookmaker->getSlug() !== $slug) {
                    $bookmaker->setSlug($slug);
                    $changed = true;
                }
                if ($bookmaker->getWebsite() !== $website) {
                    $bookmaker->setWebsite($website);
                    $changed = true;
                }
                if ($changed) {
                    $updated++;
                    $io->writeln("Mise à jour du bookmaker '$name' (csv_id: $csvId, slug: $slug, website: $website).");
                }

            // Ajout du nouveau bookmaker en base
            } else {
                $bookmaker = new Bookmaker();
                $bookmaker->setCsvId($csvId);
                $bookmaker->setName($name);
                $bookmaker->setSlug($slug);
                $bookmaker->setWebsite($website);
                $this->em->persist($bookmaker);
                $inserted++;
                $io->writeln("Création du bookmaker '$name' (csvId: $csvId, slug: $slug).");
            }
        }

        fclose($handle);
        $this->em->flush();
        $io->success("Import terminé. Lignes traitées : $rowCount. Insertion(s) : $inserted, Mise(s) à jour : $updated.");
        return Command::SUCCESS;
    }
}