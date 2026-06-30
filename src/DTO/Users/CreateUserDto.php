<?php

namespace App\DTO\Users;

use Symfony\Component\Validator\Constraints as Assert;

readonly class CreateUserDto
{
    public function __construct(
        #[Assert\NotBlank(message: 'User email is required')]
        #[Assert\Email(message: 'User email is not valid')]
        public string $email,

        #[Assert\NotBlank(message: 'Password is required')]
        #[Assert\Length(min: 6, minMessage: 'Password must be at least 6 characters')]
        public string $password,
    )
    {
    }

}
