<?php

namespace App\DTO\Users\Response;

use App\Entity\User;

class UserResponseDTO
{
    public readonly int $id;
    public readonly ?string $extId;
    public readonly string $email;
    public readonly string $activity;
    public readonly ?string $createdAt;
    public readonly ?string $updatedAt;

    public function __construct(User $user)
    {
        $this->id = $user->getId();
        $this->extId = $user->getExtId();
        $this->email = $user->getEmail();
        $this->activity = $user->getActivity()->value;
        $this->createdAt = $user->getCreatedAt()?->format('Y-m-d H:i:s');
        $this->updatedAt = $user->getUpdatedAt()?->format('Y-m-d H:i:s');
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'ext_id' => $this->extId,
            'email' => $this->email,
            'activity' => $this->activity,
            'created_at' => $this->createdAt,
            'updated_at' => $this->updatedAt,
        ];
    }
}
