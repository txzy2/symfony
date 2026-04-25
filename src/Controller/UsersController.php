<?php

namespace App\Controller;

use App\DTO\Users\Response\UserResponseDTO;
use App\Request\CreateUserRequest;
use App\Traits\ApiResponseTrait;
use App\UseCase\Users\CreateUserUseCase;
use Nelmio\ApiDocBundle\Attribute\Model;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;
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

    /**
     * @throws HttpException|\Exception
     */
    #[Route("/register", name: "users_register", methods: ["POST"])]
    #[OA\Tag(name: "Users")]
    #[OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: "email", type: "string", example: "user@example.com"),
            ]
        )
    )]
    #[OA\Response(
        response: 201,
        description: "Register success",
        content: new Model(type: UserResponseDTO::class)
    )]
    #[OA\Response(
        response: 422,
        description: "Validation error",
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: "error", type: "object"),
                new OA\Property(property: "code", type: "integer", example: 422),
            ]
        )
    )]
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
