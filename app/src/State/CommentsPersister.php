<?php

namespace App\State;

use App\Entity\Comments;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use Symfony\Bundle\SecurityBundle\Security;
use ApiPlatform\Metadata\Post;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Tasks;

class CommentsPersister implements ProcessorInterface
{

    public function __construct(
        private EntityManagerInterface $entityManager,
        private Security $security,
        private ProcessorInterface $persistProcessor,
        private Tasks $task
    ) {
        $this->entityManager = $entityManager;
        $this->security = $security;
        $this->persistProcessor = $persistProcessor;
        $this->task = $task;
    }

    public function process($data, Operation $operation, array $uriVariables = [], array $context = [])
    {
        if ($data instanceof Comments && $operation instanceof Post) {

            $user = $this->security->getUser();
            $roles = $user->getRoles();
            
            if (!$user) {
                throw new \InvalidArgumentException('Пользователь не аутентифицирован.');
            }
            
            if (!$data->getTask()) {
                throw new \InvalidArgumentException('Задача не указана.');
            }

            if (in_array('ROLE_ADMIN', $roles)) {
                $task_worker = $data->getTask()->getWorker();
                if ($task_worker->getUserId() != $user) {
                    throw new \InvalidArgumentException('Пользователь может добавить комментарий только в свою задачу.');
                }
            }

            if (in_array('ROLE_USER', $roles)) {
                $task_client = $data->getTask()->getClient();
                if ($task_client->getUserId() != $user ) {
                    throw new \InvalidArgumentException('Пользователь может добавить комментарий только в свою задачу.');
                }
            }

            // Register current user as author of the comment
            $data->setAuthor($user);
            $data->setCreatedAt(new \DateTime());
        }

        return $this->persistProcessor->process($data, $operation, $uriVariables, $context);
    }
}