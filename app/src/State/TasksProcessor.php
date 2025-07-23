<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use Symfony\Bundle\SecurityBundle\Security;
use App\Entity\Tasks;
use App\Entity\Statuses;
use App\Entity\Clients;
use App\Entity\Employee;
use Doctrine\ORM\EntityManagerInterface;
use ApiPlatform\Metadata\Post;

use Psr\Log\LoggerInterface; // Remove later!!!

final class TasksProcessor implements ProcessorInterface
{
    private $entityManager;
    private LoggerInterface $logger; // Remove later!!!

    public function __construct(
        private ProcessorInterface $persistProcessor,
        private Security $security,
        EntityManagerInterface $entityManager,
        LoggerInterface $logger // Remove later!!!
    )
    {
        $this->entityManager = $entityManager;
        $this->persistProcessor = $persistProcessor;
        $this->security = $security;
        $this->logger = $logger; // Remove later!!!
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = [])
    {
        $this->logger->debug('WWWWWWWW. Process ranning!!! Operation: ' . $operation->getName()); // Remove later!!!

        if ( $data instanceof Tasks && $operation instanceof Post ) {
            $this->logger->debug('WWWWWWWW. IF ranning!!!'); // Remove later!!!

            $user = $this->security->getUser();

            if (!$user) {
                throw new \InvalidArgumentException('Пользователь не аутентифицирован.');
            }

            // Register current user as creator of the task
            $data->setCreator($user);

            // Установка client (только для ROLE_USER)
            // Для ROLE_ADMIN и ROLE_SUPER_ADMIN client приходит из формы
            $roles = $user->getRoles();
            if (in_array('ROLE_USER', $roles)) {
                $client = $this->entityManager->getRepository(Clients::class)->findOneBy(['user_id' => $user]);
                if ($client) {
                    $data->setClient($client);
                } else {
                    throw new \InvalidArgumentException('Клиент не найден для текущего пользователя.');
                }
            }

            // Установка status по умолчанию (наименьший ID, "Создана")
            // если статус не указан в запросе
            if (!$data->getStatus()) {
                $defaultStatus = $this->entityManager->getRepository(Statuses::class)->findOneBy([], ['id' => 'ASC']);
                if ($defaultStatus) {
                    $data->setStatus($defaultStatus);
                } else {
                    throw new \InvalidArgumentException('Статус по умолчанию не найден.');
                }
            }
            
            // Установка worker (только для сотрудников)
            // Для ROLE_USER: null; для ROLE_SUPER_ADMIN: из формы
            if (in_array('ROLE_ADMIN', $roles)) {
                $employee = $this->entityManager->getRepository(Employee::class)->findOneBy(['user_id' => $user]);
                if ($employee) {
                    $data->setWorker($employee);
                } else {
                    throw new \InvalidArgumentException('Сотрудник не найден для текущего пользователя.');
                }
            }
        }

        return $this->persistProcessor->process($data, $operation, $uriVariables, $context);
    }
}