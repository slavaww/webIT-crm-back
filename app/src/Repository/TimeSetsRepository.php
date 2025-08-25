<?php

namespace App\Repository;

use App\Entity\TimeSets;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<TimeSets>
 */
class TimeSetsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TimeSets::class);
    }

    public function getMonthlySum(): array
    {
        return $this->createQueryBuilder('ts')
            ->select('YEAR(ts.date) AS year, MONTH(ts.date) AS month, ts.client as client, SUM(ts.timeSpend) AS totalSum')
            ->groupBy('year, month, ts.client')
            ->getQuery()
            ->getResult();
    }

    public function findTimeSetsWithTimeSpend(): array
    {
        return $this->createQueryBuilder('t')
            ->select('IDENTITY(t.client) AS client', 't.year', 't.month', 't.time_set', 't.timeSpend')
            ->where('t.timeSpend IS NOT NULL')
            ->getQuery()
            ->getResult();
    }

    //    /**
    //     * @return TimeSets[] Returns an array of TimeSets objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('t')
    //            ->andWhere('t.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('t.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?TimeSets
    //    {
    //        return $this->createQueryBuilder('t')
    //            ->andWhere('t.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
