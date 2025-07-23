<?php

namespace App\Security\Voter;

use App\Entity\Tasks;
// use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Bundle\SecurityBundle\Security;

use Psr\Log\LoggerInterface; // Remove later!!!

class TaskVoter extends Voter
{
    const TASK_VIEW = 'TASK_VIEW';
    const TASK_EDIT = 'TASK_EDIT';
    const TASK_DELETE = 'TASK_DELETE';
    const TASK_CREATE = 'TASK_CREATE';
    private $security = null;
    private LoggerInterface $logger; // Remove later!!!

    public function __construct(
        Security $security,
        LoggerInterface $logger // Remove later!!!
    )
    {
        $this->security = $security;
        $this->logger = $logger; // Remove later!!!
    }
    
    protected function supports(string $attribute, mixed $subject): bool
    {
        $this->logger->debug('WWWWWWWW. TaskVoter ranning!!! Supports: ' . $attribute); // Remove later!!!
        $supportsAttribute = in_array($attribute, [self::TASK_VIEW, self::TASK_EDIT, self::TASK_DELETE, self::TASK_CREATE]);
        $supportsSubject = $subject instanceof Tasks;

        return $supportsAttribute && $supportsSubject;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();

        $this->logger->debug('WWWWWWWW. TaskVoter ranning!!! voteOnAttribute'); // Remove later!!!

        if (!$user instanceof UserInterface) {
            return false;
        }

        $roles = $user->getRoles();

        // Логика для каждой операции
        switch ($attribute) {
            case self::TASK_CREATE:
                // Создать задачу может любой залогиненный (клиент, сотрудник, админ)
                return in_array('ROLE_USER', $roles) || in_array('ROLE_ADMIN', $roles) || in_array('ROLE_SUPER_ADMIN', $roles);
            case self::TASK_VIEW:
            case self::TASK_EDIT:
                /** @var Tasks $task */
                $task = $subject;

                if (in_array('ROLE_SUPER_ADMIN', $roles)) {
                    return true; // Супер-админ видит/редактирует всё
                }

                if (in_array('ROLE_ADMIN', $roles)) {
                    // Сотрудник видит/редактирует только свои задачи (где он worker)
                    return $task->getWorker()?->getUserId() === $user;
                }

                if (in_array('ROLE_USER', $roles)) {
                    // Клиент видит/редактирует только свои задачи (где он client)
                    return $task->getClient()?->getUserId() === $user;
                }

                return false;
            case self::TASK_DELETE:
                // Удалять может только супер-админ
                return in_array('ROLE_SUPER_ADMIN', $roles);
        }

        return false;
    }
}
