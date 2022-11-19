<?php

declare(strict_types=1);

namespace App\Subscriber;

use App\Entity\Task;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Workflow\Event\Event;
use Symfony\Component\Workflow\Registry;

class SlayDragonTaskWorkflowSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly Registry $workflowRegistry,
    ) {
    }

    public function onCompletedSlaying(Event $event): void
    {
        $task = $event->getSubject();
        if (!$task instanceof Task) {
            return;
        }

        $workflow = $this->workflowRegistry->get($task, $task->getName());
        $enabledTransitions = $workflow->getEnabledTransitions($task);

        $diceRoll = mt_rand(0, count($enabledTransitions) - 1);
        $workflow->apply($task, $enabledTransitions[$diceRoll]->getName());
    }

    public static function getSubscribedEvents()
    {
        return [
            'workflow.slay_dragon.completed.slay' => 'onCompletedSlaying',
        ];
    }
}
