<?php

declare(strict_types=1);

namespace Limbo\Routing;

/**
 * Trait RouteCollectionTrait
 * @package Limbo\Routing
 */
trait RouteCollectionTrait
{
    /**
     * @inheritDoc
     */
    abstract public function map(string $method, string $path, $handler): Route;

    /**
     * @inheritDoc
     */
    public function delete(string $path, $handler): Route
    {
        return $this->map('DELETE', $path, $handler);
    }

    /**
     * @inheritDoc
     */
    public function get(string $path, $handler): Route
    {
        return $this->map('GET', $path, $handler);
    }

    /**
     * @inheritDoc
     */
    public function head(string $path, $handler): Route
    {
        return $this->map('HEAD', $path, $handler);
    }

    /**
     * @inheritDoc
     */
    public function options(string $path, $handler): Route
    {
        return $this->map('OPTIONS', $path, $handler);
    }

    /**
     * @inheritDoc
     */
    public function patch(string $path, $handler): Route
    {
        return $this->map('PATCH', $path, $handler);
    }

    /**
     * @inheritDoc
     */
    public function post(string $path, $handler): Route
    {
        return $this->map('POST', $path, $handler);
    }

    /**
     * @inheritDoc
     */
    public function put(string $path, $handler): Route
    {
        return $this->map('PUT', $path, $handler);
    }
}
