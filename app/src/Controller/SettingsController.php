<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Clients;
use App\Entity\Employee;
use App\Form\EmployeeType;
use App\Form\UserRegistrationType;
use App\Form\ClientRegistrationType;
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

    #[Route('/clients', name: 'app_clients')]
    public function clients(EntityManagerInterface $em): Response
    {
        $clients = $em->getRepository(Clients::class)->findAll();
        return $this->render('settings/clients/index.html.twig', [
            'clients' => $clients,
        ]);
    }

    #[Route('/clients/new', name: 'app_settings_clients_new')]
    public function newClient(
        Request $request,
        EntityManagerInterface $em
    ): Response {
        $client = new Clients();
        // Больше не нужно создавать пользователя вручную.
        // Форма сама подставит выбранный объект User в свойство $client->user_id.

        $form = $this->createForm(ClientRegistrationType::class, $client);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Больше не нужно хешировать пароль или устанавливать роль.
            // Мы просто сохраняем клиента с уже связанным пользователем.
            $em->persist($client);
            $em->flush();

            $this->addFlash('success', 'Клиент успешно создан и связан с пользователем!');
            return $this->redirectToRoute('app_clients');
        }

        return $this->render('settings/clients/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/clients/edit/{id}', name: 'app_settings_clients_edit')]
    public function editClient(
        Clients $client,
        Request $request,
        EntityManagerInterface $em
    ): Response {
        $form = $this->createForm(ClientRegistrationType::class, $client, [
            // Передаем ID текущего пользователя в форму
            'current_client_user_id' => $client->getUserId()->getId(),
        ]);
        
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            
            $em->flush();
            
            $this->addFlash('success', 'Данные клиента обновлены!');
            return $this->redirectToRoute('app_clients');
        }
        
        return $this->render('settings/clients/edit.html.twig', [
            'form' => $form->createView(),
            'client' => $client,
        ]);
    }

    #[Route('/clients/delete/{id}', name: 'app_settings_clients_delete')]
    public function deleteClient(Clients $client, EntityManagerInterface $em): Response
    {
        // Благодаря 'cascade: ['remove']' в сущности Clients,
        // при удалении клиента Doctrine удалит и связанного пользователя.
        $em->remove($client);
        $em->flush();

        $this->addFlash('success', 'Клиент удален!');
        return $this->redirectToRoute('app_clients');
    }


    /**
     * Employee settings
     *
     */

    #[Route('/employees', name: 'app_settings_employees')]
    public function employees(EntityManagerInterface $em): Response
    {
        $employees = $em->getRepository(Employee::class)->findAll();
        return $this->render('settings/employee/index.html.twig', [
            'employees' => $employees,
        ]);
    }

    #[Route('/employees/new', name: 'app_settings_employees_new')]
    public function newEmployee(Request $request, EntityManagerInterface $em): Response
    {
        $employee = new Employee();
        $form = $this->createForm(EmployeeType::class, $employee);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($employee);
            $em->flush();

            $this->addFlash('success', 'Сотрудник успешно создан!');
            return $this->redirectToRoute('app_settings_employees');
        }

        return $this->render('settings/employee/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/employees/edit/{id}', name: 'app_settings_employees_edit')]
    public function editEmployee(Employee $employee, Request $request, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(EmployeeType::class, $employee, [
            // Передаем ID текущего пользователя, чтобы он остался в списке
            'current_employee_user_id' => $employee->getUserId()->getId(),
        ]);
        
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            $this->addFlash('success', 'Данные сотрудника обновлены!');
            return $this->redirectToRoute('app_settings_employees');
        }

        return $this->render('settings/employee/edit.html.twig', [
            'form' => $form->createView(),
            'employee' => $employee,
        ]);
    }

    #[Route('/employees/delete/{id}', name: 'app_settings_employees_delete')]
    public function deleteEmployee(Employee $employee, EntityManagerInterface $em): Response
    {
        // Благодаря cascade: ['remove'] в сущности Employee,
        // связанный User также будет удален.
        // Если вы хотите просто "отвязать" пользователя, а не удалять,
        // уберите 'remove' из каскада в сущности.
        $em->remove($employee);
        $em->flush();

        $this->addFlash('success', 'Сотрудник удален!');
        return $this->redirectToRoute('app_settings_employees');
    }

}