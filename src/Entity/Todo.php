<?php

namespace App\Entity;

use App\Enum\TodoStatus;
use App\Repository\TodoRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Types\UuidType;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: TodoRepository::class)]
class Todo
{
    #[ORM\Id]
    #[ORM\Column(type: UuidType::NAME, unique: true)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: 'doctrine.uuid_generator')]
    private ?Uuid $id = null;

    #[ORM\Column(length: 255)]
    private ?string $title = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    #[ORM\Column(length: 20, enumType: TodoStatus::class)]
    private TodoStatus $status = TodoStatus::TODO;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $dueDate = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 7, scale: 1, nullable: true)]
    private ?string $estimatedHours = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 7, scale: 1, nullable: true)]
    private ?string $spentHours = null;

    #[ORM\Column]
    private \DateTimeImmutable $createdAt;

    #[ORM\ManyToOne(inversedBy: 'todos')]
    private ?Project $project = null;

    #[ORM\ManyToOne]
    private ?User $assignedTo = null;

    /** @var Collection<int, TimeEntry> */
    #[ORM\OneToMany(targetEntity: TimeEntry::class, mappedBy: 'todo', orphanRemoval: true)]
    #[ORM\OrderBy(['date' => 'DESC'])]
    private Collection $timeEntries;

    /** @var Collection<int, TaskGroup> */
    #[ORM\ManyToMany(targetEntity: TaskGroup::class, mappedBy: 'todos')]
    private Collection $taskGroups;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->timeEntries = new ArrayCollection();
        $this->taskGroups = new ArrayCollection();
    }

    public function __toString(): string
    {
        return $this->title ?? '';
    }

    public function getId(): ?Uuid
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

    public function getStatus(): TodoStatus
    {
        return $this->status;
    }

    public function setStatus(TodoStatus $status): static
    {
        $this->status = $status;

        return $this;
    }

    public function getDueDate(): ?\DateTimeImmutable
    {
        return $this->dueDate;
    }

    public function setDueDate(?\DateTimeImmutable $dueDate): static
    {
        $this->dueDate = $dueDate;

        return $this;
    }

    public function getEstimatedHours(): ?float
    {
        return $this->estimatedHours !== null ? (float) $this->estimatedHours : null;
    }

    public function setEstimatedHours(?float $estimatedHours): static
    {
        $this->estimatedHours = $estimatedHours !== null ? (string) $estimatedHours : null;

        return $this;
    }

    public function getSpentHours(): ?float
    {
        return $this->spentHours !== null ? (float) $this->spentHours : null;
    }

    public function setSpentHours(?float $spentHours): static
    {
        $this->spentHours = $spentHours !== null ? (string) $spentHours : null;

        return $this;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getProject(): ?Project
    {
        return $this->project;
    }

    public function setProject(?Project $project): static
    {
        $this->project = $project;

        return $this;
    }

    public function getAssignedTo(): ?User
    {
        return $this->assignedTo;
    }

    public function setAssignedTo(?User $assignedTo): static
    {
        $this->assignedTo = $assignedTo;

        return $this;
    }

    /** @return Collection<int, TimeEntry> */
    public function getTimeEntries(): Collection
    {
        return $this->timeEntries;
    }

    public function getTotalLoggedHours(): float
    {
        $total = 0.0;
        foreach ($this->timeEntries as $entry) {
            $total += $entry->getHours() ?? 0;
        }

        return $total;
    }

    /**
     * @return array<string, array{user: User, hours: float}>
     */
    public function getHoursByUser(): array
    {
        $byUser = [];
        foreach ($this->timeEntries as $entry) {
            $userId = $entry->getUser()->getId()->toRfc4122();
            if (!isset($byUser[$userId])) {
                $byUser[$userId] = ['user' => $entry->getUser(), 'hours' => 0.0];
            }
            $byUser[$userId]['hours'] += $entry->getHours() ?? 0;
        }

        uasort($byUser, fn ($a, $b) => $b['hours'] <=> $a['hours']);

        return $byUser;
    }

    /** @return Collection<int, TaskGroup> */
    public function getTaskGroups(): Collection
    {
        return $this->taskGroups;
    }

    public function addTaskGroup(TaskGroup $taskGroup): static
    {
        if (!$this->taskGroups->contains($taskGroup)) {
            $this->taskGroups->add($taskGroup);
            $taskGroup->addTodo($this);
        }

        return $this;
    }

    public function removeTaskGroup(TaskGroup $taskGroup): static
    {
        if ($this->taskGroups->removeElement($taskGroup)) {
            $taskGroup->removeTodo($this);
        }

        return $this;
    }
}
