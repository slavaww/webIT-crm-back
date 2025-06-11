<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserRegistrationType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/settings')]
#[IsGranted('ROLE_SUPER_ADMIN')]
class SettingsController extends AbstractController
{
    #[Route('/', name: 'app_settings')]
    public function index(
        Request $request,
        EntityManagerInterface $em,
        UserPasswordHasherInterface $passwordHasher
    ): Response {
        $user = new User();
        $form = $this->createForm(UserRegistrationType::class, $user, [
            'is_edit' => false
        ]);
        
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
            // Хешируем пароль
            $hashedPassword = $passwordHasher->hashPassword(
                $user,
                $form->get('password')->getData()
            );
            $user->setPassword($hashedPassword);

            // Устанавливаем выбранную роль
            // $selectedRole = $form->get('roles')->getData();
            // $user->setRoles([$selectedRole]);
            
            $em->persist($user);
            $em->flush();
            
            $this->addFlash('success', 'Пользователь успешно создан!');
            return $this->redirectToRoute('app_settings');
        }
        
        // Получаем список всех пользователей
        $users = $em->getRepository(User::class)->findAll();
        
        return $this->render('settings/index.html.twig', [
            'form' => $form->createView(),
            'users' => $users,
        ]);
    }

    #[Route('/edit/{id}', name: 'app_user_edit')]
    public function edit(
        User $user,
        Request $request,
        EntityManagerInterface $em,
        UserPasswordHasherInterface $passwordHasher
    ): Response {
        $formData = [
            'email' => $user->getEmail(),
            'roles' => $user->getRoles()[0] ?? 'ROLE_USER',
        ];
        $form = $this->createForm(UserRegistrationType::class, $user, [
            'is_edit' => true
        ]);

        // Получаем текущую роль (первую из массива)
        // $currentRole = $user->getRoles()[0] ?? 'ROLE_USER';
        
        // Устанавливаем текущую роль в форму
        // $form->get('roles')->setData($currentRole);
        
        $form->setData($formData);
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
            // Если пароль был изменен
            if ($form->get('password')->getData()) {
                $hashedPassword = $passwordHasher->hashPassword(
                    $user,
                    $form->get('password')->getData()
                );
                $user->setPassword($hashedPassword);
            }
            
            // Обновляем роль
            // $selectedRole = $form->get('roles')->getData();
            // $user->setRoles([$selectedRole]);
            
            $em->flush();
            
            $this->addFlash('success', 'Пользователь обновлен!');
            return $this->redirectToRoute('app_settings');
        }
        
        return $this->render('settings/edit.html.twig', [
            'form' => $form->createView(),
            'user' => $user,
        ]);
    }

    #[Route('/delete/{id}', name: 'app_user_delete')]
    public function delete(User $user, EntityManagerInterface $em): Response
    {
        if ($user === $this->getUser()) {
            $this->addFlash('error', 'Вы не можете удалить себя!');
            return $this->redirectToRoute('app_settings');
        }

        $em->remove($user);
        $em->flush();
        
        $this->addFlash('success', 'Пользователь удален!');
        return $this->redirectToRoute('app_settings');
    }
}