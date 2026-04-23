<?php

namespace App\Helpers;

use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Validator\Validator\ValidatorInterface;

readonly class ValidationService
{
    public function __construct(
        private ValidatorInterface $validator
    )
    {
    }

    public function validate(object $dto): void
    {
        $violations = $this->validator->validate($dto);

        if (count($violations) > 0) {
            $errors = [];
            foreach ($violations as $violation) {
                $errors[$violation->getPropertyPath()] = $violation->getMessage();
            }
            throw new UnprocessableEntityHttpException(json_encode($errors));
        }
    }
}
