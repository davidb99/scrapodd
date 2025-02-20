<?php

namespace App\Service;

use App\Entity\Bookmaker;
use App\Entity\Competition;
use App\Repository\BookmakerRepository;
use App\Repository\CompetitionBookmakerRepository;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Component\DomCrawler\Crawler;

class BetclicOddsFetcher
{
    private HttpClientInterface $client;
    private CompetitionBookmakerRepository $competitionBookmakerRepository;
    private BookmakerRepository $bookmakerRepository;
    private Bookmaker $bookmaker;

    public function __construct(
        HttpClientInterface $client,
        CompetitionBookmakerRepository $competitionBookmakerRepository,
        BookmakerRepository $bookmakerRepository
    )
    {
        $this->client = $client;
        $this->competitionBookmakerRepository = $competitionBookmakerRepository;
        $this->bookmakerRepository = $bookmakerRepository;
        $this->bookmaker = $bookmakerRepository->findOneBy(['slug' => 'betclic']);
    }

    /**
     * Récupère les événements sportifs chez le bookmaker.
     *
     * @param string|null $competition Nom de la compétition (optionnel)
     * @return array Tableau des événements et de leurs cotes
     * @throws \Exception En cas d'erreur de récupération ou compétition non trouvée
     */
    public function fetchEvents(?Competition $competition = null): array
    {
        // Initialisation de l'array à retourner
        $eventsData = [];

        // Si la compétition est passée en paramètre
        if ($competition) {

            // Récupération de l'URL de la compétition chez le bookmaker
            $competitionBookmaker = $this->competitionBookmakerRepository->findOneBy([
                'competition' => $competition,
                'bookmaker' => $this->bookmaker
            ]);
            if (!$competitionBookmaker) {
                throw new \Exception(
                    'La compétition "'.$competition->getName().'" n\'a pas été trouvée chez '.
                    $this->bookmaker->getName().'.'
                );
            }
            $urls = [$competitionBookmaker->getUrl()];

        // Si la compétition n'est pas passée en paramètre
        } else {

            // Récupération des URLs de toutes les compétitions chez le bookmaker
            $competitionBookmakers = $this->competitionBookmakerRepository->findBy([
                'bookmaker' => $this->bookmaker
            ]);
            $urls = [];
            foreach ($competitionBookmakers as $competitionBookmaker) {
                $urls[] = $competitionBookmaker->getUrl();
            }
        }

        foreach ($urls as $url) {
            $eventsData = array_merge($eventsData, $this->fetchOddsFromUrlCompetition($url));
        }

        return $eventsData;
    }

