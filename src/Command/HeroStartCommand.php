<?php

namespace App\Command;

use App\Entity\Hero;
use App\Entity\Quest;
use App\Entity\Task;
use App\Repository\HeroRepository;
use App\Repository\QuestRepository;
use App\Repository\TaskRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\ConsoleOutputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\String\UnicodeString;
use Symfony\Component\Workflow\Exception\InvalidArgumentException;
use Symfony\Component\Workflow\Registry;
use Symfony\Component\Workflow\Transition;

#[AsCommand(
    name: 'hero:start',
    description: 'Add a short description for your command',
)]
class HeroStartCommand extends Command
{
    private SymfonyStyle $io;

    private bool $dryRun = false;

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

    public function configure(): void
    {
        $this->addOption('dry-run', 'd', InputOption::VALUE_NONE, 'Don\'t store progress');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        if (!$output instanceof ConsoleOutputInterface) {
            throw new \LogicException('This command accepts only an instance of "ConsoleOutputInterface".');
        }

        $this->io = new SymfonyStyle($input, $output);
        $this->dryRun = $input->getOption('dry-run');

        $this->io->section('Choose your hero');
        $hero = $this->io->choice('select your hero', $this->heroRepository->findAll());
        $this->io->info('You have just selected: ' . $hero);

        $this->handleUncompletedQuests($hero);

        return Command::SUCCESS;
    }

    /**
     * @note pick uncompleted quests and handle its tasks
     */
    private function handleUncompletedQuests(Hero $hero): void
    {
        $uncompletedQuests = $this->questRepository->findWithUncompletedTasks($hero);
        while (!empty($uncompletedQuests)) {
            $quest = $this->selectQuest($uncompletedQuests);
            $this->showStats($quest);
            $this->handleUncompletedTasks($quest);
            $this->showStats($quest);
            $this->removeQuest($quest, $uncompletedQuests);
        }
    }

    private function handleUncompletedTasks(Quest $quest): void
    {
        $this->io->section('Time get started with your quest');

        $tasks = $this->taskRepository->findUncompletedTaskByQuest($quest);
        foreach ($tasks as $task) {
            $this->io->title(sprintf('You started: %s', $task));
            $this->handleTask($task);
        }
    }

    /**
     * @note interactively move task through workflow
     */
    private function handleTask(Task $task): void
    {
        while (!in_array($task->getState(), Task::FINAL_STATES)) {
            try {
                $workflow = $this->workflowRegistry->get($task, $task->getWorkflow());
            } catch (InvalidArgumentException $exception) {
                $this->io->warning($exception->getMessage());

                exit;
            }

            $placeMetadata = $workflow->getMetadataStore()->getPlaceMetadata($task->getState());
            $this->io->text($placeMetadata['story']);

            $enabledTransitions = $workflow->getEnabledTransitions($task);
            $selectedTransition = $this->selectTransition($enabledTransitions);
            if ($selectedTransition === 'quit') {
                $this->io->warning('Goodbye, see you next time!');

                exit;
            }

            $workflow->apply($task, $selectedTransition);

            if($this->dryRun === false) {
                $this->entityManager->flush();
            }

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

    /**
     * @param Transition[] $transitions
     */
    private function selectTransition(array $transitions): string
    {
        $transitionChoices = ['Quit'];
        foreach ($transitions as $transition) {
            $name = new UnicodeString($transition->getName());
            $transitionChoices = [$name->replace('_', ' ')->title(), ...$transitionChoices];
        }
        $selectedTransition = $this->io->choice('What do you do?', $transitionChoices);
        $selectedTransition = new UnicodeString($selectedTransition);

        return $selectedTransition->lower()->snake();
    }

    /**
     * @param Quest[] $quests
     */
    private function removeQuest(Quest $currentQuest, array &$quests): void
    {
        foreach ($quests as $key => $quest) {
            if ($quest->getId() === $currentQuest->getId()) {
                unset($quests[$key]);
            }
        }

        sort($quests);
    }

    /**
     * @param Quest[] $choices
     * @return Quest
     */
    private function selectQuest(array $choices): Quest
    {
        $this->io->section('Pick a quest');

        $quest = $this->io->choice('Select your quest!', $choices);

        $this->io->info('You have just selected: ' . $quest);

        return $quest;
    }

    private function showStats(Quest $quest): void
    {
        $this->io->info('Your progress');

        /** @var Hero $hero */
        $hero = $quest->getHero();
        $this->io->section('Current state of your journey');
        $this->io->title($hero->getName() . '\'s stats');
        $this->tablelizeHeroStats($hero);

        $this->io->title('Tasks');
        $this->tablelizeCurrentStateOfTasks($quest->getTasks());
    }
}
