<?php

namespace App\UseCase\Users;

use App\DTO\Users\UpdateUserDTO;
use App\Entity\User;
use App\Repository\UsersRepository;
use Psr\Log\LoggerInterface;

readonly class UpdateUserUseCase
{
    public function __construct(
        private UsersRepository $userRepository,
        private LoggerInterface $logger
    )
    {
    }

    public function execute(UpdateUserDTO $updateUser, User $existUser): void
    {
        try {
            $existUser->applyUpdate($updateUser);
            $this->userRepository->save($existUser);
        } catch (\Throwable $exception) {
            $this->logger->error($exception->getMessage());
        }
    }
}
