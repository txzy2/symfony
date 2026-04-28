<?php

namespace App\Controller;

use App\DTO\Users\Response\UserResponseDTO;
use App\Entity\User;
use App\Enum\Activity;
use App\Repository\UsersRepository;
use App\Request\CreateUserRequest;
use App\Request\UpdateUserRequest;
use App\Traits\ApiResponseTrait;
use App\UseCase\Users\CreateUserUseCase;
use App\UseCase\Users\UpdateUserUseCase;
use Nelmio\ApiDocBundle\Attribute\Model;
use OpenApi\Attributes as OA;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
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
        private readonly CreateUserUseCase $createUserUseCase,
        private readonly UpdateUserUseCase $updateUserUseCase,
        private readonly UsersRepository   $usersRepository,
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
    public function createUser(Request $request, CreateUserRequest $createUserRequest): JsonResponse
    {
        $dto = $createUserRequest->validate($request);

        return $this->sendSuccess(
            'User created',
            $this->createUserUseCase->execute($dto)->toArray(),
            201
        );
    }

    #[Route("/{ext_id}", name: "users_get", methods: ["GET"])]
    #[OA\Tag(name: "Users")]
    #[OA\Parameter(
        name: "ext_id",
        description: "External user ID (UUID)",
        in: "path",
        required: true,
        schema: new OA\Schema(
            type: "string",
            example: "929efdea-4251-11f1-b33d-f1cddd972f59"
        )
    )]
    #[OA\Response(
        response: 201,
        description: "Get user info",
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: "id", type: "integer", example: 1),
                new OA\Property(property: "ext_id", type: "string", example: "929efdea-4251-11f1-b33d-f1cddd972f59"),
                new Oa\Property(property: "email", type: "string", example: "test@test.test"),
                new Oa\Property(property: "status", type: "string", example: Activity::ACTIVE->value),
                new OA\Property(
                    property: "created_at",
                    type: "string",
                    format: "date-time",
                    example: "2026-04-27T14:30:00+00:00"
                ),
                new OA\Property(
                    property: "updated_at",
                    type: "string",
                    format: "date-time",
                    example: "2026-04-27T14:30:00+00:00"
                ),
            ]
        )
    )]
    public function getByExtId(
        #[MapEntity(mapping: ['ext_id' => 'extId'], message: "User not found")]
        User $user,
    ): JsonResponse
    {
        return $this->sendSuccess(null, $user->toArray());
    }

    #[Route("/{ext_id}", name: "edit_user", methods: ["PATCH"])]
    #[OA\Tag(name: "Users")]
    #[OA\Parameter(
        name: "ext_id",
        description: "External user ID (UUID)",
        in: "path",
        required: true,
        schema: new OA\Schema(
            type: "string",
            example: "929efdea-4251-11f1-b33d-f1cddd972f59"
        )
    )]
    #[OA\RequestBody(
        description: "Fields for updating user (all fields are optional)",
        required: true,
        content: new OA\JsonContent(
            properties: [
                new OA\Property(
                    property: "email",
                    type: "string",
                    example: "new-email@test.com",
                    nullable: true
                ),
                new OA\Property(
                    property: "activity",
                    type: "string",
                    example: Activity::ACTIVE->value,
                    nullable: true
                ),
            ]
        )
    )]
    #[OA\Response(
        response: 200,
        description: "User successfully updated",
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: "status", type: "string", example: "SUCCESS"),
            ]
        )
    )]
    #[OA\Response(
        response: 404,
        description: "User not found",
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: "error", type: "string", example: "User not found")
            ]
        )
    )]
    public function editUser(
        #[MapEntity(mapping: ['ext_id' => 'extId'], message: "User not found")]
        User              $user,
        Request           $request,
        UpdateUserRequest $updateUserRequest
    ): JsonResponse
    {
        $dto = $updateUserRequest->validate($request);
        $this->updateUserUseCase->execute($dto, $user);
        return $this->sendSuccess("User successfully updated");
    }
}
