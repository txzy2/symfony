<?php

namespace App\Request;

use App\DTO\Users\CreateUserDto;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class CreateUserRequest
{
    public function __construct(
        private readonly ValidatorInterface $validator
    )
    {
    }

    public function validate(Request $request): CreateUserDto
    {
        $data = json_decode($request->getContent(), true);

        $dto = new CreateUserDto(email: $data["email"]);
        $violations = $this->validator->validate($dto);

        if (count($violations) > 0) {
            $errors = [];
            foreach ($violations as $violation) {
                $errors[$violation->getPropertyPath()] = $violation->getMessage();
            }
            throw new UnprocessableEntityHttpException(json_encode($errors));
        }

        return $dto;
    }

}
