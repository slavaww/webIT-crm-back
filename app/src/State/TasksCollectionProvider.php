<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Repository\TasksRepository;
use Symfony\Bundle\SecurityBundle\Security;

final class TasksCollectionProvider implements ProviderInterface
{
    private $repository;
    private $security;

    public function __construct(TasksRepository $repository, Security $security)
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
        // Проверяем статусы из query параметров
        $statuses = $filters['statuses'] ?? [];
        if (!empty($statuses)) {
            $statuses = explode(',', $statuses);
        }
        $create_start = $filters['create_start'] ?? null;
        $create_end = $filters['create_end'] ?? null;
        $worker = $filters['worker'] ?? null;
        $client = $filters['client'] ?? null;

        if (in_array('ROLE_SUPER_ADMIN', $roles)) {
            // Супер-админ видит все, отсортированные по дате создания
            if ( empty($statuses) && empty($create_start) && empty($create_end) && empty($worker) && empty($client) ) {
                // Если нет GET-параметров, то простой вывод задач
                return $this->repository->findBy([], ['create_date' => 'DESC']);
            }

            $qb = $this->repository->createQueryBuilder('t')
                    ->orderBy('t.create_date', 'DESC'); // Сортировка по дате создания

            if (!empty($worker)) {
                $qb->andWhere('t.worker = :worker');
                $qb->setParameter('worker', $worker);
            }

            if (!empty($client)) {
                $qb->andWhere('t.client = :client');
                $qb->setParameter('client', $client);
            }
            
        } elseif (in_array('ROLE_ADMIN', $roles)) {
            // Сотрудник видит только свои задачи (где worker === user), отсортированные
            if ( empty($statuses) && empty($create_start) && empty($create_end) && empty($client) ) {
                # Simle returm without GET-query
                return $this->repository->findBy(
                    ['worker' => $user->getEmployee()],
                    ['create_date' => 'DESC']
                );
            }
            
            $qb = $this->repository->createQueryBuilder('t')
                    ->orderBy('t.create_date', 'DESC') // Сортировка по дате создания
                    ->where('t.worker = :worker')
                    ->setParameter('worker', $user->getEmployee());

            if (!empty($client)) {
                $qb->andWhere('t.client = :client');
                $qb->setParameter('client', $client);
            }

        } elseif (in_array('ROLE_USER', $roles)) {
            // Клиент видит только свои задачи (где client === user)
            // return $this->repository->findBy(['client' => $user->getClient()]);
            $qb = $this->repository->createQueryBuilder('t')
                    ->orderBy('t.create_date', 'DESC') // Сортировка по дате создания
                    ->where('t.client = :client')
                    ->setParameter('client', $user->getClient());

            if (!empty($worker)) {
                if ($worker == 1) {
                    # worker is set
                    $qb->andWhere('t.worker IS NOT NULL');
                } else {
                    # worker is not set
                    $qb->andWhere('t.worker IS NULL');
                }
            }
        }

        /**
         * Выводим данные с GET-параметрами
         */
        if (!empty($statuses)) {
            $qb->andWhere('t.status IN (:statuses)')
            ->setParameter('statuses', $statuses);
        }

        if (!empty($create_start) && !empty($create_end)) {
            $qb->andWhere('t.create_date BETWEEN :create_start AND :create_end');
            $qb->setParameter('create_start', $create_start);
            $qb->setParameter('create_end', $create_end);
        } elseif (!empty($create_start)) {
            $qb->andWhere('t.create_date >= :create_start');
            $qb->setParameter('create_start', $create_start);
        } elseif (!empty($create_end)) {
            $qb->andWhere('t.create_date <= :create_end');
            $qb->setParameter('create_end', $create_end);
        }

        return $qb->getQuery()->getResult();

        // return []; // Нет доступа — пустой список
    }
}
