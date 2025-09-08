<?php

namespace App\Security\Voter;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;
use App\Entity\Employee;

class EmloyeeVoter extends Voter
{
    const EMPLOYEE_EDIT = 'EMPLOYEE_EDIT';
    const EMPLOYEE_VIEW = 'EMPLOYEE_VIEW';

    protected function supports(string $attribute, mixed $subject): bool
    {
        return $attribute === self::EMPLOYEE_EDIT
            || $attribute === self::EMPLOYEE_VIEW
            && $subject instanceof \App\Entity\Employee;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();

        if (!$user instanceof UserInterface) {
            return false;
        }

        $roles = $user->getRoles();

        if (in_array('ROLE_SUPER_ADMIN', $roles)) {
            return true;
        }

        if (in_array('ROLE_ADMIN', $roles)) {
            /** @var Employee $employee */
            $employee = $subject;
            return $employee->getUserId() === $user;
        }

        return false;
    }
}