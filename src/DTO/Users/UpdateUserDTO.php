<?php

namespace App\DTO\Users;

use App\Enum\Activity;
use Symfony\Component\Validator\Constraints as Assert;

readonly class UpdateUserDTO
{

    public function __construct(
        #[Assert\Email(message: 'User email is not valid')]
        public ?string   $email = null,

        #[Assert\Type(Activity::class)]
        public ?Activity $activity = null,
    )
    {
    }

}
