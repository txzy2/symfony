<?php

namespace App\Enum;

enum ErrorsEnum: string
{
    case SUCCESS = "200";
    case VALIDATION_ERROR = "422";
    case FORBIDDEN = "403";
    case NOT_FOUND = "404";
    case BAD_REQUEST = "400";
    case INTERNAL_ERROR = "500";
    case NOT_FOUND_ADDITIONAL_INFO = "404.4";

    case USER_ALREADY_EXISTS = "400.1";
    case USER_NOT_FOUND = "400.2";

    case USER_ALREADY_EDITED = "400.3";
    case TOKEN_EXPIRED = "403.1";
    case TOKEN_INVALID = "403.2";

    /**
     * getMessage - получение сообщения ошибки
     *
     * @param string|null $message
     * @return string
     */
    public function getMessage(?string $message = ""): string
    {
        return match ($this) {
            self::SUCCESS => 'OK',
            self::VALIDATION_ERROR => 'Не заполнены обязательные поля',
            self::FORBIDDEN => "Не введен обязательный заголовок {$message}",
            self::BAD_REQUEST => 'Неверный запрос. Проверьте отправленные данные',
            self::NOT_FOUND_ADDITIONAL_INFO => 'Ошибка получения шаблона. Не заполнено обязательное поле additionalFields',
            self::USER_ALREADY_EXISTS => "Пользователь {$message} уже существует",
            self::USER_NOT_FOUND => "Пользователь {$message} не найден",
            self::USER_ALREADY_EDITED => "Пользователь {$message} уже отредактирован",
            self::TOKEN_EXPIRED => "Токен не действителен",
            self::TOKEN_INVALID => "Токен не верен",
            self::NOT_FOUND => 'Ресурс не найден',
            default => 'Ошибка сервера, попробуйте позже',
        };
    }

    /**
     * getHttpCode - получение http кода
     *
     * @return int
     */
    public function getHttpCode(): int
    {
        return (int)$this->value;
    }
}
