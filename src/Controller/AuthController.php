<?php

namespace App\Controller;

use App\DTO\Auth\TokenResponseDTO;
use App\Enum\ErrorsEnum;
use App\Request\LoginRequest;
use App\Request\RefreshTokenRequest;
use App\Service\AuthService;
use App\Traits\ApiResponseTrait;
use Nelmio\ApiDocBundle\Attribute\Model;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Routing\Attribute\Route;

#[Route("/api/v1/auth")]
final class AuthController extends AbstractController
{
    use ApiResponseTrait;

    public function __construct(
        private readonly AuthService $authService,
    ) {}

    #[Route("/login", name: "auth_login", methods: ["POST"])]
    #[OA\Tag(name: "Auth")]
    #[OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: "email", type: "string", example: "user@example.com"),
                new OA\Property(property: "password", type: "string", example: "password123"),
            ],
        ),
    )]
    #[OA\Response(
        response: 200,
        description: "Login success",
        content: new Model(type: TokenResponseDTO::class),
    )]
    #[OA\Response(response: 401, description: "Invalid credentials")]
    public function login(
        Request $request,
        LoginRequest $loginRequest,
    ): JsonResponse {
        $dto = $loginRequest->validate($request);
        $tokens = $this->authService->login($dto->email, $dto->password);

        return $this->sendSuccess('Login successful', $tokens->toArray());
    }

    #[Route("/refresh", name: "auth_refresh", methods: ["POST"])]
    #[OA\Tag(name: "Auth")]
    #[OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: "refresh_token", type: "string", example: "..."),
            ],
        ),
    )]
    #[OA\Response(
        response: 200,
        description: "Token refreshed",
        content: new Model(type: TokenResponseDTO::class),
    )]
    #[OA\Response(response: 401, description: "Invalid refresh token")]
    public function refresh(
        Request $request,
        RefreshTokenRequest $refreshTokenRequest,
    ): JsonResponse {
        $dto = $refreshTokenRequest->validate($request);
        $tokens = $this->authService->refreshAccessToken($dto->refreshToken);

        return $this->sendSuccess('Token refreshed', $tokens->toArray());
    }

    #[Route("/me", name: "auth_me", methods: ["GET"])]
    #[OA\Tag(name: "Auth")]
    #[OA\Response(response: 200, description: "Current user info")]
    #[OA\Response(response: 401, description: "Unauthorized")]
    public function me(Request $request): JsonResponse
    {
        $user = $this->authService->getUserFromRequest($request);

        if (!$user) {
            throw new HttpException(
                ErrorsEnum::UNAUTHORIZED->getHttpCode(),
                ErrorsEnum::UNAUTHORIZED->getMessage(),
                headers: ["code" => ErrorsEnum::UNAUTHORIZED->value],
            );
        }

        return $this->sendSuccess(null, $user->toArray());
    }

    #[Route("/logout", name: "auth_logout", methods: ["POST"])]
    #[OA\Tag(name: "Auth")]
    #[OA\Response(response: 200, description: "Logged out")]
    public function logout(Request $request): JsonResponse
    {
        $this->authService->logout($request);
        return $this->sendSuccess('Logged out successfully');
    }
}