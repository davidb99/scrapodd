<?php

namespace App\Entity;

use App\Repository\OddsSnapshotRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: OddsSnapshotRepository::class)]
class OddsSnapshot
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'oddsSnapshots')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Event $event = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $snapshot_date = null;

    #[ORM\Column(length: 50)]
    private ?string $market = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $created_at = null;

    /**
     * @var Collection<int, Odd>
     */
    #[ORM\OneToMany(targetEntity: Odd::class, mappedBy: 'odds_snapshot')]
    private Collection $odds;

    public function __construct()
    {
        $this->odds = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEvent(): ?Event
    {
        return $this->event;
    }

    public function setEvent(?Event $event): static
    {
        $this->event = $event;

        return $this;
    }

    public function getSnapshotDate(): ?\DateTimeImmutable
    {
        return $this->snapshot_date;
    }

    public function setSnapshotDate(\DateTimeImmutable $snapshot_date): static
    {
        $this->snapshot_date = $snapshot_date;

        return $this;
    }

    public function getMarket(): ?string
    {
        return $this->market;
    }

    public function setMarket(string $market): static
    {
        $this->market = $market;

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

    /**
     * @return Collection<int, Odd>
     */
    public function getOdds(): Collection
    {
        return $this->odds;
    }

    public function addOdd(Odd $odd): static
    {
        if (!$this->odds->contains($odd)) {
            $this->odds->add($odd);
            $odd->setOddsSnapshot($this);
        }

        return $this;
    }

    public function removeOdd(Odd $odd): static
    {
        if ($this->odds->removeElement($odd)) {
            // set the owning side to null (unless already changed)
            if ($odd->getOddsSnapshot() === $this) {
                $odd->setOddsSnapshot(null);
            }
        }

        return $this;
    }
}
