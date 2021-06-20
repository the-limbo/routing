<?php

declare(strict_types=1);

namespace Limbo\Routing;

use Limbo\Routing\Middleware\MiddlewareAwareTrait;
use Limbo\Routing\Middleware\MiddlewareAwareInterface;

/**
 * Group for routes
 * Class RouteGroup
 * @package Limbo\Routing
 */
class RouteGroup implements
    RouteCollectionInterface,
    MiddlewareAwareInterface
{
    use RouteCollectionTrait;
    use MiddlewareAwareTrait;

    /**
     * Prefix for group
     * @var string
     */
    protected string $prefix;

    /**
     * Group callback
     * @var callable
     */
    protected $callback;

    /**
     * Route collection for mapping group
     * @var RouteCollectionInterface
     */
    protected RouteCollectionInterface $collection;

    /**
     * RouteGroup constructor.
     * @param string $prefix
     * @param callable $callback
     * @param RouteCollectionInterface $collection
     */
    public function __construct(string $prefix, callable $callback, RouteCollectionInterface $collection)
    {
        $this->prefix = sprintf('/%s', ltrim($prefix, '/'));
        $this->callback = $callback;
        $this->collection = $collection;
    }

    /**
     * Invoke this group to collect routes from group
     * @return RouteGroup
     */
    public function __invoke(): RouteGroup
    {
        ($this->callback)($this);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function map(string $method, string $path, $handler): Route
    {
        $path = ($path === '/')
            ? $this->prefix
            : $this->prefix . sprintf('/%s', ltrim($path, '/'));

        return $this->collection
            ->map($method, $path, $handler)
            ->setGroup($this);
    }

    /**
     * Get group prefix
     * @return string
     */
    public function getPrefix(): string
    {
        return $this->prefix;
    }
}
