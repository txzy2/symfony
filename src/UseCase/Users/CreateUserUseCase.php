<?php

namespace App\UseCase\Users;

use App\DTO\Users\CreateUserDto;
use App\DTO\Users\Response\UserResponseDTO;
use App\Entity\User;
use App\Enum\ErrorsEnum;
use App\Repository\UsersRepository;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpKernel\Exception\HttpException;

readonly class CreateUserUseCase
{
    public function __construct(
        private LoggerInterface $logger,
        private UsersRepository $usersRepository,
    )
    {
    }

    /**
     * execute - Создание пользователя
     *
     * @param CreateUserDto $userData
     * @return UserResponseDTO
     *
     * @throws HttpException|\Exception
     */
    public function execute(CreateUserDto $userData): UserResponseDTO
    {
        if ($this->usersRepository->existByEmail($userData->email)) {
            throw new HttpException(
                ErrorsEnum::USER_ALREADY_EXISTS->getHttpCode(),
                ErrorsEnum::USER_ALREADY_EXISTS->getMessage($userData->email),
                headers: ['code' => ErrorsEnum::USER_ALREADY_EXISTS->value]
            );
        }

        try {
            $user = User::create($userData->email);
            $this->usersRepository->save($user);

            return new UserResponseDTO($user);
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
            throw new HttpException(ErrorsEnum::INTERNAL_ERROR->getHttpCode(), ErrorsEnum::INTERNAL_ERROR->getMessage());
        }
    }

}
