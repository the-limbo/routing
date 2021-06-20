<?php

declare(strict_types=1);

namespace Limbo\Routing\Middleware;

use Psr\Http\Server\MiddlewareInterface;

/**
 * Class MiddlewareAwareInterface
 * @package Limbo\Routing\Middleware
 */
interface MiddlewareAwareInterface
{
    /**
     * Get all middleware
     * @return MiddlewareInterface[]
     */
    public function middlewares(): array;

    /**
     * Add middleware
     * @param MiddlewareInterface|string ...$middleware
     * @return static
     */
    public function middleware(...$middleware): self;

    /**
     * Add middleware to the prepend of the stack
     * @param MiddlewareInterface|string $middleware
     * @return static
     */
    public function prependMiddleware($middleware): self;

    /**
     * Get first middleware in stack
     * @return MiddlewareInterface
     */
    public function shiftMiddleware(): MiddlewareInterface;
}
