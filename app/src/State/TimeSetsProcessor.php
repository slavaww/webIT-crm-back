<?php

namespace App\State;

use ApiPlatform\State\ProcessorInterface;
use ApiPlatform\Metadata\Operation;
use App\Entity\TimeSets;
use Symfony\Bundle\SecurityBundle\Security;
use ApiPlatform\Metadata\Post;
use Doctrine\ORM\EntityManagerInterface;

final class TimeSetsProcessor implements ProcessorInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private Security $security,
        private ProcessorInterface $persistProcessor,
        private TimeSets $timeSets
    )
    {
        $this->entityManager = $entityManager;
        $this->security = $security;
        $this->persistProcessor = $persistProcessor;
        $this->timeSets = $timeSets;
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = [])
    {
        $user = $this->security->getUser();
        $roles = $user->getRoles();

        if (!in_array('ROLE_SUPER_ADMIN', $roles)) {
            throw new \InvalidArgumentException('Никто кроме Администратора не может устанавливать время клиенту.');
        }

        if ($data instanceof TimeSets && $operation instanceof Post) {
            // Add client_id to TimeSets

            $year = $data->getYear();
            $month = $data->getMonth();
            $client = $data->getClient();

            if (!$client) {
                # Client have to present
                throw new \InvalidArgumentException('Клиент не указан.');
            }

            if (!$year) {
                # Set current year
                $now = new \DateTime();
                $year = (int)$now->format('Y');
                $data->setYear($year);
            }

            if (!$month) {
                # Set current month
                $now = new \DateTime();
                $month = (int)$now->format('n');
                $data->setMonth($month);
            }

            // Проверить, нет ли уже такой записи для этого клиента и месяца
            $exists = $this->entityManager->getRepository(TimeSets::class)->findOneBy([
                'year' => $year,
                'month' => $month,
                'client' => $client
            ]);

            if ($exists) {
                // Обновляем существующую запись
                $exists->setTimeSet((int)$data->getTimeSet());
                $this->entityManager->persist($exists);
                $this->entityManager->flush();
                return $exists;
            }

        }


        return $this->persistProcessor->process($data, $operation, $uriVariables, $context);
    }
}