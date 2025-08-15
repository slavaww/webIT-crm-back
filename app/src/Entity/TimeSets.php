<?php

namespace App\Entity;

use App\Repository\TimeSetsRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TimeSetsRepository::class)]
class TimeSets
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?int $time_set = null;

    #[ORM\ManyToOne(inversedBy: 'timeSets')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Clients $client = null;

    #[ORM\Column(nullable: true)]
    private ?int $time_spend = null;

    #[ORM\Column]
    private ?int $year = null;

    #[ORM\Column]
    private ?int $month = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTimeSet(): ?int
    {
        return $this->time_set;
    }

    public function setTimeSet(int $time_set): static
    {
        $this->time_set = $time_set;

        return $this;
    }

    public function getClient(): ?Clients
    {
        return $this->client;
    }

    public function setClient(?Clients $client): static
    {
        $this->client = $client;

        return $this;
    }

    public function getTimeSpend(): ?int
    {
        return $this->time_spend;
    }

    public function setTimeSpend(?int $time_spend): static
    {
        $this->time_spend = $time_spend;

        return $this;
    }

    public function getYear(): ?int
    {
        return $this->year;
    }

    public function setYear(int $year): static
    {
        $this->year = $year;

        return $this;
    }

    public function getMonth(): ?int
    {
        return $this->month;
    }

    public function setMonth(int $month): static
    {
        $this->month = $month;

        return $this;
    }
}
