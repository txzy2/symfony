<?php

namespace App\Controller;

use App\Request\CreateUserRequest;
use App\Traits\ApiResponseTrait;
use App\UseCase\Users\CreateUserUseCase;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/v1/users')]
final class UsersController extends AbstractController
{
    use ApiResponseTrait;

    public function __construct(
        private readonly CreateUserUseCase $createUserUseCase
    )
    {
    }

    #[Route("/register", name: "register", methods: ["POST"])]
    public function createUser(Request $request, CreateUserRequest $createWalletRequest): JsonResponse
    {
        $dto = $createWalletRequest->validate($request);
        return $this->sendSuccess(
            'User created',
            $this->createUserUseCase->execute($dto)->toArray(),
            201
        );
    }
}
