<?php

namespace App\Controller;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Serializer\SerializerInterface;
use Doctrine\ORM\EntityManagerInterface;
// use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;

#[Route('/api/me')]
#[IsGranted('IS_AUTHENTICATED_FULLY')]
class MeController extends AbstractController
{
    #[Route('', name: 'api_me_get', methods: ['GET'])]
    public function getMe(): Response
    {
        // Возвращаем данные текущего пользователя, используя группу 'user:read'
        return $this->json($this->getUser(), Response::HTTP_OK, [], ['groups' => 'user:read']);
    }

    #[Route('', name: 'api_me_patch', methods: ['PATCH'])]
    public function patchMe(
        Request $request,
        SerializerInterface $serializer,
        EntityManagerInterface $em,
        UserPasswordHasherInterface $passwordHasher
    ): Response {
        /** @var User|null $user */
        $user = $this->getUser();

        // Десериализуем входящие данные в существующий объект пользователя
        $serializer->deserialize($request->getContent(), get_class($user), 'json', [
            'object_to_populate' => $user,
            'groups' => 'user:write'
        ]);

        // Проверяем, был ли отправлен новый пароль
        $data = json_decode($request->getContent(), true);
        if (isset($data['password']) && !empty($data['password'])) {
            $hashedPassword = $passwordHasher->hashPassword($user, $data['password']);
            $user->setPassword($hashedPassword);
        }

        $em->flush();

        return $this->json($user, Response::HTTP_OK, [], ['groups' => 'user:read']);
    }
}
