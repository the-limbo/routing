<?php

declare(strict_types=1);

namespace Limbo\Routing\Middleware;

use Throwable;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Class ThrowableMiddleware
 * @package Limbo\Routing\Middleware
 */
class ThrowableMiddleware implements MiddlewareInterface
{
    /**
     * @var Throwable
     */
    protected Throwable $error;

    /**
     * Set throw to middleware
     * @param Throwable $error
     * @return MiddlewareInterface
     */
    public function setThrowError(Throwable $error): MiddlewareInterface
    {
        $this->error = $error;

        return $this;
    }

    /**
     * @inheritDoc
     * @throws Throwable
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        throw $this->error;
    }

    /**
     * Invoke throw to middleware
     * @param Throwable $error
     * @return MiddlewareInterface
     */
    public function __invoke(Throwable $error): MiddlewareInterface
    {
        $this->error = $error;

        return $this;
    }
}
