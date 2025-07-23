<?php

namespace App\Entity;

use App\Repository\ClientsRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: ClientsRepository::class)]
#[ApiResource(
    operations: [
        // Разрешаем получать (GET) свой профиль
        new Get(
            normalizationContext: ['groups' => ['client:read']],
            security: "is_granted('CLIENT_EDIT', object)"
        ),
        new GetCollection(
            normalizationContext: ['groups' => ['client:read']],
            security: "is_granted('ROLE_ADMIN') or is_granted('ROLE_SUPER_ADMIN') or is_granted('ROLE_USER')"
        ),
        // Разрешаем обновлять (PATCH) свой профиль
        new Patch(
            denormalizationContext: ['groups' => ['client:write']],
            security: "is_granted('CLIENT_EDIT', object)"
        )
    ],
    // Контекст для всех операций по умолчанию
    normalizationContext: ['groups' => ['client:read']],
    denormalizationContext: ['groups' => ['client:write']]
)]
class Clients
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['client:read'])]
    private ?int $id = null;

    #[ORM\OneToOne(cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['client:read', 'client:write'])]
    private ?User $user_id = null;

    #[ORM\Column(length: 255)]
    #[Groups(['client:read', 'client:write'])]
    private ?string $title = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['client:read', 'client:write'])]
    private ?string $description = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['client:read', 'client:write'])]
    private ?string $job_title = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['client:read', 'client:write'])]
    private ?string $phone = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['client:read', 'client:write'])]
    private ?string $email = null;

    #[ORM\OneToOne(targetEntity: ClientsEmerg::class, cascade: ['persist', 'remove'], orphanRemoval: true)] 
    #[Groups(['client:read', 'client:write'])]
    private ?ClientsEmerg $client_emrg = null;

    /**
     * @var Collection<int, Tasks>
     */
    #[ORM\OneToMany(targetEntity: Tasks::class, mappedBy: 'client')]
    private Collection $tasks;

    /**
     * @var Collection<int, TimeSets>
     */
    #[ORM\OneToMany(targetEntity: TimeSets::class, mappedBy: 'client')]
    private Collection $timeSets;

    /**
     * @var Collection<int, TimeSpend>
     */
    #[ORM\OneToMany(targetEntity: TimeSpend::class, mappedBy: 'client')]
    private Collection $timeSpends;

    public function __construct()
    {
        $this->tasks = new ArrayCollection();
        $this->timeSets = new ArrayCollection();
        $this->timeSpends = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getJobTitle(): ?string
    {
        return $this->job_title;
    }

    public function setJobTitle(?string $job_title): static
    {
        $this->job_title = $job_title;

        return $this;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(?string $phone): static
    {
        $this->phone = $phone;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): static
    {
        $this->email = $email;

        return $this;
    }

    public function getClientEmrg(): ?ClientsEmerg
    {
        return $this->client_emrg;
    }

    public function setClientEmrg(?ClientsEmerg $client_emrg): static
    {
        $this->client_emrg = $client_emrg;

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
            $task->setClient($this);
        }

        return $this;
    }

    public function removeTask(Tasks $task): static
    {
        if ($this->tasks->removeElement($task)) {
            // set the owning side to null (unless already changed)
            if ($task->getClient() === $this) {
                $task->setClient(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, TimeSets>
     */
    public function getTimeSets(): Collection
    {
        return $this->timeSets;
    }

    public function addTimeSet(TimeSets $timeSet): static
    {
        if (!$this->timeSets->contains($timeSet)) {
            $this->timeSets->add($timeSet);
            $timeSet->setClient($this);
        }

        return $this;
    }

    public function removeTimeSet(TimeSets $timeSet): static
    {
        if ($this->timeSets->removeElement($timeSet)) {
            // set the owning side to null (unless already changed)
            if ($timeSet->getClient() === $this) {
                $timeSet->setClient(null);
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
            $timeSpend->setClient($this);
        }

        return $this;
    }

    public function removeTimeSpend(TimeSpend $timeSpend): static
    {
        if ($this->timeSpends->removeElement($timeSpend)) {
            // set the owning side to null (unless already changed)
            if ($timeSpend->getClient() === $this) {
                $timeSpend->setClient(null);
            }
        }

        return $this;
    }
}
