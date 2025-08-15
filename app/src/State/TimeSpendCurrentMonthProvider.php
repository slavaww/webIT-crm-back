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
        // Сотрудник видит только свои записи
        if (in_array('ROLE_ADMIN', $roles)) {
            return $this->repo->createQueryBuilder('t')
                    ->where('t.date BETWEEN :start AND :end AND t.worker = :worker')
                    ->setParameter('start', $start)
                    ->setParameter('end', $end)
                    ->setParameter('worker', $user->getEmployee())
                    ->getQuery()
                    ->getResult();
        }
        // Клиент видит только свои записи
        if (in_array('ROLE_USER', $roles)) {
            return $this->repo->createQueryBuilder('t')
                ->where('t.date BETWEEN :start AND :end AND t.client = :client')
                ->setParameter('start', $start)
                ->setParameter('end', $end)
                ->setParameter('client', $user->getClient())
                ->getQuery()
                ->getResult();
        }

        // Запрос с фильтрацией по date
        return $this->repo->createQueryBuilder('t')
            ->where('t.date BETWEEN :start AND :end')
            ->setParameter('start', $start)
            ->setParameter('end', $end)
            ->getQuery()
            ->getResult();
    }
}
