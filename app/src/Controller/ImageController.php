<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/images', name: 'api_images_')]
final class ImageController extends AbstractController
{
    #[Route('/upload', name: 'upload', methods: ['POST'])]
    public function upload(Request $request): JsonResponse
    {
        $file = $request->files->get('image');
        if (!$file) {
            return $this->json(['error' => 'No image uploaded'], 400);
        }

        // Проверка типа файла
        if (!in_array($file->getMimeType(), ['image/jpeg', 'image/png'])) {
            return $this->json(['error' => 'Invalid file type'], 400);
        }

        // Сохраняем файл (пример для публичной директории)
        $filename = uniqid() . '.' . $file->guessExtension();
        $file->move($this->getParameter('kernel.project_dir') . '/public/uploads', $filename);

        // Возвращаем URL изображения
        return $this->json([
            'url' => '/uploads/' . $filename,
        ]);
    }
    // #[Route('/image', name: 'app_image')]
    // public function index(): Response
    // {
    //     return $this->render('image/index.html.twig', [
    //         'controller_name' => 'ImageController',
    //     ]);
    // }
}
