<?php

declare(strict_types=1);

namespace App\Subscriber;

use App\Entity\Task;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Workflow\Event\Event;

class TrainTaskWorkflowSubscriber implements EventSubscriberInterface
{
    public function onLeaveTraining(Event $event): void
    {
        $task = $event->getSubject();
        if (!$task instanceof Task) {
            return;
        }

        $hero = $task->getQuest()->getHero();
        $hero->increaseStamina();
        $hero->increaseStrength();
        $hero->increaseDexterity();
        $hero->decreaseIntelligence();
    }

    /**
     * @return string[]
     */
    public static function getSubscribedEvents(): array
    {
        return [
            'workflow.train.leave.training' => 'onLeaveTraining',
        ];
    }
}
