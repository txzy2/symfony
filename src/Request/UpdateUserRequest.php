<?php

namespace App\Request;

use App\DTO\Users\UpdateUserDTO;
use App\Helpers\ValidationService;
use Symfony\Component\HttpFoundation\Request;

class UpdateUserRequest
{
    public function __construct(
        private readonly ValidationService $validationService
    )
    {
    }

    public function validate(Request $request): UpdateUserDTO
    {
        $data = json_decode($request->getContent(), true);
        $dto = new UpdateUserDTO(
            email: $data['email'] ?? null,
            activity: $data['activity'] ?? null,
        );
        $this->validationService->validate($dto);
        return $dto;
    }
}
