<?php

namespace App\EventListener;

use Monolog\Level;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Event\ResponseEvent;

class ReqResLogListener
{
    public function __construct(
        private readonly LoggerInterface $logger
    )
    {
    }

    /**
     * onKernelRequest - логирование входящего запроса
     *
     * @param RequestEvent $event
     *
     * @return void
     */
    public function onKernelRequest(RequestEvent $event): void
    {
        $request = $event->getRequest();

        $isJson = str_contains($request->headers->get('content-type', ''), 'application/json');
        $body = $isJson
            ? json_decode($request->getContent(), true) ?? []
            : $request->request->all();

        $this->logger->info(
            "INCOMING REQUEST {$request->getPathInfo()} (METHOD: {$request->getMethod()})",
            [
                "body" => $body,
                "query" => $request->query->all(),
                "headers" => [
                    "content-type" => $request->headers->get('content-type'),
                    "authorization" => $request->headers->has('authorization') ? '***' : null,
                    "user-agent" => $request->headers->get('user-agent'),
                ],
            ]
        );
    }

    /**
     * onKernelResponse - Логирование исходящего запроса
     *
     * @param ResponseEvent $event
     *
     * @return void
     */
    public function onKernelResponse(ResponseEvent $event): void
    {
        $response = $event->getResponse();
        $request = $event->getRequest();
        $duration = round(microtime(true) - $_SERVER['REQUEST_TIME_FLOAT'], 3);

        $level = $response->getStatusCode() >= 400 ? Level::Warning : Level::Info;
        $this->logger->log(
            $level,
            "RESPONSE {$request->getMethod()} {$request->getPathInfo()} ({$duration}s)})",
            [
                "status" => $response->getStatusCode(),
                "method" => $request->getMethod(),
                "path" => $request->getPathInfo(),
                "body" => json_decode($response->getContent(), true),
            ]
        );
    }
}
