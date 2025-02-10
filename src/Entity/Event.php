<?php

namespace App\Entity;

use App\Repository\EventRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: EventRepository::class)]
class Event
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'events')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Bookmaker $bookmaker = null;

    #[ORM\Column(length: 50)]
    private ?string $bookmaker_event_id = null;

    #[ORM\Column(length: 50)]
    private ?string $sport = null;

    #[ORM\Column(length: 100)]
    private ?string $competition = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $date = null;

    #[ORM\Column(length: 100)]
    private ?string $participant1 = null;

    #[ORM\Column(length: 100)]
    private ?string $participant2 = null;

    #[ORM\Column(length: 20)]
    private ?string $status = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $created_at = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $updated_at = null;

    /**
     * @var Collection<int, OddsSnapshot>
     */
    #[ORM\OneToMany(targetEntity: OddsSnapshot::class, mappedBy: 'event')]
    private Collection $oddsSnapshots;

    public function __construct()
    {
        $this->oddsSnapshots = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getBookmaker(): ?Bookmaker
    {
        return $this->bookmaker;
    }

    public function setBookmaker(?Bookmaker $bookmaker): static
    {
        $this->bookmaker = $bookmaker;

        return $this;
    }

    public function getBookmakerEventId(): ?string
    {
        return $this->bookmaker_event_id;
    }

    public function setBookmakerEventId(string $bookmaker_event_id): static
    {
        $this->bookmaker_event_id = $bookmaker_event_id;

        return $this;
    }

    public function getSport(): ?string
    {
        return $this->sport;
    }

    public function setSport(string $sport): static
    {
        $this->sport = $sport;

        return $this;
    }

    public function getCompetition(): ?string
    {
        return $this->competition;
    }

    public function setCompetition(string $competition): static
    {
        $this->competition = $competition;

        return $this;
    }

    public function getDate(): ?\DateTimeImmutable
    {
        return $this->date;
    }

    public function setDate(\DateTimeImmutable $date): static
    {
        $this->date = $date;

        return $this;
    }

    public function getParticipant1(): ?string
    {
        return $this->participant1;
    }

    public function setParticipant1(string $participant1): static
    {
        $this->participant1 = $participant1;

        return $this;
    }

    public function getParticipant2(): ?string
    {
        return $this->participant2;
    }

    public function setParticipant2(string $participant2): static
    {
        $this->participant2 = $participant2;

        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): static
    {
        $this->status = $status;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->created_at;
    }

    public function setCreatedAt(\DateTimeImmutable $created_at): static
    {
        $this->created_at = $created_at;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updated_at;
    }

    public function setUpdatedAt(?\DateTimeImmutable $updated_at): static
    {
        $this->updated_at = $updated_at;

        return $this;
    }

    /**
     * @return Collection<int, OddsSnapshot>
     */
    public function getOddsSnapshots(): Collection
    {
        return $this->oddsSnapshots;
    }

    public function addOddsSnapshot(OddsSnapshot $oddsSnapshot): static
    {
        if (!$this->oddsSnapshots->contains($oddsSnapshot)) {
            $this->oddsSnapshots->add($oddsSnapshot);
            $oddsSnapshot->setEvent($this);
        }

        return $this;
    }

    public function removeOddsSnapshot(OddsSnapshot $oddsSnapshot): static
    {
        if ($this->oddsSnapshots->removeElement($oddsSnapshot)) {
            // set the owning side to null (unless already changed)
            if ($oddsSnapshot->getEvent() === $this) {
                $oddsSnapshot->setEvent(null);
            }
        }

        return $this;
    }
}
