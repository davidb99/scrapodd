<?php

namespace App\Entity;

use App\Repository\CompetitionRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CompetitionRepository::class)]
class Competition
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'competitions')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Sport $sport = null;

    #[ORM\Column(length: 100)]
    private ?string $name = null;

    /**
     * @var Collection<int, CompetitionBookmaker>
     */
    #[ORM\OneToMany(targetEntity: CompetitionBookmaker::class, mappedBy: 'competition')]
    private Collection $competitionBookmakers;

    public function __construct()
    {
        $this->competitionBookmakers = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSport(): ?Sport
    {
        return $this->sport;
    }

    public function setSport(?Sport $sport): static
    {
        $this->sport = $sport;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return Collection<int, CompetitionBookmaker>
     */
    public function getCompetitionBookmakers(): Collection
    {
        return $this->competitionBookmakers;
    }

    public function addCompetitionBookmaker(CompetitionBookmaker $competitionBookmaker): static
    {
        if (!$this->competitionBookmakers->contains($competitionBookmaker)) {
            $this->competitionBookmakers->add($competitionBookmaker);
            $competitionBookmaker->setCompetition($this);
        }

        return $this;
    }

    public function removeCompetitionBookmaker(CompetitionBookmaker $competitionBookmaker): static
    {
        if ($this->competitionBookmakers->removeElement($competitionBookmaker)) {
            // set the owning side to null (unless already changed)
            if ($competitionBookmaker->getCompetition() === $this) {
                $competitionBookmaker->setCompetition(null);
            }
        }

        return $this;
    }
}
