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

        if (in_array('ROLE_SUPER_ADMIN', $roles)) {
            // Супер-админ видит все
            return $this->repository->findAll();
        } elseif (in_array('ROLE_ADMIN', $roles)) {
            // Сотрудник видит только свои задачи (где worker === user)
            return $this->repository->findBy(['worker' => $user->getEmployee()]); // Предполагаем, что у User есть getEmployee()
        } elseif (in_array('ROLE_USER', $roles)) {
            // Клиент видит только свои задачи (где client === user)
            return $this->repository->findBy(['client' => $user->getClient()]); // Предполагаем, что у User есть getClient()
        }

        return []; // Нет доступа — пустой список
    }
}
