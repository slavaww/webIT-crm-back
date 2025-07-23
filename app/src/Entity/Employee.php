<?php

namespace App\Entity;

use App\Repository\EmployeeRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Delete;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: EmployeeRepository::class)]
#[ApiResource(
    normalizationContext: ['groups' => ['employee:read']],
    denormalizationContext: ['groups' => ['employee:write']],
    operations: [
        new GetCollection(
            security: "is_granted('ROLE_ADMIN') or is_granted('ROLE_SUPER_ADMIN') or is_granted('ROLE_USER')",
            normalizationContext: ['groups' => ['employee:read']]
        ),
        new Patch(
            denormalizationContext: ['groups' => ['employee:write']],
            // Защищаем эндпоинт с помощью Voter'а
            security: "is_granted('EMPLOYEE_EDIT', object)"
        ),
        new Get(
            security: "is_granted('EMPLOYEE_VIEW', object)"
        ),
        new Post(
            security: "is_granted('EMPLOYEE_CREATE', object)"
        ),
        new Delete(
            security: "is_granted('EMPLOYEE_DELETE', object)"
        ),
    ]
)]
class Employee
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['employee:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(['employee:read', 'employee:write'])]
    private ?string $job_title = null;

    #[ORM\OneToOne(inversedBy: 'employee', cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['employee:read', 'employee:write'])]
    private ?User $user_id = null;

    /**
     * @var Collection<int, Tasks>
     */
    #[ORM\OneToMany(targetEntity: Tasks::class, mappedBy: 'worker')]
    #[Groups(['employee:read'])]
    private Collection $tasks;

    /**
     * @var Collection<int, TimeSpend>
     */
    #[ORM\OneToMany(targetEntity: TimeSpend::class, mappedBy: 'worker')]
    #[Groups(['employee:read'])]
    private Collection $timeSpends;

    public function __construct()
    {
        $this->tasks = new ArrayCollection();
        $this->timeSpends = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getJobTitle(): ?string
    {
        return $this->job_title;
    }

    public function setJobTitle(string $job_title): static
    {
        $this->job_title = $job_title;

        return $this;
    }

    public function getUserId(): ?User
    {
        return $this->user_id;
    }

    public function setUserId(User $user_id): static
    {
        $this->user_id = $user_id;

        return $this;
    }

    /**
     * @return Collection<int, Tasks>
     */
    public function getTasks(): Collection
    {
        return $this->tasks;
    }

    public function addTask(Tasks $task): static
    {
        if (!$this->tasks->contains($task)) {
            $this->tasks->add($task);
            $task->setWorker($this);
        }

        return $this;
    }

    public function removeTask(Tasks $task): static
    {
        if ($this->tasks->removeElement($task)) {
            // set the owning side to null (unless already changed)
            if ($task->getWorker() === $this) {
                $task->setWorker(null);
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
            $timeSpend->setWorker($this);
        }

        return $this;
    }

    public function removeTimeSpend(TimeSpend $timeSpend): static
    {
        if ($this->timeSpends->removeElement($timeSpend)) {
            // set the owning side to null (unless already changed)
            if ($timeSpend->getWorker() === $this) {
                $timeSpend->setWorker(null);
            }
        }

        return $this;
    }
}
