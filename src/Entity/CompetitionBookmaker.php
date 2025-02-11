<?php

namespace App\Entity;

use App\Repository\CompetitionBookmakerRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CompetitionBookmakerRepository::class)]
class CompetitionBookmaker
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'competitionBookmakers')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Competition $competition = null;

    #[ORM\ManyToOne(inversedBy: 'competitionBookmakers')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Bookmaker $bookmaker = null;

    #[ORM\Column(length: 255)]
    private ?string $url = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $created_at = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $updated_at = null;

    /**
     * @var Collection<int, EventBookmaker>
     */
    #[ORM\OneToMany(targetEntity: EventBookmaker::class, mappedBy: 'competition_bookmaker')]
    private Collection $eventBookmakers;

    public function __construct()
    {
        $this->eventBookmakers = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCompetition(): ?Competition
    {
        return $this->competition;
    }

    public function setCompetition(?Competition $competition): static
    {
        $this->competition = $competition;

        return $this;
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

    public function getUrl(): ?string
    {
        return $this->url;
    }

    public function setUrl(string $url): static
    {
        $this->url = $url;

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
     * @return Collection<int, EventBookmaker>
     */
    public function getEventBookmakers(): Collection
    {
        return $this->eventBookmakers;
    }

    public function addEventBookmaker(EventBookmaker $eventBookmaker): static
    {
        if (!$this->eventBookmakers->contains($eventBookmaker)) {
            $this->eventBookmakers->add($eventBookmaker);
            $eventBookmaker->setCompetitionBookmaker($this);
        }

        return $this;
    }

    public function removeEventBookmaker(EventBookmaker $eventBookmaker): static
    {
        if ($this->eventBookmakers->removeElement($eventBookmaker)) {
            // set the owning side to null (unless already changed)
            if ($eventBookmaker->getCompetitionBookmaker() === $this) {
                $eventBookmaker->setCompetitionBookmaker(null);
            }
        }

        return $this;
    }
}
