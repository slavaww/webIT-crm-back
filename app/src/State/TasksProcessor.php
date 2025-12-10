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
use ApiPlatform\Metadata\Patch;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

use Psr\Log\LoggerInterface;

final class TasksProcessor implements ProcessorInterface
{
    
    public function __construct(
        private ProcessorInterface $persistProcessor,
        private Security $security,
        private EntityManagerInterface $entityManager,
        private LoggerInterface $logger
    )
    {
        // Конструктор с автоматическим внедрением свойств (property promotion) в PHP 8+
        // не требует дополнительного присваивания в теле.
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = [])
    {
        $user = $this->security->getUser();
        if (!$user) {
            throw new \InvalidArgumentException('Пользователь не аутентифицирован.');
        }
        $roles = $user->getRoles();

        if ( $data instanceof Tasks && $operation instanceof Post ) {
            // Register current user as creator of the task
            $data->setCreator($user);

            // Установка client (только для ROLE_USER)
            // Для ROLE_ADMIN и ROLE_SUPER_ADMIN client приходит из формы
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
        
        if ($data instanceof Tasks && $operation instanceof Patch) {
            # Edit fields of the task

            /** @var Request|null $request */
            $request = $context['request'] ?? null;
            if ($request) {
                $patch_data = json_decode($request->getContent(), true);
                // $this->logger->debug('PATCH data received.', ['data' => $patch_data]);
            }
            
            if (in_array('ROLE_USER', $roles)) {
                // "Белый список" полей, которые ROLE_USER может изменять.
                // Все остальные поля будут запрещены для редактирования.
                $allowed_fields = [
                    'status',
                    'description',
                    'title',
                    'endTime'
                ];
                // Check if status sets permissions for clients
                $forbidden_statuses = [2, 3, 5];
            }
            if (in_array('ROLE_ADMIN', $roles)) {
                // "Белый список" полей, которые ROLE_USER может изменять.
                // Все остальные поля будут запрещены для редактирования.
                $allowed_fields = [
                    'status',
                ];

                if ($user == $data->getCreator()) {
                    # Add permissions if worker is creator of this task
                    $allowed_fields = array_merge($allowed_fields, [
                        'description',
                        'title',
                        'endTime'
                    ]);
                }
                // Check if status sets permissions for workers
                $forbidden_statuses = [6];
            }
            
            if ($request && $patch_data) {
                if (!empty($allowed_fields)) {
                    # Check allowed fields for workers and clients
                    # SuperAdmin can all
                    $requested_fields = array_keys($patch_data);
                    $forbidden_fields_attempt = array_diff($requested_fields, $allowed_fields);
                    
                    if (isset($patch_data['status'])) {
                        if (in_array($this->getStatusFromRequest($patch_data['status']), $forbidden_statuses)) {
                            $forbidden_fields_attempt['status'] = $this->getStatusFromRequest($patch_data['status']);
                        }
                    }
                }

            } else {
                # Something is wrong
                throw new \InvalidArgumentException('Something is wrong.');
            }

            if (!empty($forbidden_fields_attempt)) {
                throw new AccessDeniedHttpException('У вас нет прав на изменение следующих полей: ' . implode(', ', $forbidden_fields_attempt));
            }

            if (
                isset($patch_data['status'])
                && $this->getStatusFromRequest($patch_data['status'])
                && $this->getStatusFromRequest($patch_data['status']) >= 2
                && empty($data->getStartTime())
                ) {
                # Set current time to start_time field if status >= 2
                $now = new \DateTime();
                $data->setStartTime($now);
            }

            if (isset($patch_data['endTime'])) {
                if ($patch_data['endTime'] == 1 && empty($data->getEndTime()) ) {
                    # Close current task by setting curent date
                    $now = new \DateTime();
                    $data->setEndTime($now);
                }
                if ($patch_data['endTime'] == 2) {
                    # Reopen current task
                    $data->setEndTime(null);
                }
            }
            
        }

        return $this->persistProcessor->process($data, $operation, $uriVariables, $context);
    }

    public function getStatusFromRequest($status): int | false {
        $str_needle = '/api/statuses/';

        if ($status) {
            if (str_contains($status, $str_needle)) {
                return (int) str_replace($str_needle, '', $status);
            }
        }

        return false;
    }

}