<?php

namespace App\DTO\Auth;

use Symfony\Component\Validator\Constraints as Assert;

readonly class LoginDTO
{
    public function __construct(
        #[Assert\NotBlank(message: 'Email is required')]
        #[Assert\Email(message: 'Email is not valid')]
        public string $email,

        #[Assert\NotBlank(message: 'Password is required')]
        public string $password,
    )
    {
    }
}
