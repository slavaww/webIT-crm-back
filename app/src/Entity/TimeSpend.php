<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use App\Repository\TimeSpendRepository;
use Doctrine\ORM\Mapping as ORM;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Delete;
use Symfony\Component\Serializer\Annotation\Groups;
use App\State\TimeSpendCurrentMonthProvider;
use App\State\TimeSpendProcessor;

#[ApiResource(
    normalizationContext: ['groups' => ['timeSpend:read']],
    denormalizationContext: ['groups' => ['timeSpend:write']],
    operations: [
        new GetCollection(
            normalizationContext: ['groups' => ['timeSpend:read']],
            provider: TimeSpendCurrentMonthProvider::class,
        ),
        new Get(
            security: "is_granted('TIME_SPEND_VIEW', object)",
            normalizationContext: ['groups' => ['timeSpend:read']],
        ),
        new Post(
            security: "is_granted('TIME_SPEND_CREATE')",
            denormalizationContext: ['groups' => ['timeSpend:write']],
            processor: TimeSpendProcessor::class,
        ),
        new Patch(
            security: "is_granted('TIME_SPEND_EDIT', object)"
        ),
        new Delete(
            security: "is_granted('TIME_SPEND_DELETE', object)"
        ),
    ]
)]
#[ORM\Entity(repositoryClass: TimeSpendRepository::class)]
class TimeSpend
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['timeSpend:read'])]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'timeSpends')]
    #[Groups(['timeSpend:read', 'timeSpend:write'])]
    private ?Clients $client = null;

    #[ORM\Column]
    #[Groups(['timeSpend:read', 'timeSpend:write'])]
    private ?\DateTime $date = null;

    #[ORM\Column]
    #[Groups(['timeSpend:read', 'timeSpend:write'])]
    private ?int $time_spend = null; // time in minutes

    #[ORM\ManyToOne(inversedBy: 'timeSpends')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['timeSpend:read', 'timeSpend:write'])]
    private ?Employee $worker = null;

    #[ORM\ManyToOne(inversedBy: 'timeSpends')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['timeSpend:read', 'timeSpend:write'])]
    private ?Tasks $task = null;

    #[ORM\ManyToOne(inversedBy: 'timeSpends')]
    #[Groups(['timeSpend:read', 'timeSpend:write'])]
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
