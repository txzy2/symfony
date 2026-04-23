<?php

namespace App\Traits;

use App\Enum\ErrorsEnum;
use Symfony\Component\HttpFoundation\JsonResponse;

trait ApiResponseTrait
{
    /**
     * Формирует и возвращает JSON-ответ с ошибкой.
     *
     * @param string|null $message
     * @param string $code
     * @return JsonResponse
     */
    protected function sendError(?string $message = null, string $code = ErrorsEnum::BAD_REQUEST->value): JsonResponse
    {
        return new JsonResponse([
            'success' => false,
            'message' => $message ?? ErrorsEnum::getMessageByCode($code),
        ], $code);
    }

    /**
     * Формирует и возвращает JSON-ответ с успешным сообщением.
     *
     * @param string $message
     * @param mixed $data
     * @param int $code
     * @return JsonResponse
     */
    protected function sendSuccess(string $message, array $data = [], int $code = 200): JsonResponse
    {
        $response = [
            'success' => true,
            'message' => $message,
        ];

        if (!empty($data)) {
            $response['data'] = $data;
        }

        return new JsonResponse($response, $code);
    }
}
