<?php

namespace App\State;

use App\Repository\ClientsRepository;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use Symfony\Bundle\SecurityBundle\Security;

final class ClientsCollectionProvider implements ProviderInterface
{
    private ClientsRepository $repository;
    private Security $security;

    public function __construct(ClientsRepository $repository, Security $security)
    {
        $this->repository = $repository;
        $this->security = $security;
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): array
    {
        $user = $this->security->getUser();
        $roles = $user->getRoles();

        if (in_array('ROLE_SUPER_ADMIN', $roles) || in_array('ROLE_ADMIN', $roles)) {
            // Супер-админ или Сотрудник видит все
            return $this->repository->findAll();
        } elseif (in_array('ROLE_USER', $roles)) {
            // Клиент видит только себя (где client === user)
            return $this->repository->findBy(['id' => $user->getClient()]); // Предполагаем, что у User есть getClient()
        }

        return []; // Нет доступа — пустой список
        }
}