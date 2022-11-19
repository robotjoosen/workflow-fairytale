<?php

namespace App\DataFixtures;

use App\Entity\Hero;
use App\Entity\Quest;
use App\Entity\Task;
use App\Model\HeroStats;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    private ObjectManager $manager;

    public function load(ObjectManager $manager): void
    {
        $this->manager = $manager;

        $this->createFirstHero();
        $this->createSecondHero();



        $manager->flush();
    }

    private function createFirstHero(): void
    {
        $statsModel = new HeroStats(80, 40, 70, 50, 80);
        $hero = $this->createHero('Smashers Inc.', 'Warrior', $statsModel);

        $quest1 = $this->createQuest($hero, 'The beginning', 'A hero is born', 10);
        $quest1->addTask($this->createTask('First training', 'train', 10));
        $quest1->addTask($this->createTask('Soul searching', 'wander', 20));

        $quest2 = $this->createQuest($hero, 'Not again', 'Already tired from the first one', 20);
        $quest2->addTask($this->createTask('More training', 'train', 10));
        $quest2->addTask($this->createTask('Walk around', 'wander', 20));
        $quest2->addTask($this->createTask('Kick Ass', 'train', 30));
        $quest2->addTask($this->createTask('Slay Dragon', 'slay_dragon', 40));
    }

    private function createSecondHero(): void
    {
        $statsModel = new HeroStats(20, 75, 10, 100, 50);
        $hero = $this->createHero('The grey mass', 'Writer', $statsModel);

        $quest2 = $this->createQuest($hero, 'The accident', 'Stumbled on something unpleasant', 20);
        $quest2->addTask($this->createTask('Search', 'wander', 10));
        $quest2->addTask($this->createTask('Search', 'wander', 20));
        $quest2->addTask($this->createTask('Search some more', 'slay_dragon', 30));
    }

    private function createHero(string $name, string $type, HeroStats $stats): Hero
    {
        $hero = new Hero();
        $hero->setName($name);
        $hero->setType($type);
        $hero->setStrength($stats->getStrength());
        $hero->setCharisma($stats->getCharisma());
        $hero->setDexterity($stats->getDexterity());
        $hero->setIntelligence($stats->getIntelligence());
        $hero->setStamina($stats->getStamina());

        $this->manager->persist($hero);

        return $hero;
    }

    private function createQuest(Hero $hero, string $name, string $description, int $ordinality = 0): Quest
    {
        $quest = new Quest();
        $quest->setName($name);
        $quest->setDescription($description);
        $quest->setHero($hero);
        $quest->setOrdinality($ordinality);

        $this->manager->persist($quest);

        return $quest;
    }

    private function createTask(string $name, string $workflow, int $ordinality = 0): Task
    {
        $task = new Task();
        $task->setName($name);
        $task->setOrdinality($ordinality);
        $task->setWorkflow($workflow);

        $this->manager->persist($task);

        return $task;
    }
}
