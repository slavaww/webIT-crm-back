<?php

namespace App\Event;

use App\Repository\ClientsRepository;
use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTCreatedEvent;
use Symfony\Component\HttpFoundation\RequestStack;

class JWTEventListener
{
    private ClientsRepository $clientsRepository;

    public function __construct(ClientsRepository $clientsRepository)
    {
        $this->clientsRepository = $clientsRepository;
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

        // Находим клиента, связанного с этим пользователем
        $client = $this->clientsRepository->findOneBy(['user_id' => $user]);

        // Если клиент найден, добавляем его ID в payload токена
        if ($client) {
            $payload['clientId'] = $client->getId();
        }

        $event->setData($payload);
    }
}
