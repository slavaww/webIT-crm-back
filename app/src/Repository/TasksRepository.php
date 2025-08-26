<?php

namespace App\Repository;

use App\Entity\Tasks;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Tasks>
 */
class TasksRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Tasks::class);
    }

       /**
        * @return int Task Client ID or Null
        */
       public function findByIdClient($id): ?int
       {
            $result = $this->createQueryBuilder('t')
               ->select('IDENTITY(t.client) AS client')
               ->andWhere('t.id = :id')
               ->setParameter('id', $id)
               ->orderBy('t.id', 'ASC')
               ->getQuery()
               ->getOneOrNullResult();
            return $result ? (int) $result['client'] : null;
       }

       public function findByIdWorker($id): ?int
       {
            $result = $this->createQueryBuilder('t')
               ->select('IDENTITY(t.worker) AS worker')
               ->andWhere('t.id = :id')
               ->setParameter('id', $id)
               ->orderBy('t.id', 'ASC')
               ->getQuery()
               ->getOneOrNullResult();
            return $result ? (int) $result['worker'] : null;
       }

    //    public function findOneBySomeField($value): ?Tasks
    //    {
    //        return $this->createQueryBuilder('t')
    //            ->andWhere('t.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
