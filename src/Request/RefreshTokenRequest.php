<?php

namespace App\Request;

use App\DTO\Auth\RefreshTokenDTO;
use App\Helpers\ValidationService;
use Symfony\Component\HttpFoundation\Request;

readonly class RefreshTokenRequest
{
    public function __construct(
        private ValidationService $validationService
    )
    {
    }

    public function validate(Request $request): RefreshTokenDTO
    {
        $data = json_decode($request->getContent(), true);
        $dto = new RefreshTokenDTO(
            refreshToken: $data['refresh_token'] ?? '',
        );
        $this->validationService->validate($dto);

        return $dto;
    }
}
