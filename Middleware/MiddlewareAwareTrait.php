<?php

declare(strict_types=1);

namespace Limbo\Routing\Middleware;

use OutOfBoundsException;
use Psr\Http\Server\MiddlewareInterface;
use Limbo\Container\ContainerAwareInterface;
use Limbo\Routing\Exception\MiddlewareNotResolveException;

/**
 * Trait MiddlewareAwareTrait
 * @package Limbo\Routing\Middleware
 */
trait MiddlewareAwareTrait
{
    /**
     * List of middleware
     * @var MiddlewareInterface[]
     */
    protected array $middlewares = [];

    /**
     * @inheritDoc
     */
    public function middlewares(): array
    {
        return $this->middlewares;
    }

    /**
     * @inheritDoc
     */
    public function middleware(...$middleware): self
    {
        foreach ($middleware as $value) {
            array_push($this->middlewares, $this->resolveMiddleware($value));
        }

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function prependMiddleware($middleware): self
    {
        array_unshift($this->middlewares, $this->resolveMiddleware($middleware));

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function shiftMiddleware(): MiddlewareInterface
    {
        $middleware = array_shift($this->middlewares);
        if ($middleware === null) {
            throw new OutOfBoundsException('Reached end of middleware stack. Does your handler return a response?');
        }

        return $middleware;
    }

    /**
     * Resolve middleware class
     * @param string|MiddlewareInterface $middleware
     * @return MiddlewareInterface
     */
    protected function resolveMiddleware($middleware): MiddlewareInterface
    {
        if ($middleware instanceof MiddlewareInterface) {
            return $middleware;
        }

        if (is_string($middleware) && class_exists($middleware)) {
            if ($this instanceof ContainerAwareInterface) {
                return $this->getContainer()->get($middleware);
            }

            return new $middleware();
        }

        throw new MiddlewareNotResolveException(
            sprintf('Could not resolve middleware class: "%s"', $middleware)
        );
    }
}
