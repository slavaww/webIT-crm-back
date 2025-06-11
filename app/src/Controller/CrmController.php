<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CrmController extends AbstractController
{
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    #[Route('/crm/{reactRoute}', name: 'app_crm', requirements: ['reactRoute' => '.+'], defaults: ['reactRoute' => null])]
    public function index(): Response
    {
        // Рендерим шаблон-оболочку для нашего SPA
        return $this->render('crm/index.html.twig');
    }
}