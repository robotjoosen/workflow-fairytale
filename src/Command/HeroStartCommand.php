<?php

namespace App\Command;

use App\Entity\Task;
use App\Repository\HeroRepository;
use App\Repository\QuestRepository;
use App\Repository\TaskRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
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
        $this->io = new SymfonyStyle($input, $output);

        $hero = $this->io->choice('select your hero', $this->heroRepository->findAll());
        $this->io->info('You have just selected: ' . $hero);

        $quest = $this->io->choice('Select your quest!', $this->questRepository->findWithUncompletedTasks());
        $this->io->info('You have just selected: ' . $quest);

        $this->io->note('Current state of your journey');
        $this->tablelizeCurrentStateOfTasks($quest->getTasks());

        $tasks = $this->taskRepository->findUncompletedTaskByQuest($quest);
        foreach ($tasks as $task) {
            $this->io->info(sprintf('Your started the %s task', $task));
            $this->handleTask($task);
        }

        return Command::SUCCESS;
    }

    private function handleTask(Task $task): void
    {
        while (!in_array($task->getState(), ['completed', 'failed'])) {
            $workflow = $this->workflowRegistry->get($task, $task->getName());
            $transitions = $workflow->getEnabledTransitions($task);

            $this->io->block(
                $workflow->getMetadataStore()->getPlaceMetadata($task->getState())['story'],
                'Story',
                'info',
            );

            $transitionChoices = ['quit'];
            foreach ($transitions as $transition) {
                $transitionChoices = [$transition->getName(), ...$transitionChoices];
            }
            $selectedTransition = $this->io->choice('What is next?', $transitionChoices);

            if ($selectedTransition === 'quit') {
                $this->io->warning('Goodbye, see you next time!');

                exit;
            }

            $workflow->apply($task, $selectedTransition ?? (reset($transitions))->getName());
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
}
