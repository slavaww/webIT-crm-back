<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class SecurityController extends AbstractController
{
    #[Route('/api/login', name: 'api_login', methods: ['POST'])]
    public function login(): never
    {
        // Этот метод никогда не будет выполнен.
        // Запрос перехватывается файрволом json_login.
        // Мы выбрасываем исключение, чтобы быть уверенными, что если конфигурация
        // безопасности вдруг сломается, мы сразу это увидим.
        throw new \LogicException('This method should not be reached. It is handled by the security system.');
    }
}
