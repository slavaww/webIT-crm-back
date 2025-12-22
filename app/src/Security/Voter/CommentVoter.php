<?php

namespace App\Security\Voter;

use App\Entity\Comments;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Bundle\SecurityBundle\Security;

class CommentVoter extends Voter
{
    const COMMENT_VIEW = 'COMMENT_VIEW';
    const COMMENT_EDIT = 'COMMENT_EDIT';
    const COMMENT_DELETE = 'COMMENT_DELETE';
    const COMMENT_CREATE = 'COMMENT_CREATE';

    private $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    protected function supports(string $attribute, mixed $subject): bool
    {
        $supportsAttribute = in_array($attribute, [self::COMMENT_VIEW, self::COMMENT_EDIT, self::COMMENT_DELETE, self::COMMENT_CREATE]);
        $supportsSubject = $subject instanceof Comments || $attribute === self::COMMENT_CREATE;

        return $supportsAttribute && $supportsSubject;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();

        if (!$user instanceof UserInterface) {
            return false;
        }

        $roles = $user->getRoles();

        /** @var Comments $comment */
        $comment = $subject;

        switch ($attribute) {
            case self::COMMENT_CREATE:
                return in_array('ROLE_USER', $roles) || in_array('ROLE_ADMIN', $roles) || in_array('ROLE_SUPER_ADMIN', $roles);
            case self::COMMENT_VIEW:
            case self::COMMENT_EDIT:

                /** @var Tasks $task */
                $task = $comment->getTask();
                if (in_array('ROLE_SUPER_ADMIN', $roles)) {
                    return true;
                }

                // Пользователь может видеть/редактировать комментарии к своим задачам (через связь task)
                if ($task) {
                    if (in_array('ROLE_ADMIN', $roles)) {
                        return $task->getWorker()?->getUserId() === $user;
                    }
                    if (in_array('ROLE_USER', $roles)) {
                        return $task->getClient()?->getUserId() === $user;
                    }
                }

                return false;
            case self::COMMENT_DELETE:
                // Comments with time spent cannot be deleted (include super admin)
                if (!$comment->getTimeSpends()->isEmpty()) {
                    return false;
                }
                
                // ROLE_SUPER_ADMIN can delete any comment
                if ($this->security->isGranted('ROLE_SUPER_ADMIN')) {
                    return true;
                }
                
                // ROLE_ADMIN and ROLE_USER can delete their own comments
                // Assuming $comment->getAuthor() returns the user who created the comment.
                return $comment->getAuthor() === $user;
        }

        return false;
    }
}
