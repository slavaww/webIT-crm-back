<?php

namespace App\Entity;

use App\Repository\TimeSpendRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TimeSpendRepository::class)]
class TimeSpend
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'timeSpends')]
    private ?Clients $client = null;

    #[ORM\Column]
    private ?\DateTime $date = null;

    #[ORM\Column]
    private ?int $time_spend = null;

    #[ORM\ManyToOne(inversedBy: 'timeSpends')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Employee $worker = null;

    #[ORM\ManyToOne(inversedBy: 'timeSpends')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Tasks $task = null;

    #[ORM\ManyToOne(inversedBy: 'timeSpends')]
    private ?Comments $comment = null;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getDate(): ?\DateTime
    {
        return $this->date;
    }

    public function setDate(\DateTime $date): static
    {
        $this->date = $date;

        return $this;
    }

    public function getTimeSpend(): ?int
    {
        return $this->time_spend;
    }

    public function setTimeSpend(int $time_spend): static
    {
        $this->time_spend = $time_spend;

        return $this;
    }

    public function getWorker(): ?Employee
    {
        return $this->worker;
    }

    public function setWorker(?Employee $worker): static
    {
        $this->worker = $worker;

        return $this;
    }

    public function getTask(): ?Tasks
    {
        return $this->task;
    }

    public function setTask(?Tasks $task): static
    {
        $this->task = $task;

        return $this;
    }

    public function getComment(): ?Comments
    {
        return $this->comment;
    }

    public function setComment(?Comments $comment): static
    {
        $this->comment = $comment;

        return $this;
    }
}
