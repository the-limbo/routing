<?php

declare(strict_types=1);

namespace Limbo\Routing;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\MiddlewareInterface;
use Limbo\Container\ContainerAwareTrait;
use Limbo\Container\Reflection\Reflector;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Limbo\Container\ContainerAwareInterface;
use Limbo\Routing\Middleware\MiddlewareAwareTrait;
use Limbo\Routing\Middleware\MiddlewareAwareInterface;
use Limbo\Routing\Exception\InvalidRouteHandlerException;

/**
 * Class Route
 * @package Limbo\Routing
 */
class Route implements
    MiddlewareInterface,
    MiddlewareAwareInterface,
    ContainerAwareInterface
{
    use MiddlewareAwareTrait;
    use ContainerAwareTrait;

    /**
     * Controllers namespaces
     * @var array
     */
    protected array $namespaces = [];

    /**
     * Route method
     * @var string
     */
    protected string $method;

    /**
     * Route path
     * @var string
     */
    protected string $path;

    /**
     * Route handler
     * @var string|callable
     */
    protected $handler;

    /**
     * Route vars
     * @var array
     */
    protected array $params = [];

    /**
     * Route name
     * @var string|null
     */
    protected ?string $name = null;

    /**
     * Route group
     * @var RouteGroup|null
     */
    protected ?RouteGroup $group = null;

    /**
     * Route constructor.
     * @param string $method
     * @param string $path
     * @param callable|string $handler
     */
    public function __construct(string $method, string $path, $handler)
    {
        $this->method = strtoupper($method);
        $this->path = $path;
        $this->handler = $handler;
    }

    /**
     * Get method for route
     * @return string
     */
    public function getMethod(): string
    {
        return $this->method;
    }

    /**
     * Get compiled path for route
     * @param array $params
     * @return string
     */
    public function getPath(array $params = []): string
    {
        $toReplace = [];

        foreach ($params as $wildcard => $actual) {
            $toReplace['/{' . preg_quote($wildcard, '/') . '(:.*?)?}/'] = $actual;
        }

        return preg_replace(array_keys($toReplace), array_values($toReplace), $this->path);
    }

    /**
     * Get callback handler for route
     * @return callable
     */
    public function getHandler(): callable
    {
        $callable = $this->handler;
        if (is_string($callable) && strpos($callable, '@') !== false) {
            $callable = explode('@', $callable, 2);
        }

        if (is_array($callable) && isset($callable[0]) && is_object($callable[0])) {
            $callable = [$callable[0], $callable[1]];
        }

        if (is_array($callable) && isset($callable[0]) && is_string($callable[0])) {
            $callable = [$this->getContainer()->get($callable[0]), $callable[1]];
        }

        if (is_string($callable)) {
            $callable = $this->getContainer()->get($callable);
        }

        if (!is_callable($callable)) {
            throw new InvalidRouteHandlerException(
                'Could not resolve a callable handler for this route.'
            );
        }

        return $callable;
    }

    /**
     * Get parsed params for route
     * @return array
     */
    public function getParams(): array
    {
        return $this->params;
    }

    /**
     * Set parsed params to route
     * @param array $params
     * @return Route
     */
    public function setParams(array $params): Route
    {
        $this->params = $params;

        return $this;
    }

    /**
     * Get name for route
     * @return string|null
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * Set name for route
     * @param string $name
     * @return Route
     */
    public function setName(string $name): Route
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get group for route
     * @return RouteGroup|null
     */
    public function getGroup(): ?RouteGroup
    {
        return $this->group;
    }

    /**
     * Set group for route
     * @param RouteGroup $group
     * @return Route
     */
    public function setGroup(RouteGroup $group): Route
    {
        $this->group = $group;
        $prefix = $group->getPrefix();
        $path = $this->getPath();

        if (strcmp($prefix, substr($path, 0, strlen($prefix))) !== 0) {
            $this->path = $prefix . $path;
        }

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function process(
        ServerRequestInterface $request,
        RequestHandlerInterface $handler
    ): ResponseInterface {
        $handler = $this->getHandler();

        $params = $this->getParams();
        foreach ($params as $key => $value) {
            if (preg_match('/[0-9]+/i', $value)) {
                settype($params[$key], 'integer');
            }
        }

        return Reflector::with($this->getContainer())
            ->resolveCallable($handler, $params);
    }
}
