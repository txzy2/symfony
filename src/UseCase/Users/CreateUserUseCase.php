<?php

namespace App\UseCase\Users;

use App\DTO\Users\CreateUserDto;
use App\DTO\Users\Response\UserResponseDTO;
use App\Entity\User;
use App\Enum\ErrorsEnum;
use App\Repository\UsersRepository;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

readonly class CreateUserUseCase
{
    public function __construct(
        private LoggerInterface $logger,
        private UsersRepository $usersRepository,
        private EntityManagerInterface $em,
        private UserPasswordHasherInterface $passwordHasher,
    ) {}

    public function execute(CreateUserDto $userData): UserResponseDTO
    {
        if ($this->usersRepository->existByEmail($userData->email)) {
            throw new HttpException(
                ErrorsEnum::USER_ALREADY_EXISTS->getHttpCode(),
                ErrorsEnum::USER_ALREADY_EXISTS->getMessage($userData->email),
                headers: ["code" => ErrorsEnum::USER_ALREADY_EXISTS->value],
            );
        }

        try {
            $user = User::create($userData->email, '');
            $user->setPassword($this->passwordHasher->hashPassword($user, $userData->password));
            $this->em->persist($user);
            $this->em->flush();

            return new UserResponseDTO($user);
        } catch (Exception $e) {
            $this->logger->error($e->getMessage());
            throw new HttpException(
                ErrorsEnum::INTERNAL_ERROR->getHttpCode(),
                ErrorsEnum::INTERNAL_ERROR->getMessage(),
                headers: ["code" => ErrorsEnum::INTERNAL_ERROR->value],
            );
        }
    }
}
