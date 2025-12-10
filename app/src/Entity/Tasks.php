<?php

namespace App\Entity;

use App\Repository\TasksRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Delete;
use Symfony\Component\Serializer\Annotation\Groups;
use App\Entity\User;
use App\Entity\Statuses;
use App\Entity\Clients;
use App\Entity\Employee;
use App\Entity\Comments;
use App\Entity\TimeSpend;
use App\State\TasksCollectionProvider;
use \App\State\TasksProcessor;

#[ORM\Entity(repositoryClass: TasksRepository::class)]
#[ApiResource(
    normalizationContext: ['groups' => ['task:read']],
    denormalizationContext: ['groups' => ['task:write']],
    operations: [
        new GetCollection(
            normalizationContext: ['groups' => ['task:read']],
            provider: TasksCollectionProvider::class,
        ),
        new Get(
            security: "is_granted('TASK_VIEW', object)",
            normalizationContext: ['groups' => ['task:read']]
        ),
        new Post(
            processor: TasksProcessor::class,
            security: "is_granted('TASK_CREATE')",
        ),
        new Patch(
            processor: TasksProcessor::class,
            security: "is_granted('TASK_EDIT', object)"
        ),
        new Delete(
            security: "is_granted('TASK_DELETE', object)"
        ),
    ]
)]
class Tasks
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['task:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(['task:read', 'task:write'])]
    private ?string $title = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Groups(['task:read', 'task:write'])]
    private ?string $description = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    #[Groups(['task:read'])]
    private ?\DateTime $create_date = null;

    #[ORM\ManyToOne(inversedBy: 'tasks')]
    #[ORM\JoinColumn(nullable: true)]
    #[Groups(['task:read', 'task:write', 'comment:read'])]
    private ?User $creator = null;
    
    #[ORM\ManyToOne(inversedBy: 'tasks')]
    #[ORM\JoinColumn(nullable: true)]
    #[Groups(['task:read', 'task:write', 'comment:read'])]
    private ?Clients $client = null;
    
    #[ORM\Column(nullable: true)]
    #[Groups(['task:read', 'task:write'])]
    private ?\DateTime $start_time = null;
    
    #[ORM\Column(nullable: true)]
    #[Groups(['task:read', 'task:write'])]
    private ?\DateTime $end_time = null;
    
    #[ORM\ManyToOne(inversedBy: 'tasks')]
    #[ORM\JoinColumn(nullable: true)]
    #[Groups(['task:read', 'task:write', 'comment:read'])]
    private ?Statuses $status = null;
    
    #[ORM\ManyToOne(inversedBy: 'tasks')]
    #[ORM\JoinColumn(nullable: true)]
    #[Groups(['task:read', 'task:write', 'comment:read'])]
    private ?Employee $worker = null;

    /**
     * @var Collection<int, Comments>
     */
    #[ORM\OneToMany(targetEntity: Comments::class, mappedBy: 'task')]
    #[Groups(['task:read'])]
    private Collection $comments;

    /**
     * @var Collection<int, TimeSpend>
     */
    #[ORM\OneToMany(targetEntity: TimeSpend::class, mappedBy: 'task')]
    #[Groups(['task:read'])]
    private Collection $timeSpends;

    public function __construct()
    {
        $this->comments = new ArrayCollection();
        $this->timeSpends = new ArrayCollection();
        $this->create_date = new \DateTime();
    }

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

    /**
     * @return Collection<int, Comments>
     */
    public function getComments(): Collection
    {
        return $this->comments;
    }

    public function addComment(Comments $comment): static
    {
        if (!$this->comments->contains($comment)) {
            $this->comments->add($comment);
            $comment->setTask($this);
        }

        return $this;
    }

    public function removeComment(Comments $comment): static
    {
        if ($this->comments->removeElement($comment)) {
            // set the owning side to null (unless already changed)
            if ($comment->getTask() === $this) {
                $comment->setTask(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, TimeSpend>
     */
    public function getTimeSpends(): Collection
    {
        return $this->timeSpends;
    }

    public function addTimeSpend(TimeSpend $timeSpend): static
    {
        if (!$this->timeSpends->contains($timeSpend)) {
            $this->timeSpends->add($timeSpend);
            $timeSpend->setTask($this);
        }

        return $this;
    }

    public function removeTimeSpend(TimeSpend $timeSpend): static
    {
        if ($this->timeSpends->removeElement($timeSpend)) {
            // set the owning side to null (unless already changed)
            if ($timeSpend->getTask() === $this) {
                $timeSpend->setTask(null);
            }
        }

        return $this;
    }
}
