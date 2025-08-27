<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Entity\Comments;
use Symfony\Bundle\SecurityBundle\Security;
use App\Repository\CommentsRepository;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Get;
use App\Repository\TasksRepository;
use Monolog\Logger;
use Psr\Log\LoggerInterface;

final class CommentsCollectionProvider implements ProviderInterface
{
    private $repository;
    private $security;
    private $tasks;
    private $logger;


    public function __construct(
            CommentsRepository $repository,
            Security $security,
            TasksRepository $tasks,
            LoggerInterface $logger
        )
    {
        $this->repository = $repository;
        $this->security = $security;
        $this->tasks = $tasks;
        $this->logger = $logger;
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): array
    {
        $this->logger->info('RRRRRRRRR 1');
        if ($operation instanceof GetCollection || $operation instanceof Get) {
            $this->logger->info('RRRRRRRRR 2');
            
            $user = $this->security->getUser();
            $roles = $user->getRoles();
            
            if (in_array('ROLE_SUPER_ADMIN', $roles)) {
                $this->logger->info('RRRRRRRRR 3');
                // Супер-админ видит все
                return $this->repository->findAll();
            } elseif (in_array('ROLE_ADMIN', $roles)) {
                // Сотрудник видит только свои записи
                $worker = $user->getEmployee();
                $task_worker_id = $this->tasks->findByIdWorker($uriVariables['id']);

                if (!empty($worker) && !empty($task_worker_id)) {
                    $worker_id = (int) $worker->getId();

                    if (!empty($task_worker_id) && $worker_id === $task_worker_id) {
                        return $this->repository->findAll();
                    }
                }

            } elseif (in_array('ROLE_USER', $roles)) {

                $client = $user->getClient();
                $task_client_id = $this->tasks->findByIdClient($uriVariables['id']);

                if (!empty($client) && !empty($task_client_id)) {
                    $client_id = (int) $client->getId();

                    if (!empty($task_client_id) && $client_id === $task_client_id) {
                        return $this->repository->findAll();
                    }
                }
            }
        }
        $this->logger->info('RRRRRRRRR 4');

        return []; // Нет доступа — пустой список
    }
}