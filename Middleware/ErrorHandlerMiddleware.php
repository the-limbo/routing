<?php

declare(strict_types=1);

namespace Limbo\Routing\Middleware;

use Throwable;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Class ErrorHandlerMiddleware
 * @package Limbo\Routing\Middleware
 */
class ErrorHandlerMiddleware implements ErrorHandlerInterface
{
    /**
     * @inheritDoc
     * @throws Throwable
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        try {
            return $handler->handle($request);
        } catch (Throwable $throwable) {
            throw $throwable;
        }
    }
}
