<?php

namespace App\State;

use App\Entity\Comments;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use Symfony\Bundle\SecurityBundle\Security;
use ApiPlatform\Metadata\Post;
use Doctrine\ORM\EntityManagerInterface;

class CommentsPersister implements ProcessorInterface
{

    public function __construct(
        private EntityManagerInterface $entityManager,
        private Security $security,
        private ProcessorInterface $persistProcessor
    ) {
        $this->entityManager = $entityManager;
        $this->security = $security;
        $this->persistProcessor = $persistProcessor;
    }

    public function process($data, Operation $operation, array $uriVariables = [], array $context = [])
    {
        if ($data instanceof Comments && $operation instanceof Post) {

            $data->setAuthor($this->security->getUser());
            $data->setCreatedAt(new \DateTime());
        }

        return $this->persistProcessor->process($data, $operation, $uriVariables, $context);
    }
}