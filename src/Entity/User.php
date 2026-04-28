<?php

namespace App\Entity;

use App\DTO\Users\UpdateUserDTO;
use App\Enum\Activity;
use App\Repository\UsersRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;

#[ORM\HasLifecycleCallbacks]
#[ORM\Entity(repositoryClass: UsersRepository::class)]
#[ORM\Table(name: 'users')]
class User
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(name: 'ext_id', type: Types::GUID, unique: true)]
    #[ORM\GeneratedValue]
    private ?string $extId = null;

    #[ORM\Column(length: 255)]
    private ?string $email = null;

    #[ORM\Column(enumType: Activity::class)]
    private ?Activity $activity = null;

    #[ORM\Column(name: "created_at", type: Types::DATETIME_IMMUTABLE)]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(name: "updated_at", type: Types::DATETIME_IMMUTABLE)]
    private ?\DateTimeImmutable $updatedAt = null;

    public static function create(string $email): self
    {
        $user = new self();
        $user->setExtId(Uuid::v1());
        $user->setEmail($email);
        $user->setActivity(Activity::ACTIVE);
        return $user;
    }

    #[ORM\PrePersist]
    public function onPrePersist(): void
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();
    }

    #[ORM\PreUpdate]
    public function onPreUpdate(): void
    {
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    public function getActivity(): ?Activity
    {
        return $this->activity;
    }

    public function setActivity(Activity $activity): static
    {
        $this->activity = $activity;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTimeImmutable $updatedAt): static
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    public function getExtId(): ?string
    {
        return $this->extId;
    }

    public function setExtId(string $extId): static
    {
        $this->extId = $extId;

        return $this;
    }

    public function toArray(): array
    {
        return [
            "id" => $this->id,
            "ext_id" => $this->extId,
            "email" => $this->email,
            "created_at" => $this->createdAt->format("Y-m-d H:i:s"),
            "updated_at" => $this->updatedAt->format("Y-m-d H:i:s"),
        ];
    }

    public function applyUpdate(UpdateUserDTO $dto): void
    {
        if ($dto->email !== null) {
            $this->email = $dto->email;
        }

        if ($dto->activity !== null) {
            $this->activity = $dto->activity;
        }
    }
}
