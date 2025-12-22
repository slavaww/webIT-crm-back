<?php
namespace App\State;

use ApiPlatform\State\ProviderInterface;
use ApiPlatform\Metadata\Operation;
use App\Repository\TimeSpendRepository;
use Symfony\Bundle\SecurityBundle\Security;

// use Psr\Log\LoggerInterface;

final class TimeSpendCurrentMonthProvider implements ProviderInterface
{
    private TimeSpendRepository $repo;
    private Security $security;
    // private LoggerInterface $logger;

    public function __construct(
            TimeSpendRepository $repo,
            Security $security,
            // LoggerInterface $logger
        )
    {
        $this->repo = $repo;
        $this->security = $security;
        // $this->logger = $logger;
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): array
    {
        $user = $this->security->getUser();
        $roles = $user->getRoles();

        // Проверяем фильтры из query параметров
        $filters = $context['filters'] ?? [];

        // Создаем QueryBuilder без начальных условий
        $qb = $this->repo->createQueryBuilder('t');

        // Фильтрация по роли (сотрудник видит только свои записи)
        if (in_array('ROLE_ADMIN', $roles)) {
            $qb->andWhere('t.worker = :worker')
                ->setParameter('worker', $user->getEmployee());
        }
        // Фильтрация по роли (клиент видит только свои записи)
        if (in_array('ROLE_USER', $roles)) {
            $qb->andWhere('t.client = :client')
                ->setParameter('client', $user->getClient());
        }

        if (!empty($filters['task'])) {
            $qb->andWhere('t.task = :task')
                ->setParameter('task', $filters['task']);
        }

        if (!empty($filters['comment'])) {
            // Этот фильтр должен работать независимо от даты
            $qb->andWhere('t.comment = :comment')
                ->setParameter('comment', $filters['comment']);
        } else {
            // Если фильтра по comment нет, применяем логику дат
            // Определяем даты начала и конца периода
            if (!empty($filters['start']) && !empty($filters['end'])) {
                $start = new \DateTimeImmutable($filters['start']);
                $end = new \DateTimeImmutable($filters['end']);
            } else if (!empty($filters['start'])) {
                $start = new \DateTimeImmutable($filters['start']);
                $now = new \DateTimeImmutable();
                $end = $now->modify('last day of this month')->setTime(23, 59, 59);
            } else if (!empty($filters['end'])) {
                $end = new \DateTimeImmutable($filters['end']);
                $now = new \DateTimeImmutable();
                $start = $now->modify('first day of this month')->setTime(0, 0, 0);
            } else {
                // По умолчанию — текущий месяц
                $now = new \DateTimeImmutable();
                $start = $now->modify('first day of this month')->setTime(0, 0, 0);
                $end = $now->modify('last day of this month')->setTime(23, 59, 59);
            }

            $qb->andWhere('t.date BETWEEN :start AND :end')
                ->setParameter('start', $start)
                ->setParameter('end', $end);
        }

        if (!empty($filters['total'])) {
            # We should return only total time
            $result = $qb->getQuery()->getResult();
            $total_time = 0;
            if (!empty($result)) {
                foreach ($result as $time) {
                    $total_time += $time->getTimeSpend();
                }
                return array( 'total' => $total_time );
            } else {
                return array( 'total' => 0 );
            }
        }

        // Запрос с фильтрацией по date
        return $qb->getQuery()->getResult();
    }
}
