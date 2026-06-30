<?php

namespace App\Request;

use App\DTO\Auth\LoginDTO;
use App\Helpers\ValidationService;
use Symfony\Component\HttpFoundation\Request;

readonly class LoginRequest
{
    public function __construct(
        private ValidationService $validationService
    )
    {
    }

    public function validate(Request $request): LoginDTO
    {
        $data = json_decode($request->getContent(), true);
        $dto = new LoginDTO(
            email: $data['email'] ?? '',
            password: $data['password'] ?? '',
        );
        $this->validationService->validate($dto);

        return $dto;
    }
}
