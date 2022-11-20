<?php

namespace App\Entity;

use App\Repository\TaskRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TaskRepository::class)]
class Task
{
    public const COMPLETED_STATE = 'completed';
    public const FAILED_STATE = 'failed';
    public const FINAL_STATES = [
        self::COMPLETED_STATE,
        self::FAILED_STATE,
    ];

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\ManyToOne(inversedBy: 'tasks')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Quest $quest = null;

    #[ORM\Column(length: 255)]
    private string $state = 'new';

    #[ORM\Column(options: ['default' => 0])]
    private int $ordinality = 0;

    #[ORM\Column(length: 255)]
    private ?string $workflow = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getQuest(): ?Quest
    {
        return $this->quest;
    }

    public function setQuest(?Quest $quest): void
    {
        $this->quest = $quest;
    }

    public function getState(): string
    {
        return $this->state;
    }

    public function setState(string $state): void
    {
        $this->state = $state;
    }

    public function getOrdinality(): int
    {
        return $this->ordinality;
    }

    public function setOrdinality(int $ordinality): void
    {
        $this->ordinality = $ordinality;
    }

    public function getWorkflow(): ?string
    {
        return $this->workflow;
    }

    public function setWorkflow(string $workflow): self
    {
        $this->workflow = $workflow;

        return $this;
    }

    public function __toString(): string
    {
        return $this->getName();
    }
}
