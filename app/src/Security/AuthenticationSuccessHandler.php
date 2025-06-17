<?php

namespace App\Security;

use Lexik\Bundle\JWTAuthenticationBundle\Security\Http\Authentication\AuthenticationSuccessHandler as LexikSuccessHandler;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Http\SecurityRequestAttributes;

class AuthenticationSuccessHandler extends LexikSuccessHandler
{
    public function onAuthenticationSuccess(Request $request, TokenInterface $token): Response
    {
        // 1. Получаем стандартный JWT-ответ от Lexik-бандла
        $response = parent::onAuthenticationSuccess($request, $token);

        // 2. "Запоминаем" факт успешного логина для системы сессий Symfony.
        // Это создаст и установит сессионную cookie в браузере.
        $request->getSession()->set(SecurityRequestAttributes::LAST_USERNAME, $token->getUserIdentifier());
        $request->getSession()->set('_security_main', serialize($token)); // 'main' - имя нашего stateful-файрвола

        return $response;
    }
}
