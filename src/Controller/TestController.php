<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/v1')]
class TestController extends AbstractController
{
    /**
     * test - Тестовый контроллер для проверки работоспособности сервиса
     *
     * @param ?string $name
     *
     * @return Response
     */
    #[Route('/test', name: 'test', methods: ['GET'])]
    public function test(#[MapQueryParameter] ?string $name = null): Response
    {
        if (!$name) {
            return $this->json(['status' => 'fail', 'message' => 'name is required'], 400);
        }

        return $this->json(["status" => "success", 'message' => "Hello {$name}"]);
    }

    /**
     * testHtml - Тестовый метод для отображения HTML в браузере
     *
     * @return Response
     */
    #[Route('/test/html', name: 'testHtml', methods: ['GET'])]
    public function testHtml(): Response
    {
        return new Response("<html lang='ru'><body>Привет</body></html>");
    }
}
