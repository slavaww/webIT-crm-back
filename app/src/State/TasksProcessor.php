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
    private LoggerInterface $logger; // Remove later!!!

    public function __construct(
        private ProcessorInterface $persistProcessor,
        private Security $security,
        LoggerInterface $logger // Remove later!!!
    ) {
        $this->logger = $logger; // Remove later!!!
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = [])
    {
        $this->logger->debug('WWWWWWWW. Process ranning!!! Operation: ' . $operation->getName()); // Remove later!!!

        if ( $data instanceof Tasks && $operation instanceof Post ) {
            $this->logger->debug('WWWWWWWW. IF ranning!!!'); // Remove later!!!

            $data->setCreator($this->security->getUser());
        }

        return $this->persistProcessor->process($data, $operation, $uriVariables, $context);
    }
}