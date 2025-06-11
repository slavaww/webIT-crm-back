<?php

namespace App\Entity;

use App\Repository\TasksRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TasksRepository::class)]
class Tasks
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $title = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTime $create_date = null;

    #[ORM\ManyToOne(inversedBy: 'tasks')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $creator = null;

    #[ORM\ManyToOne(inversedBy: 'tasks')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Clients $client = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTime $start_time = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTime $end_time = null;

    #[ORM\ManyToOne(inversedBy: 'tasks')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Statuses $status = null;

    #[ORM\ManyToOne(inversedBy: 'tasks')]
    private ?Employee $worker = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getCreateDate(): ?\DateTime
    {
        return $this->create_date;
    }

    public function setCreateDate(\DateTime $create_date): static
    {
        $this->create_date = $create_date;

        return $this;
    }

    public function getCreator(): ?User
    {
        return $this->creator;
    }

    public function setCreator(?User $creator): static
    {
        $this->creator = $creator;

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

    public function getStartTime(): ?\DateTime
    {
        return $this->start_time;
    }

    public function setStartTime(?\DateTime $start_time): static
    {
        $this->start_time = $start_time;

        return $this;
    }

    public function getEndTime(): ?\DateTime
    {
        return $this->end_time;
    }

    public function setEndTime(?\DateTime $end_time): static
    {
        $this->end_time = $end_time;

        return $this;
    }

    public function getStatus(): ?Statuses
    {
        return $this->status;
    }

    public function setStatus(?Statuses $status): static
    {
        $this->status = $status;

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
}
