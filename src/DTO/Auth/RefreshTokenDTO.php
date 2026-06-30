<?php

namespace App\DTO\Auth;

use Symfony\Component\Validator\Constraints as Assert;

readonly class RefreshTokenDTO
{
    public function __construct(
        #[Assert\NotBlank(message: 'Refresh token is required')]
        public string $refreshToken,
    )
    {
    }
}
