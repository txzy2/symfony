<?php

namespace App\Enum;

use Exception;

enum ErrorsEnum: string
{
    case SUCCESS = "200";
    case VALIDATION_ERROR = "422";
    case NOT_FOUND = "404";
    case BAD_REQUEST = "400";
    case INTERNAL_ERROR = "500";
    case NOT_FOUND_ADDITIONAL_INFO = "404.4";

    case USER_ALREADY_EXISTS = "400.1";

    public static function getMessageByCode(string $code): string
    {
        foreach (self::cases() as $case) {
            if ($case->value === $code) {
                try {
                    return $case->getMessage();
                } catch (Exception $e) {
                    continue;
                }
            }
        }

        return 'Неизвестная ошибка';
    }

    /**
     * @throws Exception
     */
    public function getMessage(?string $message = ""): string
    {
        return match ($this) {
            self::SUCCESS => 'OK',
            self::VALIDATION_ERROR => 'Не заполнены обязательные поля',
            self::BAD_REQUEST => 'Неверный запрос. Проверьте отправленные данные',
            self::INTERNAL_ERROR => 'Ошибка сервера, попробуйте позже',
            self::NOT_FOUND_ADDITIONAL_INFO => 'Ошибка получения шаблона. Не заполнено обязательное поле additionalFields',
            self::USER_ALREADY_EXISTS => "Пользователь {$message} уже существует",
            self::NOT_FOUND => 'Ресурс не найден'
        };
    }
}
