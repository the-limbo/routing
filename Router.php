<?php

declare(strict_types=1);

namespace Limbo\Routing;

use FastRoute\RouteCollector;
use FastRoute\RouteParser\Std;
use Limbo\Container\Container;
use Psr\Http\Message\ResponseInterface;
use FastRoute\DataGenerator\GroupCountBased;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Message\ServerRequestInterface;
use Limbo\Routing\Middleware\MiddlewareAwareTrait;
use Limbo\Routing\Exception\InvalidRouteNameException;
use Limbo\Routing\Middleware\MiddlewareAwareInterface;

/**
 * Class Router
 * @package Limbo\Routing
 */
class Router implements
    RouteCollectionInterface,
    RequestHandlerInterface,
    MiddlewareAwareInterface
{
    use RouteCollectionTrait;
    use MiddlewareAwareTrait;

    /**
     * The route collector instance
     * @var RouteCollector
     */
    protected RouteCollector $collector;

    /**
     * DI container instance
     * @var Container
     */
    protected Container $container;

    /**
     * The route array
     * @var Route[]
     */
    protected array $routes = [];

    /**
     * The route groups array
     * @var RouteGroup[]
     */
    protected array $routeGroups = [];

    /**
     * The named route array
     * @var Route[]
     */
    protected array $namedRoutes = [];

    /**
     * Is routes map built
     * @var bool
     */
    protected bool $routesBuilt = false;

    /**
     * Route regex patterns
     * @var array
     */
    protected array $patterns = [
        '/{(.+?):num}/' => '{$1:[0-9]+}',
        '/{(.+?):word}/' => '{$1:[a-zA-Z]+}',
        '/{(.+?):any}/' => '{$1:[a-zA-Z0-9-_]+}',
        '/{(.+?):slug}/' => '{$1:[a-z0-9-]+}',
        '/{(.+?):uuid}/' => '{$1:[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}+}',
    ];

    /**
     * Router constructor.
     * @param Container|null $container
     */
    public function __construct(?Container $container = null)
    {
        $this->container = $container ?? new Container();
        $this->collector = new RouteCollector(new Std(), new GroupCountBased());
    }

    /**
     * @inheritDoc
     */
    public function map(string $method, string $path, $handler): Route
    {
        $path = sprintf('/%s', ltrim($path, '/'));
        $route = (new Route($method, $path, $handler))
            ->setContainer($this->container);
        $this->routes[] = $route;

        return $route;
    }

    /**
     * Create group for routes
     * @param string $prefix
     * @param callable $callback
     * @return RouteGroup
     */
    public function group(string $prefix, callable $callback): RouteGroup
    {
        $group = new RouteGroup($prefix, $callback, $this);
        $this->routeGroups[] = $group;
        $group();
        array_pop($this->routeGroups);

        return $group;
    }

    /**
     * @inheritDoc
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        if ($this->routesBuilt === false) {
            $this->buildRoutesMap();
        }
        $dispatcher = (new Dispatcher($this->collector))
            ->setContainer($this->container)
            ->middleware(...$this->middlewares());

        return $dispatcher->handle($request);
    }

    /**
     * Build routes map
     * @return void
     */
    protected function buildRoutesMap(): void
    {
        $this->buildNamedRoutesMap();
        /* @var Route[] $routes */
        $routes = array_merge(array_values($this->routes), array_values($this->namedRoutes));

        foreach ($routes as $route) {
            $this->collector->addRoute(
                $route->getMethod(),
                $this->compileRoutePath($route->getPath()),
                $route
            );
        }

        $this->routesBuilt = true;
    }

    /**
     * Build named routes map
     * @return void
     */
    protected function buildNamedRoutesMap(): void
    {
        foreach ($this->routes as $id => $route) {
            if ($route->getName() !== null) {
                unset($this->routes[$id]);
                $this->namedRoutes[$route->getName()] = $route;
            }
        }
    }

    /**
     * Get map for all routes
     * @return array
     */
    public function getRoutes(): array
    {
        if ($this->routesBuilt === false) {
            $this->buildRoutesMap();
        }

        return array_merge($this->routes, $this->namedRoutes);
    }

    /**
     * Get route by name
     * @param string $name
     * @return Route
     * @throws InvalidRouteNameException
     */
    public function route(string $name): Route
    {
        if ($this->has($name)) {
            return $this->namedRoutes[$name];
        }

        throw new InvalidRouteNameException(sprintf('Route with name "%s" not found.', $name));
    }

    /**
     * If route with name exists - returning true, if not false
     * @param string $name
     * @return bool
     */
    public function has(string $name): bool
    {
        $this->buildNamedRoutesMap();

        return isset($this->namedRoutes[$name]);
    }

    /**
     * Generate url string for route with name and with route params
     * @param string $name
     * @param array $params
     * @return string
     */
    public function url(string $name, array $params = []): string
    {
        $route = $this->route($name);
        $path = $route->getPath($params);
        if (($offset = strpos($path, '[')) === false) {
            return $path;
        }

        return substr($path, 0, $offset);
    }

    /**
     * Replacing patterns values to regex values in route path
     * @param string $path
     * @return string
     */
    protected function compileRoutePath(string $path): string
    {
        return preg_replace(
            array_keys($this->patterns),
            array_values($this->patterns),
            $path
        );
    }
}
