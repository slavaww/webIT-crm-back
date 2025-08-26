<?php

namespace App\State;

use ApiPlatform\State\ProviderInterface;
use ApiPlatform\Metadata\Operation;
use App\Repository\TimeSetsRepository;
use Symfony\Bundle\SecurityBundle\Security;
use App\Entity\User;

final class TimeSetsCollectionProvider implements ProviderInterface
{
    private TimeSetsRepository $repository;
    private Security $security; 

    public function __construct(TimeSetsRepository $repository, Security $security)
    {
        $this->repository = $repository;
        $this->security = $security;
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): array
    {
        $user = $this->security->getUser();
        $roles = $user->getRoles();

        // Проверяем фильтры из query параметров
        $filters = $context['filters'] ?? [];

        if (empty($filters['month'])) {
            # Set current month
            $now = new \DateTime();
            $filters['month'] = (int)$now->format('n');
        }

        if (empty($filters['year'])) {
            # Set current year
            $now = new \DateTime();
            $filters['year'] = (int)$now->format('Y');
        }

        if (in_array('ROLE_SUPER_ADMIN', $roles) || in_array('ROLE_ADMIN', $roles)) {
            # Супер-админ & Сотрудник видит все
            if (empty($filters['client'])) {
                return $this->repository->createQueryBuilder('t')
                    ->where('t.year = :year AND t.month = :month')
                    ->setParameter('year', $filters['year'])
                    ->setParameter('month', $filters['month'])
                    ->getQuery()
                    ->getResult();
            }
            return $this->repository->createQueryBuilder('t')
                ->where('t.year = :year AND t.month = :month AND t.client = :client')
                ->setParameter('year', $filters['year'])
                ->setParameter('month', $filters['month'])
                ->setParameter('client', $filters['client'])
                ->getQuery()
                ->getResult();
        } elseif (in_array('ROLE_USER', $roles)) {
            # Клиент видит только свои записи
            return $this->repository->createQueryBuilder('t')
                ->where('t.year = :year AND t.month = :month AND t.client = :client')
                ->setParameter('year', $filters['year'])
                ->setParameter('month', $filters['month'])
                ->setParameter('client', $user->getClient())
                ->getQuery()
                ->getResult();
        }

        return [];

    }
}