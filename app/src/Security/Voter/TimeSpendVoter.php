<?php

namespace App\Security\Voter;

use App\Entity\TimeSpend;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Bundle\SecurityBundle\Security;

class TimeSpendVoter extends Voter
{
    const TIME_SPEND_VIEW = 'TIME_SPEND_VIEW';
    const TIME_SPEND_EDIT = 'TIME_SPEND_EDIT';
    const TIME_SPEND_DELETE = 'TIME_SPEND_DELETE';
    const TIME_SPEND_CREATE = 'TIME_SPEND_CREATE';
    private $security = null;

    public function __construct(
        Security $security,
    )
    {
        $this->security = $security;
    }

    protected function supports(string $attribute, mixed $subject): bool
    {
        $supportsAttribute = in_array($attribute, [self::TIME_SPEND_VIEW, self::TIME_SPEND_EDIT, self::TIME_SPEND_DELETE, self::TIME_SPEND_CREATE]);
        $supportsSubject = $subject instanceof TimeSpend || $attribute === self::TIME_SPEND_CREATE;

        return $supportsAttribute && $supportsSubject;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();

        if (!$user instanceof UserInterface) {
            return false;
        }

        $roles = $user->getRoles();
        $time_spend = $subject;

        // Логика для каждой операции
        switch ($attribute) {
            case self::TIME_SPEND_CREATE:
                // Создать время может только работник или Админ
                return in_array(['ROLE_USER', 'ROLE_ADMIN'], $roles);
            case self::TIME_SPEND_VIEW:
                // Смотреть время может любой залогиненный, но только свое
                if (in_array('ROLE_SUPER_ADMIN', $roles)) {
                    return true; // Супер-админ видит всё
                }
                if (in_array('ROLE_ADMIN', $roles)) {
                    // Сотрудник видит только свои записи
                    return $time_spend->getWorker()?->getUserId() === $user;
                }
                if (in_array('ROLE_USER', $roles)) {
                    // Клиент видит только свои записи
                    return $time_spend->getClient()?->getUserId() === $user;
                }
                return false;
            case self::TIME_SPEND_EDIT:
            case self::TIME_SPEND_DELETE:
                if (in_array('ROLE_SUPER_ADMIN', $roles)) {
                    // Редактировать время может супер-админ
                    return true;
                }
                if (in_array('ROLE_ADMIN', $roles)) {
                    // Сотрудник может редактировать только свои записи
                    return $time_spend->getWorker()?->getUserId() === $user;
                }
                return false;
        }


        return false;
    }
}