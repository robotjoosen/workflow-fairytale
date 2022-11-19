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

        $statsModel = new HeroStats(8, 4, 7, 5, 8);

        $hero = $this->createHero('Hiro', 'Warrior', $statsModel);

        $quest1 = $this->createQuest($hero, 'The beginning', 'A hero is born', 20);
        $quest1->addTask($this->createTask('train', 10));
        $quest1->addTask($this->createTask('wander', 20));

        $quest2 = $this->createQuest($hero, 'Not again', 'Already tired from the first one', 10);
        $quest2->addTask($this->createTask('train', 10));
        $quest2->addTask($this->createTask('wander', 5));
        $quest2->addTask($this->createTask('train', 20));
        $quest2->addTask($this->createTask('slay_dragon', 30));

        $manager->flush();
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

    private function createTask(string $name, int $ordinality = 0): Task
    {
        $task = new Task();
        $task->setName($name);
        $task->setOrdinality($ordinality);

        $this->manager->persist($task);

        return $task;
    }
}
