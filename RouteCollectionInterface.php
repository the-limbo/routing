<?php

declare(strict_types=1);

namespace Limbo\Routing;

/**
 * Class RouteCollectionInterface
 * @package Limbo\Routing
 */
interface RouteCollectionInterface
{
    /**
     * Map route for method
     * @param string $method
     * @param string $path
     * @param callable|string $handler
     * @return Route
     */
    public function map(string $method, string $path, $handler): Route;

    /**
     * Add route for method DELETE
     * @param string $path
     * @param callable|string $handler
     * @return Route
     */
    public function delete(string $path, $handler): Route;

    /**
     * Add route for method GET
     * @param string $path
     * @param callable|string $handler
     * @return Route
     */
    public function get(string $path, $handler): Route;

    /**
     * Add route for method HEAD
     * @param string $path
     * @param callable|string $handler
     * @return Route
     */
    public function head(string $path, $handler): Route;

    /**
     * Add route for method OPTIONS
     * @param string $path
     * @param callable|string $handler
     * @return Route
     */
    public function options(string $path, $handler): Route;

    /**
     * Add route for method PATCH
     * @param string $path
     * @param callable|string $handler
     * @return Route
     */
    public function patch(string $path, $handler): Route;

    /**
     * Add route for method POST
     * @param string $path
     * @param callable|string $handler
     * @return Route
     */
    public function post(string $path, $handler): Route;

    /**
     * Add route for method PUT
     * @param string $path
     * @param callable|string $handler
     * @return Route
     */
    public function put(string $path, $handler): Route;
}
