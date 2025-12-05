<?php
namespace App\State;

use ApiPlatform\State\ProviderInterface;
use ApiPlatform\Metadata\Operation;
use App\Repository\TimeSpendRepository;
use Symfony\Bundle\SecurityBundle\Security;

final class TimeSpendCurrentMonthProvider implements ProviderInterface
{
    private TimeSpendRepository $repo;
    private Security $security;

    public function __construct(TimeSpendRepository $repo, Security $security)
    {
        $this->repo = $repo;
        $this->security = $security;
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): array
    {
        $user = $this->security->getUser();
        $roles = $user->getRoles();

        // Проверяем фильтры из query параметров
        $filters = $context['filters'] ?? [];

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
            // По умолчанию — текущий месяц (как было)
            $now = new \DateTimeImmutable();
            $start = $now->modify('first day of this month')->setTime(0, 0, 0);
            $end = $now->modify('last day of this month')->setTime(23, 59, 59);
        }

        // Супер-админ видит все
        $qb = $this->repo->createQueryBuilder('t')
                ->where('t.date BETWEEN :start AND :end')
                ->setParameter('start', $start)
                ->setParameter('end', $end);

        // Сотрудник видит только свои записи
        if (in_array('ROLE_ADMIN', $roles)) {
            $qb->andWhere('t.worker = :worker')
                ->setParameter('worker', $user->getEmployee());
        }
        // Клиент видит только свои записи
        if (in_array('ROLE_USER', $roles)) {
            $qb->andWhere('t.client = :client')
                ->setParameter('client', $user->getClient());
        }

        if (!empty($filters['task'])) {
            $qb->andWhere('t.task = :task')
                ->setParameter('task', $filters['task']);
        }

        // Запрос с фильтрацией по date
        return $qb->getQuery()->getResult();
    }
}
