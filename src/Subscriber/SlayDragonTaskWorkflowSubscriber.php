<?php

declare(strict_types=1);

namespace App\Subscriber;

use App\Entity\Task;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Workflow\Event\Event;
use Symfony\Component\Workflow\Exception\InvalidArgumentException;
use Symfony\Component\Workflow\Registry;

class SlayDragonTaskWorkflowSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly Registry $workflowRegistry,
        private readonly LoggerInterface $logger,
    ) {
    }

    public function onCompletedSlaying(Event $event): void
    {
        $task = $event->getSubject();
        if (!$task instanceof Task) {
            return;
        }

        try {
            $workflow = $this->workflowRegistry->get($task, $task->getWorkflow());
        } catch (InvalidArgumentException $exception) {
            $this->logger->critical('Workflow for task not found', [
                'exception' => $exception->getMessage(),
            ]);

            return;
        }

        /**
         * @note pick a random transition, which in this case is win or lose
         */
        $enabledTransitions = $workflow->getEnabledTransitions($task);
        $diceRoll = mt_rand(0, count($enabledTransitions) - 1);
        $workflow->apply($task, $enabledTransitions[$diceRoll]->getName());
    }

    /**
     * @return string[]
     */
    public static function getSubscribedEvents(): array
    {
        return [
            'workflow.slay_dragon.completed.slay' => 'onCompletedSlaying',
        ];
    }
}