    /**
     * Scrape une URL donnée pour récupérer les cotes.
     *
     * @param string $url
     * @return array
     * @throws \Exception
     */
    public function fetchOddsFromUrlCompetition(string $url): array
    {
        $response = $this->client->request('GET', $url);
        if ($response->getStatusCode() !== 200) {
            throw new \Exception("Échec de la récupération des données pour l'URL $url.");
        }
        $html = $response->getContent();
        $crawler = new Crawler($html);

        $eventsData = [];

        // On utilise le tag personnalisé 'sports-events-event'
        $crawler->filter('sports-events-event-card')->each(function(Crawler $eventNode) use (&$eventsData) {

            dump($eventNode);

            // On récupère l'URL de l'événement via le lien (balise <a> avec classe "cardEvent")
            $linkNodes = $eventNode->filter('a.cardEvent');
            if (!$linkNodes->count()) {
                return;
            }
            $eventUrl = $linkNodes->first()->attr('href');

            // On récupère le statut en examinant l'attribut class
            $classAttr = $linkNodes->first()->attr('class');
            $isLive = strpos($classAttr, 'is-live') !== false;

            // Extraction de l'identifiant de l'événement depuis l'URL (si elle se termine par "-m<id>")
            $eventId = null;
            if (preg_match('/-m(\d+)$/', $eventUrl, $matches)) {
                $eventId = $matches[1];
            }
            // Extraction des participants à partir des labels (ex. data-qa="contestant-1-label")
            $participant1 = '';
            $participant2 = '';
            try {
                $participant1 = trim($eventNode->filter('[data-qa="contestant-1-label"]')->text());
            } catch (\Exception $e) {}
            try {
                $participant2 = trim($eventNode->filter('[data-qa="contestant-2-label"]')->text());
            } catch (\Exception $e) {}

            // Extraire la date/heure de l'événement (depuis un élément avec la classe "event_infoTime")
            $eventDateStr = '';
            try {
                $eventDateStr = trim($eventNode->filter('.event_infoTime')->text());
            } catch (\Exception $e) {}
            $eventDate = $this->convertEventDate($eventDateStr);

            // Extraction des cotes depuis les boutons situés dans un container avec la classe "market_odds"
            $odds = [];
            $eventNode->filter('.market_odds .btn')->each(function(Crawler $btnNode) use (&$odds) {
                // Extraction de l'issue : le libellé dans un span ayant la classe "btn_label is-top"
                $outcome = '';
                try {
                    // On peut récupérer le texte du span interne
                    $outcome = trim($btnNode->filter('.btn_label.is-top')->text());
                } catch (\Exception $e) {}

                // Extraction de la cote (la valeur affichée dans le bouton)
                $oddValue = '';
                try {
                    // Parfois, le bouton contient plusieurs span, et le dernier contient la cote
                    $oddValue = trim($btnNode->filter('.btn_label')->last()->text());
                } catch (\Exception $e) {}

                if ($outcome !== '' && $oddValue !== '') {
                    $odds[] = [
                        'outcome'   => $outcome,
                        'odd_value' => $oddValue,
                    ];
                }
            });

            $eventsData[] = [
                'event_id' => $eventId,
                'url' => $eventUrl,
                'event_date' => $eventDate,
                'participant1' => $participant1,
                'participant2' => $participant2,
                'odds' => $odds,
                'is_live' => $isLive,
            ];
        });

        return $eventsData;
    }

    private function convertEventDate(string $dateString): ?\DateTimeImmutable
    {
        $dateString = trim($dateString);
        $today = new \DateTimeImmutable();

        // Cas "Aujourd'hui"
        if (stripos($dateString, "Aujourd'hui") !== false) {
            $timePart = trim(str_ireplace("Aujourd'hui", "", $dateString));
            $combined = $today->format('d/m/Y') . ' ' . $timePart;
            $dt = \DateTimeImmutable::createFromFormat('d/m/Y H:i', $combined);
            if ($dt !== false) {
                return $dt;
            }
        }

        // Cas "Demain"
        if (stripos($dateString, "Demain") !== false) {
            $timePart = trim(str_ireplace("Demain", "", $dateString));
            $tomorrow = $today->modify('+1 day');
            $combined = $tomorrow->format('d/m/Y') . ' ' . $timePart;
            $dt = \DateTimeImmutable::createFromFormat('d/m/Y H:i', $combined);
            if ($dt !== false) {
                return $dt;
            }
        }

        // Cas "Après-demain"
        if (stripos($dateString, "Après-demain") !== false) {
            $timePart = trim(str_ireplace("Après-demain", "", $dateString));
            $afterTomorrow = $today->modify('+2 day');
            $combined = $afterTomorrow->format('d/m/Y') . ' ' . $timePart;
            $dt = \DateTimeImmutable::createFromFormat('d/m/Y H:i', $combined);
            if ($dt !== false) {
                return $dt;
            }
        }

        // Cas absolu : format "d/m/Y H:i"
        $dt = \DateTimeImmutable::createFromFormat('d/m/Y H:i', $dateString);
        if ($dt !== false) {
            return $dt;
        }

        // Si aucun format ne correspond, on retourne null
        return null;
    }
}