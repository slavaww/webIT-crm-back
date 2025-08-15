<?php

namespace App\Repository;

use App\Entity\TimeSpend;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<TimeSpend>
 */
class TimeSpendRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TimeSpend::class);
    }

    public function getMonthlyClientSum(): array
    {
        return $this->createQueryBuilder('ts')
        ->select('SUBSTRING(ts.date, 1, 4) AS year, SUBSTRING(ts.date, 6, 2) AS month, IDENTITY(ts.client) AS client_id, SUM(ts.time_spend) AS totalSum')
        ->groupBy('year, month, ts.client')
        ->getQuery()
        ->getResult();
    }
    //    /**
    //     * @return TimeSpend[] Returns an array of TimeSpend objects
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

    //    public function findOneBySomeField($value): ?TimeSpend
    //    {
    //        return $this->createQueryBuilder('t')
    //            ->andWhere('t.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
