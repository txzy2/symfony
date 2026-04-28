<?php

namespace App\Traits;

use Symfony\Component\HttpFoundation\JsonResponse;

trait ApiResponseTrait
{
    /**
     * Формирует и возвращает JSON-ответ с успешным сообщением.
     *
     * @param string|null $message
     * @param mixed $data
     * @param int $code
     * @return JsonResponse
     */
    protected function sendSuccess(?string $message = null, array $data = [], int $code = 200): JsonResponse
    {
        $response = [
            'success' => true,
            'message' => $message,
        ];

        if (!empty($data)) {
            $response['data'] = $data;
        }

        if (empty($message)) {
            unset($response['message']);
        }

        return new JsonResponse($response, $code);
    }
}
