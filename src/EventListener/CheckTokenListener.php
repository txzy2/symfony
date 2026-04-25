<?php

namespace App\EventListener;

use App\Enum\ErrorsEnum;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpFoundation\HeaderBag;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\Exception\HttpException;

readonly class HeadersData
{
    private function __construct(
        public int    $xTimeStamp,
        public string $xSignature,
    )
    {
    }

    /**
     * fromRequest - валидация и возврат обязательных полей для валидации токена
     *
     * @param HeaderBag $headers
     * @return self
     */
    public static function fromRequest(HeaderBag $headers): self
    {
        $timestamp = (int)$headers->get('x-timestamp');
        $signature = $headers->get('x-signature') ?? '';

        if ($timestamp <= 0) {
            throw new HttpException(ErrorsEnum::FORBIDDEN->value, ErrorsEnum::FORBIDDEN->getMessage("x-timestamp"));
        }

        if (empty($signature)) {
            throw new HttpException(ErrorsEnum::FORBIDDEN->value, ErrorsEnum::FORBIDDEN->getMessage("x-signature"));
        }

        return new self($timestamp, $signature);
    }
}


final class CheckTokenListener
{
    private const int TOKEN_TTL_SECONDS = 3600;

    public function __construct(
        private readonly LoggerInterface $logger,
        #[Autowire(param: 'app.token_secret')]
        private readonly string          $secret,
        #[Autowire(param: 'app.protected_routes')]
        private readonly array           $protectedRoutes,
    )
    {
    }

    /**
     * __invoke - Middleware для проверки токена
     *
     * @param ControllerEvent $event
     * @return void
     */
    #[AsEventListener]
    public function __invoke(ControllerEvent $event): void
    {
        if (!in_array($event->getRequest()->attributes->get('_route'), $this->protectedRoutes)) {
            return;
        }

        $request = $event->getRequest();
        $headersData = HeadersData::fromRequest($request->headers);

        if (abs(time() - $headersData->xTimeStamp) > self::TOKEN_TTL_SECONDS) {
            $this->logger->warning("VALIDATE TOKEN HEADERS EXPIRED");
            throw new HttpException(
                ErrorsEnum::TOKEN_EXPIRED->getHttpCode(),
                ErrorsEnum::TOKEN_EXPIRED->getMessage(),
                headers: ["code" => ErrorsEnum::TOKEN_EXPIRED->value]
            );
        }

        $expected = hash_hmac(
            'sha256',
            $request->getMethod() . $request->getPathInfo() . $headersData->xTimeStamp . $request->getContent(),
            $this->secret,
        );

        if (!hash_equals($expected, $headersData->xSignature)) {
            $this->logger->warning("HASH NOT VALID");
            throw new HttpException(
                ErrorsEnum::TOKEN_INVALID->getHttpCode(),
                ErrorsEnum::TOKEN_INVALID->getMessage(),
                headers: ["code" => ErrorsEnum::TOKEN_INVALID->value]
            );
        }

        $this->logger->info("VALIDATE TOKEN HEADERS SUCCESSFUL", ['userAgent' => $request->headers->get('user-agent')]);
    }
}
