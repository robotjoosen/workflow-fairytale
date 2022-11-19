<?php

namespace App\Repository;

use App\Entity\Quest;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Quest>
 *
 * @method Quest|null find($id, $lockMode = null, $lockVersion = null)
 * @method Quest|null findOneBy(array $criteria, array $orderBy = null)
 * @method Quest[]    findAll()
 * @method Quest[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class QuestRepository extends ServiceEntityRepository
{
    public const ALIAS = 'quest';

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Quest::class);
    }

    public function getQueryBuilder(): QueryBuilder
    {
        return $this->createQueryBuilder(self::ALIAS);
    }

    /**
     * @return Quest[]
     */
    public function findWithUncompletedTasks(): array
    {
        $queryBuilder = $this->getQueryBuilder();
        $queryBuilder->leftJoin(self::ALIAS . '.tasks', 'task');
        $queryBuilder->andWhere($queryBuilder->expr()->notIn('task.state', ['completed', 'failed']));
        $queryBuilder->orderBy(self::ALIAS . '.ordinality');

        return $queryBuilder->getQuery()->getResult();
    }
}
