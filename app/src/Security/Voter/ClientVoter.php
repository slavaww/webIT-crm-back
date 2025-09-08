<?php

namespace App\Security\Voter;

use App\Entity\Clients;
use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

class ClientVoter extends Voter
{
    const CLIENT_EDIT = 'CLIENT_EDIT';

    protected function supports(string $attribute, mixed $subject): bool
    {
        // Мы "голосуем" только по разрешению CLIENT_EDIT
        // и только для объектов типа Clients
        return $attribute === self::CLIENT_EDIT
            && $subject instanceof \App\Entity\Clients;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();

        // Если пользователь не авторизован, доступ запрещен
        if (!$user instanceof UserInterface) {
            return false;
        }

        /** @var Clients $client */
        $client = $subject;

        // Проверяем, является ли текущий пользователь тем, кто связан с этим профилем клиента
        return $client->getUserId() === $user;
    }
}
