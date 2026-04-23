<?php

namespace App\DTO\Users;

use Symfony\Component\Validator\Constraints as Assert;

class CreateUserDto
{
    public function __construct(
        #[Assert\NotBlank(message: 'User email is required')]
        #[Assert\Email(message: 'User email is not valid')]
        public readonly string $email,
    )
    {
    }

}
