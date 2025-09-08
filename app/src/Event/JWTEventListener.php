<?php

namespace App\Event;

use App\Repository\ClientsRepository;
use App\Repository\EmployeeRepository;
use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTCreatedEvent;
use Symfony\Component\HttpFoundation\RequestStack;

class JWTEventListener
{
    private ClientsRepository $clientsRepository;
    private EmployeeRepository $employeeRepository;

    public function __construct(ClientsRepository $clientsRepository, EmployeeRepository $employeeRepository)
    {
        $this->clientsRepository = $clientsRepository;
        $this->employeeRepository = $employeeRepository;
    }

    /**
     * @param JWTCreatedEvent $event
     *
     * @return void
     */
    public function onJWTCreated(JWTCreatedEvent $event): void
    {
        $user = $event->getUser();
        $payload = $event->getData();

        // Добавляем роли пользователя в payload
        $payload['roles'] = $user->getRoles();

        // Находим клиента, связанного с этим пользователем
        $client = $this->clientsRepository->findOneBy(['user_id' => $user]);
        $employee = $this->employeeRepository->findOneBy(['user_id' => $user]);

        // Если клиент найден, добавляем его ID в payload токена
        if ($client) {
            $payload['clientId'] = $client->getId();
        }

        if ($employee ) {
            $payload['employeeId'] = $employee->getId();
        }

        // Обновляем данные в событии
        $event->setData($payload);
    }
}
