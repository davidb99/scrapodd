<?php

namespace App\Entity;

use App\Repository\OddRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: OddRepository::class)]
class Odd
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'odds')]
    #[ORM\JoinColumn(nullable: false)]
    private ?OddsSnapshot $odds_snapshot = null;

    #[ORM\Column(length: 100)]
    private ?string $outcome = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 6, scale: 2)]
    private ?string $value = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $created_at = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getOddsSnapshot(): ?OddsSnapshot
    {
        return $this->odds_snapshot;
    }

    public function setOddsSnapshot(?OddsSnapshot $odds_snapshot): static
    {
        $this->odds_snapshot = $odds_snapshot;

        return $this;
    }

    public function getOutcome(): ?string
    {
        return $this->outcome;
    }

    public function setOutcome(string $outcome): static
    {
        $this->outcome = $outcome;

        return $this;
    }

    public function getValue(): ?string
    {
        return $this->value;
    }

    public function setValue(string $value): static
    {
        $this->value = $value;

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
}
