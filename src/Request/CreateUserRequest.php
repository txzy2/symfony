<?php

namespace App\Request;

use App\DTO\Users\CreateUserDto;
use App\Helpers\ValidationService;
use Symfony\Component\HttpFoundation\Request;

readonly class CreateUserRequest
{
    public function __construct(
        private ValidationService $validationService
    )
    {
    }

    public function validate(Request $request): CreateUserDto
    {
        $data = json_decode($request->getContent(), true);
        $dto = new CreateUserDto(email: $data['email'] ?? '');
        $this->validationService->validate($dto);

        return $dto;
    }

}
