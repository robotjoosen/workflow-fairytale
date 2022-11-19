<?php

declare(strict_types=1);

namespace App\Subscriber;

use App\Entity\Task;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Workflow\Event\Event;

class WanderTaskWorkflowSubscriber implements EventSubscriberInterface
{
    public function onLeaveTraveling(Event $event): void
    {
        $task = $event->getSubject();
        if (!$task instanceof Task) {
            return;
        }

        $hero = $task->getQuest()->getHero();
        $hero->increaseIntelligence();
        $hero->increaseStamina();
        $hero->decreaseStrength();
    }

    /**
     * @return string[]
     */
    public static function getSubscribedEvents(): array
    {
        return [
            'workflow.wander.leave.traveling' => 'onLeaveTraveling',
        ];
    }
}
