<?php

namespace App\Service;

use App\DTO\Auth\TokenResponseDTO;
use App\Entity\User;
use App\Enum\ErrorsEnum;
use App\Repository\UsersRepository;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

readonly class AuthService
{
    private const int ACCESS_TOKEN_TTL = 3600;
    private const int REFRESH_TOKEN_TTL = 2592000;
    private const string REFRESH_CACHE_PREFIX = 'refresh_token_';

    public function __construct(
        #[Autowire(param: 'app.jwt_secret')] private string $jwtSecret,
        #[Autowire(param: 'app.jwt_algorithm')] private string $jwtAlgorithm,
        private UserPasswordHasherInterface $passwordHasher,
        #[Autowire(service: 'cache.refresh_tokens')] private CacheInterface $cache,
        private UsersRepository $usersRepository,
    ) {}

    public function login(string $email, string $password): TokenResponseDTO
    {
        $user = $this->usersRepository->findOneBy(['email' => $email]);

        if (!$user || !$this->passwordHasher->isPasswordValid($user, $password)) {
            throw new HttpException(
                ErrorsEnum::INVALID_CREDENTIALS->getHttpCode(),
                ErrorsEnum::INVALID_CREDENTIALS->getMessage(),
                headers: ["code" => ErrorsEnum::INVALID_CREDENTIALS->value],
            );
        }

        return $this->createTokens($user);
    }

    public function createTokens(User $user): TokenResponseDTO
    {
        $now = time();
        $accessPayload = [
            'sub' => $user->getExtId(),
            'email' => $user->getEmail(),
            'iat' => $now,
            'exp' => $now + self::ACCESS_TOKEN_TTL,
        ];

        $accessToken = JWT::encode($accessPayload, $this->jwtSecret, $this->jwtAlgorithm);

        $refreshPayload = [
            'sub' => $user->getExtId(),
            'iat' => $now,
            'exp' => $now + self::REFRESH_TOKEN_TTL,
        ];
        $refreshToken = JWT::encode($refreshPayload, $this->jwtSecret, $this->jwtAlgorithm);

        $this->cache->delete(self::REFRESH_CACHE_PREFIX . $user->getExtId());
        $this->cache->get(
            self::REFRESH_CACHE_PREFIX . $user->getExtId(),
            fn(ItemInterface $item) => $item->expiresAfter(self::REFRESH_TOKEN_TTL)->set($refreshToken),
        );

        return new TokenResponseDTO($accessToken, $refreshToken, self::ACCESS_TOKEN_TTL);
    }

    public function decodeToken(string $token): object
    {
        try {
            return JWT::decode($token, new Key($this->jwtSecret, $this->jwtAlgorithm));
        } catch (\Exception $e) {
            throw new HttpException(
                ErrorsEnum::TOKEN_INVALID_OR_EXPIRED->getHttpCode(),
                ErrorsEnum::TOKEN_INVALID_OR_EXPIRED->getMessage(),
                headers: ["code" => ErrorsEnum::TOKEN_INVALID_OR_EXPIRED->value],
            );
        }
    }

    public function refreshAccessToken(string $refreshToken): TokenResponseDTO
    {
        $decoded = $this->decodeToken($refreshToken);
        $userExtId = $decoded->sub;

        $storedToken = $this->cache->get(self::REFRESH_CACHE_PREFIX . $userExtId, fn(ItemInterface $item) => null);

        if ($storedToken !== $refreshToken) {
            throw new HttpException(
                ErrorsEnum::INVALID_REFRESH_TOKEN->getHttpCode(),
                ErrorsEnum::INVALID_REFRESH_TOKEN->getMessage(),
                headers: ["code" => ErrorsEnum::INVALID_REFRESH_TOKEN->value],
            );
        }

        $user = $this->usersRepository->findOneBy(['extId' => $userExtId]);
        if (!$user) {
            throw new HttpException(
                ErrorsEnum::USER_NOT_FOUND_AUTH->getHttpCode(),
                ErrorsEnum::USER_NOT_FOUND_AUTH->getMessage(),
                headers: ["code" => ErrorsEnum::USER_NOT_FOUND_AUTH->value],
            );
        }

        return $this->createTokens($user);
    }

    public function getUserFromRequest(Request $request): ?User
    {
        $authHeader = $request->headers->get('Authorization', '');

        if (!str_starts_with($authHeader, 'Bearer ')) {
            return null;
        }

        $token = substr($authHeader, 7);

        try {
            $decoded = JWT::decode($token, new Key($this->jwtSecret, $this->jwtAlgorithm));
        } catch (\Exception) {
            return null;
        }

        return $this->usersRepository->findOneBy(['extId' => $decoded->sub]);
    }

    public function logout(Request $request): void
    {
        $authHeader = $request->headers->get('Authorization', '');

        if (!str_starts_with($authHeader, 'Bearer ')) {
            return;
        }

        $token = substr($authHeader, 7);

        try {
            $decoded = JWT::decode($token, new Key($this->jwtSecret, $this->jwtAlgorithm));
            $this->cache->delete(self::REFRESH_CACHE_PREFIX . $decoded->sub);
        } catch (\Exception) {
        }
    }

    public function getBearerToken(Request $request): ?string
    {
        $authHeader = $request->headers->get('Authorization', '');

        if (!str_starts_with($authHeader, 'Bearer ')) {
            return null;
        }

        return substr($authHeader, 7);
    }
}