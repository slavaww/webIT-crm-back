<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
final class LoginController extends AbstractController
{
    /**
     * Этот маршрут перехватывает все обращения к старой странице входа
     * и перенаправляет их на новую страницу входа в SPA.
     */
    #[Route('/login', name: 'app_login')]
    public function redirectToSpaLogin(): Response
    {
        // Если пользователь уже аутентифицирован (например, по сессии),
        // ему не нужно снова видеть страницу входа. Отправим его сразу в CRM.
        if ($this->getUser()) {
            return $this->redirectToRoute('app_crm');
        }

        // Перенаправляем на маршрут, который рендерит SPA,
        // передавая ему параметр, чтобы React Router открыл нужную страницу.
        return $this->redirectToRoute('app_crm', ['reactRoute' => 'login']);
    }

    #[Route(path: '/logout', name: 'app_logout')]
    public function logout(): void
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }

}
