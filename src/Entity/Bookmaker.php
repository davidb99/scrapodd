<?php

namespace App\Entity;

use App\Repository\BookmakerRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: BookmakerRepository::class)]
class Bookmaker
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    private ?string $name = null;

    #[ORM\Column(length: 50)]
    private ?string $slug = null;

    #[ORM\Column(length: 255)]
    private ?string $website = null;

    /**
     * @var Collection<int, CompetitionBookmaker>
     */
    #[ORM\OneToMany(targetEntity: CompetitionBookmaker::class, mappedBy: 'bookmaker')]
    private Collection $competitionBookmakers;

    public function __construct()
    {
        $this->competitionBookmakers = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): static
    {
        $this->slug = $slug;

        return $this;
    }

    public function getWebsite(): ?string
    {
        return $this->website;
    }

    public function setWebsite(string $website): static
    {
        $this->website = $website;

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
            $competitionBookmaker->setBookmaker($this);
        }

        return $this;
    }

    public function removeCompetitionBookmaker(CompetitionBookmaker $competitionBookmaker): static
    {
        if ($this->competitionBookmakers->removeElement($competitionBookmaker)) {
            // set the owning side to null (unless already changed)
            if ($competitionBookmaker->getBookmaker() === $this) {
                $competitionBookmaker->setBookmaker(null);
            }
        }

        return $this;
    }
}
