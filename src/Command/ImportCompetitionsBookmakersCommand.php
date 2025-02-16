<?php

namespace App\Command;

use App\Entity\Bookmaker;
use App\Entity\Competition;
use App\Entity\CompetitionBookmaker;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:import-competitions-bookmakers',
    description: 'Importe et/ou met à jour les compétitions des bookmakers depuis un fichier CSV en se basant sur ".
        "l\'ID du CSV.',
)]
class ImportCompetitionsBookmakersCommand extends Command
{
    private EntityManagerInterface $em;
    private string $csvPath;
    public function __construct(EntityManagerInterface $em)
    {
        parent::__construct();
        $this->em = $em;
        $this->csvPath = $_ENV['DATA_PATH'] . '/competitions-bookmakers.csv';
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
        $competitionBookmakerRepository = $this->em->getRepository(CompetitionBookmaker::class);
        $competitionRepository = $this->em->getRepository(Competition::class);
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
            $slugCompetition = trim($row['slug_competition'] ?? '');
            $slugBookmaker = trim($row['slug_bookmaker'] ?? '');
            $url = trim($row['url'] ?? '');

            // Vérification de la présence des données sur la ligne
            if (empty($csvId) || empty($slugCompetition) || empty($slugBookmaker) || empty($url)) {
                $io->warning("Ligne $rowCount incomplète (id, slug_competition, slug_bookmaker ou url manquant).");
                continue;
            }

            // Tentative de récupération du competitionBookmaker en BDD en se basant sur le csv_id
            $competitionBookmaker = $competitionBookmakerRepository->findOneBy(['csv_id' => $csvId]);

            // Mise à jour du competitionBookmaker s'il existe en base et que les données ont changé
            if ($competitionBookmaker) {
                $changed = false;
                if ($competitionBookmaker->getCompetition()->getSlug() !== $slugCompetition) {
                    $competitionBookmaker->setCompetition(
                        $competitionRepository->findOneBy(['slug' => $slugCompetition])
                    );
                    $changed = true;
                }
                if ($competitionBookmaker->getBookmaker()->getSlug() !== $slugBookmaker) {
                    $competitionBookmaker->setBookmaker(
                        $bookmakerRepository->findOneBy(['slug' => $slugBookmaker])
                    );
                    $changed = true;
                }
                if ($competitionBookmaker->getUrl() !== $url) {
                    $competitionBookmaker->setUrl($url);
                    $changed = true;
                }
                if ($changed) {
                    $competitionBookmaker->setUpdatedAt(new \DateTimeImmutable());
                    $updated++;
                    $io->writeln(
                        "Mise à jour du couple compétition / bookmaker '".
                        $competitionRepository->findOneBy(['slug' => $slugCompetition])->getName()."' / '".
                        $bookmakerRepository->findOneBy(['slug' => $slugBookmaker])->getName()."' (csv_id: $csvId, ".
                        "url: $url)."
                    );
                }

                // Ajout du nouveau competitionBookmaker en base
            } else {
                $competitionBookmaker = new CompetitionBookmaker();
                $competitionBookmaker->setCsvId($csvId);
                $competitionBookmaker->setCompetition($competitionRepository->findOneBy(['slug' => $slugCompetition]));
                $competitionBookmaker->setBookmaker($bookmakerRepository->findOneBy(['slug' => $slugBookmaker]));
                $competitionBookmaker->setUrl($url);
                $competitionBookmaker->setCreatedAt(new \DateTimeImmutable());
                $this->em->persist($competitionBookmaker);
                $inserted++;
                $io->writeln(
                    "Création du couple compétition / bookmaker '".
                    $competitionRepository->findOneBy(['slug' => $slugCompetition])->getName()."' / '".
                    $bookmakerRepository->findOneBy(['slug' => $slugBookmaker])->getName()."' (csv_id: $csvId, ".
                    "url: $url)."
                );
            }
        }

        fclose($handle);
        $this->em->flush();
        $io->success("Import terminé. Lignes traitées : $rowCount. Insertion(s) : $inserted, Mise(s) à jour : $updated.");
        return Command::SUCCESS;
    }
}