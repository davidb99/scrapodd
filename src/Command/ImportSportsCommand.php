<?php

namespace App\Command;

use App\Entity\Sport;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:import-sports',
    description: 'Importe et/ou met à jour les sports depuis un fichier CSV en se basant sur l\'ID du CSV.',
)]
class ImportSportsCommand extends Command
{
    private EntityManagerInterface $em;
    private string $csvPath;
    public function __construct(EntityManagerInterface $em)
    {
        parent::__construct();
        $this->em = $em;
        $this->csvPath = $_ENV['DATA_PATH'] . '/sports.csv';
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
        $sportRepository = $this->em->getRepository(Sport::class);

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

            // Vérification de la présence des données sur la ligne
            if (empty($csvId) || empty($name) || empty($slug)) {
                $io->warning("Ligne $rowCount incomplète (id, name ou slug manquant).");
                continue;
            }

            // Tentative de récupération du sport en BDD en se basant sur le csv_id
            $sport = $sportRepository->findOneBy(['csv_id' => $csvId]);

            // Mise à jour du sport s'il existe en base et que les données ont changé
            if ($sport) {
                $changed = false;
                if ($sport->getName() !== $name) {
                    $sport->setName($name);
                    $changed = true;
                }
                if ($sport->getSlug() !== $slug) {
                    $sport->setSlug($slug);
                    $changed = true;
                }
                if ($changed) {
                    $updated++;
                    $io->writeln("Mise à jour du sport '$name' (csv_id: $csvId, slug: $slug).");
                }

            // Ajout du nouveau sport en base
            } else {
                $sport = new Sport();
                $sport->setCsvId($csvId);
                $sport->setName($name);
                $sport->setSlug($slug);
                $this->em->persist($sport);
                $inserted++;
                $io->writeln("Création du sport '$name' (csvId: $csvId, slug: $slug).");
            }
        }

        fclose($handle);
        $this->em->flush();
        $io->success("Import terminé. Lignes traitées : $rowCount. Insertion(s) : $inserted, Mise(s) à jour : $updated.");
        return Command::SUCCESS;
    }
}