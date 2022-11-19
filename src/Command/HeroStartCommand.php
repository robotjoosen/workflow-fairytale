<?php

namespace App\Command;

use App\Entity\Hero;
use App\Entity\Task;
use App\Repository\HeroRepository;
use App\Repository\QuestRepository;
use App\Repository\TaskRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\ConsoleOutputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\String\UnicodeString;
use Symfony\Component\Workflow\Registry;

#[AsCommand(
    name: 'hero:start',
    description: 'Add a short description for your command',
)]
class HeroStartCommand extends Command
{
    private SymfonyStyle $io;

    public function __construct(
        private readonly HeroRepository $heroRepository,
        private readonly QuestRepository $questRepository,
        private readonly TaskRepository $taskRepository,
        private readonly EntityManagerInterface $entityManager,
        private readonly Registry $workflowRegistry,
        string $name = null,
    ) {
        parent::__construct($name);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        if (!$output instanceof ConsoleOutputInterface) {
            throw new \LogicException('This command accepts only an instance of "ConsoleOutputInterface".');
        }

        $this->io = new SymfonyStyle($input, $output);

        $this->io->section('Choose your hero');
        $hero = $this->io->choice('select your hero', $this->heroRepository->findAll());
        $this->io->info('You have just selected: ' . $hero);

        $this->io->section('Pick a quest');
        $quest = $this->io->choice('Select your quest!', $this->questRepository->findWithUncompletedTasks());
        $this->io->info('You have just selected: ' . $quest);

        $this->io->section('Current state of your journey');
        $this->io->title($hero->getName() . '\'s stats');
        $this->tablelizeHeroStats($hero);

        $this->io->title('Tasks');
        $this->tablelizeCurrentStateOfTasks($quest->getTasks());

        $this->io->section('Time get started with your quest');
        $tasks = $this->taskRepository->findUncompletedTaskByQuest($quest);
        foreach ($tasks as $task) {
            $this->io->title(sprintf('Your started to %s', $task));
            $this->handleTask($task);

            $this->io->info('Your progress');
            $this->tablelizeHeroStats($hero);
        }

        return Command::SUCCESS;
    }

    private function handleTask(Task $task): void
    {
        while (!in_array($task->getState(), ['completed', 'failed'])) {
            $workflow = $this->workflowRegistry->get($task, $task->getName());
            $transitions = $workflow->getEnabledTransitions($task);

            $this->io->text($workflow->getMetadataStore()->getPlaceMetadata($task->getState())['story']);

            $transitionChoices = ['Quit'];
            foreach ($transitions as $transition) {
                $name = new UnicodeString($transition->getName());
                $transitionChoices = [$name->replace('_', ' ')->title(), ...$transitionChoices];
            }
            $selectedTransition = $this->io->choice('What is next?', $transitionChoices);

            if ($selectedTransition === 'Quit') {
                $this->io->warning('Goodbye, see you next time!');

                exit;
            }

            $selectedTransition = new UnicodeString($selectedTransition);
            $workflow->apply($task, $selectedTransition->lower()->snake() ?? (reset($transitions))->getName());
            $this->entityManager->flush();

            $this->io->comment(sprintf('you just performed %s', $selectedTransition));
        }
    }

    /**
     * @param Task[] $tasks
     */
    private function tablelizeCurrentStateOfTasks(array $tasks): void
    {
        usort($tasks, fn(Task $a, Task $b) => $a->getOrdinality() <=> $b->getOrdinality());

        $tableRows = [];
        foreach ($tasks as $task) {
            $tableRows[] = [
                $task->getName(),
                $task->getState(),
                $task->getOrdinality(),
            ];
        }
        $this->io->table(['name', 'state', 'ordinality'], $tableRows);
    }

    private function tablelizeHeroStats(Hero $hero): void
    {
        $this->io->table(
            ['Stat', 'Value'],
            [
                ['Strength', $hero->getStrength()],
                ['Stamina', $hero->getStamina()],
                ['Intelligence', $hero->getIntelligence()],
                ['Dexterity', $hero->getDexterity()],
                ['Charisma', $hero->getCharisma()],
            ]
        );
    }
}
