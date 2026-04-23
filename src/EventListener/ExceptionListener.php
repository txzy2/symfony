<?php

namespace App\EventListener;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

class ExceptionListener
{
    public function onKernelException(ExceptionEvent $event): void
    {
        $exception = $event->getThrowable();
//        dd($exception);

        // Проверяем отправили ли мы в хедарах ошибки дополнительный код
        $headers = $exception instanceof HttpExceptionInterface
            ? $exception->getHeaders()
            : null;

        // Проверяем statusCode если его нет то 500
        $statusCode = $exception instanceof HttpExceptionInterface
            ? $exception->getStatusCode()
            : 500;

        $message = $exception->getMessage();
        $decoded = json_decode($message, true);

        $event->setResponse(new JsonResponse([
            'error' => $decoded ?? $message,
            'code' => $headers['code'] ?? $statusCode
        ], $statusCode));
    }
}
