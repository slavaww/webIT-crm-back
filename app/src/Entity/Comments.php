<?php

namespace App\Entity;

use App\Repository\CommentsRepository;
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
use Symfony\Component\Security\Http\Attribute\IsGranted;
use App\Entity\User;
use App\Entity\Tasks;
use App\State\CommentsPersister;
use App\Entity\TimeSpend;
use ApiPlatform\Metadata\Link;
use App\State\CommentsCollectionProvider;

#[ORM\Entity(repositoryClass: CommentsRepository::class)]
#[ApiResource(
    normalizationContext: ['groups' => ['comment:read']],
    denormalizationContext: ['groups' => ['comment:write']],
    operations: [
        new GetCollection(
            uriTemplate: '/tasks/{id}/comments',
            uriVariables: [
                'id' => new Link(fromProperty: 'comments', fromClass: Tasks::class),
            ],
            normalizationContext: ['groups' => ['comment:read']],
            provider: CommentsCollectionProvider::class,
        ),
        new Get(
            security: "is_granted('COMMENT_VIEW', object)",
            normalizationContext: ['groups' => ['comment:read']],
        ),
        new Post(
            security: "is_granted('COMMENT_CREATE', object)",
            uriTemplate: '/tasks/{id}/comments',
            processor: CommentsPersister::class,
            denormalizationContext: ['groups' => ['comment:write']]
        ),
        new Patch(
            security: "is_granted('ROLE_SUPER_ADMIN') or (is_granted('ROLE_USER') or is_granted('ROLE_ADMIN')) and object.getAuthor() == user",
            securityMessage: "Редактировать комментарий может только его автор или SUPER_ADMIN"
        ),
        new Delete(
            security: "is_granted('ROLE_SUPER_ADMIN') or (is_granted('ROLE_USER') or is_granted('ROLE_ADMIN')) and object.getAuthor() == user",
            securityMessage: "Удалять комментарий может только его автор или SUPER_ADMIN"
        ),
    ],
)]
class Comments
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['comment:read'])]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'comments')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['comment:read'])]
    private ?User $author = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Groups(['comment:read', 'comment:write'])]
    private ?string $description = null;

    #[ORM\ManyToOne(inversedBy: 'comments')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['comment:read', 'comment:write'])]
    private ?Tasks $task = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    #[Groups(['comment:read'])]
    private ?\DateTime $created_at = null;

    /**
     * @var Collection<int, TimeSpend>
     */
    #[ORM\OneToMany(targetEntity: TimeSpend::class, mappedBy: 'comment')]
    private Collection $timeSpends;

    public function __construct()
    {
        $this->timeSpends = new ArrayCollection();
        $this->created_at = new \DateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAuthor(): ?User
    {
        return $this->author;
    }

    public function setAuthor(?User $author): static
    {
        $this->author = $author;

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

    public function getTask(): ?Tasks
    {
        return $this->task;
    }

    public function setTask(?Tasks $task): static
    {
        $this->task = $task;

        return $this;
    }

    public function getCreatedAt(): ?\DateTime
    {
        return $this->created_at;
    }

    public function setCreatedAt(\DateTime $created_at): self
    {
        $this->created_at = $created_at;
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
            $timeSpend->setComment($this);
        }

        return $this;
    }

    public function removeTimeSpend(TimeSpend $timeSpend): static
    {
        if ($this->timeSpends->removeElement($timeSpend)) {
            // set the owning side to null (unless already changed)
            if ($timeSpend->getComment() === $this) {
                $timeSpend->setComment(null);
            }
        }

        return $this;
    }
}
