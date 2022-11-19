<?php

namespace App\Repository;

use App\Entity\Quest;
use App\Entity\Task;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Task>
 *
 * @method Task|null find($id, $lockMode = null, $lockVersion = null)
 * @method Task|null findOneBy(array $criteria, array $orderBy = null)
 * @method Task[]    findAll()
 * @method Task[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TaskRepository extends ServiceEntityRepository
{
    public const ALIAS = 'task';

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Task::class);
    }

    public function getQueryBuilder(): QueryBuilder
    {
        return $this->createQueryBuilder(self::ALIAS);
    }

    /**
     * @return Task[]
     */
    public function findUncompletedTaskByQuest(Quest $quest): array
    {
        $queryBuilder = $this->getQueryBuilder();
        $queryBuilder->andWhere($queryBuilder->expr()->eq(self::ALIAS . '.quest', $quest->getId()));
        $queryBuilder->andWhere($queryBuilder->expr()->notIn(self::ALIAS . '.state', ['completed', 'failed']));
        $queryBuilder->orderBy(self::ALIAS . '.ordinality');

        return $queryBuilder->getQuery()->getResult();
    }
}
