<?php

namespace App\Command;

use App\Entity\Competition;
use App\Entity\Sport;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:import-competitions',
    description: 'Importe et/ou met à jour les compétitions depuis un fichier CSV en se basant sur l\'ID du CSV.',
)]
class ImportCompetitionsCommand extends Command
{
    private EntityManagerInterface $em;
    private string $csvPath;
    public function __construct(EntityManagerInterface $em)
    {
        parent::__construct();
        $this->em = $em;
        $this->csvPath = $_ENV['DATA_PATH'] . '/competitions.csv';
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

        // Récupération des repositories
        $competitionRepository = $this->em->getRepository(Competition::class);
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
            $slugSport = trim($row['slug_sport'] ?? '');

            // Vérification de la présence des données sur la ligne
            if (empty($csvId) || empty($name) || empty($slug) || empty($slugSport)) {
                $io->warning("Ligne $rowCount incomplète (id, name, slug ou slug_sport manquant).");
                continue;
            }

            // Tentative de récupération de la compétition en BDD en se basant sur le csv_id
            $competition = $competitionRepository->findOneBy(['csv_id' => $csvId]);

            // Mise à jour de la compétition si elle existe en base et que les données ont changé
            if ($competition) {
                $changed = false;
                if ($competition->getName() !== $name) {
                    $competition->setName($name);
                    $changed = true;
                }
                if ($competition->getSlug() !== $slug) {
                    $competition->setSlug($slug);
                    $changed = true;
                }
                if ($competition->getSport()->getSlug() !== $slugSport) {
                    $competition->setSport($sportRepository->findOneBy(['slug' => $slugSport]));
                    $changed = true;
                }
                if ($changed) {
                    $updated++;
                    $io->writeln(
                        "Mise à jour de la compétition '$name' (csv_id: $csvId, slug: $slug, slug_sport: ".
                        "$slugSport)."
                    );
                }

                // Ajout de la nouvelle compétition en base
            } else {
                $competition = new Competition();
                $competition->setCsvId($csvId);
                $competition->setName($name);
                $competition->setSlug($slug);
                $competition->setSport($sportRepository->findOneBy(['slug' => $slugSport]));
                $this->em->persist($competition);
                $inserted++;
                $io->writeln(
                    "Création de la compétition '$name' (csv_id: $csvId, slug: $slug, slug_sport: $slugSport)."
                );
            }
        }

        fclose($handle);
        $this->em->flush();
        $io->success("Import terminé. Lignes traitées : $rowCount. Insertion(s) : $inserted, Mise(s) à jour : $updated.");
        return Command::SUCCESS;
    }
}