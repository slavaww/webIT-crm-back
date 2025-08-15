<?php

namespace App\State;

use ApiPlatform\State\ProcessorInterface;
use ApiPlatform\Metadata\Operation;
use App\Entity\TimeSpend;
use Symfony\Bundle\SecurityBundle\Security;
use ApiPlatform\Metadata\Post;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Tasks;

final class TimeSpendProcessor implements ProcessorInterface
{

    public function __construct(
        private Security $security,
        private ProcessorInterface $persistProcessor,
        private EntityManagerInterface $entityManager,
        private TimeSpend $timeSpend,
        private Tasks $task,
    )
    {
        $this->persistProcessor = $persistProcessor;
        $this->security = $security;
        $this->entityManager = $entityManager;
        $this->task = $task;
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = [])
    {
        if ($data instanceof TimeSpend && $operation instanceof Post) {
            // Add client_id and worker_id to TimeSpend
            $user = $this->security->getUser();
            $roles = $user->getRoles();

            if (in_array('ROLE_SUPER_ADMIN', $roles) || in_array('ROLE_ADMIN', $roles)) {
                $id_task = $data->getTask()->getId();
                $task_obj = $this->entityManager->getRepository(Tasks::class)->findOneBy(['id' => $id_task]);

                $client = $task_obj->getClient();
                $worker = $task_obj->getWorker();

                if (in_array('ROLE_ADMIN', $roles) && $worker->getUserId() != $user) {
                    throw new \InvalidArgumentException('Пользователь может добавить время только в свою задачу.');
                }
    
                $data->setClient($client);
                $data->setWorker($worker);

                // Если дата не указана, то устанавливаем текущую дату
                if (!$data->getDate()) {
                    $data->setDate(new \DateTime());
                }

            } else {
                throw new \InvalidArgumentException('Пользователь не может добавить время в задачу.');
            }

        }

        return $this->persistProcessor->process($data, $operation, $uriVariables, $context);
    }
}