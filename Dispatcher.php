<?php

declare(strict_types=1);

namespace Limbo\Routing;

use FastRoute\RouteCollector;
use Psr\Http\Message\ResponseInterface;
use Limbo\Container\ContainerAwareTrait;
use FastRoute\Dispatcher\GroupCountBased;
use Psr\Http\Message\ServerRequestInterface;
use Limbo\Routing\Exception\NotFoundException;
use Limbo\Routing\Middleware\ThrowableMiddleware;
use Limbo\Routing\Middleware\MiddlewareAwareTrait;
use Limbo\Routing\Middleware\ErrorHandlerInterface;
use Limbo\Routing\Middleware\ErrorHandlerMiddleware;
use Limbo\Routing\Exception\MethodNotAllowedException;

/**
 * Class Dispatcher
 * @package Limbo\Routing
 */
class Dispatcher extends GroupCountBased implements DispatcherInterface
{
    use MiddlewareAwareTrait;
    use ContainerAwareTrait;

    /**
     * Dispatcher constructor.
     * @param RouteCollector $collector
     */
    public function __construct(RouteCollector $collector)
    {
        parent::__construct($collector->getData());
    }

    /**
     * @inheritDoc
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $method = $request->getMethod();
        $uri = $request->getUri()->getPath();
        $match = $this->dispatch($method, $uri);

        switch ($match[0]) {
            case Dispatcher::NOT_FOUND:
                $this->setNotFoundMiddleware(sprintf('Route with path "%s" not found', $uri));
                break;
            case Dispatcher::METHOD_NOT_ALLOWED:
                $this->setMethodNotAllowedMiddleware($match[1]);
                break;
            case Dispatcher::FOUND:
                $route = $this->getRoute($method, $uri, $match[1])->setParams($match[2]);
                $request = $this->getRequestWithRoute($route, $request);
                $this->setFoundMiddleware($route);
                break;
        }

        $middleware = $this->shiftMiddleware();
        return $middleware->process($request, $this);
    }

    /**
     * Get handler to found route
     * @param string $method
     * @param string $uri
     * @param Route|callable|string $handler
     * @return Route
     */
    protected function getRoute(string $method, string $uri, $handler): Route
    {
        if ($handler instanceof Route) {
            return $handler;
        }

        return (new Route($method, $uri, $handler))->setContainer($this->getContainer());
    }

    /**
     * Inject parsed parameters from route to request
     * @param Route $route
     * @param ServerRequestInterface $request
     * @return ServerRequestInterface
     */
    protected function getRequestWithRoute(Route $route, ServerRequestInterface $request): ServerRequestInterface
    {
        $params = $route->getParams();

        foreach ($params as $key => $value) {
            $request = $request->withAttribute($key, $value);
        }

        return $request;
    }

    /**
     * Rebuild middleware queue
     * @param Route $route
     * @return void
     */
    protected function setFoundMiddleware(Route $route): void
    {
        /**
         * Rebuild global middleware list
         */
        foreach ($this->middlewares() as $id => $middleware) {
            $this->middlewares[$id] = $middleware;
        }

        /**
         * Set error handler middleware
         */
        $this->setErrorHandlerMiddleware();

        /**
         * If route has parent group - include group middlewares to global list
         */
        if (($group = $route->getGroup()) !== null) {
            $this->middleware(...$group->middlewares());
        }

        /**
         * Include route middlewares to global list
         */
        $this->middleware(...$route->middlewares());
        $this->prependMiddleware($route);
    }

    /**
     * Set error handler middleware
     * @return void
     */
    protected function setErrorHandlerMiddleware(): void
    {
        if ($this->getContainer()->has(ErrorHandlerInterface::class)) {
            $this->prependMiddleware($this->getContainer()->get(ErrorHandlerInterface::class));
            return;
        }

        $this->prependMiddleware(new ErrorHandlerMiddleware());
    }

    /**
     * Not found route exception
     * @param string $error
     * @return void
     */
    protected function setNotFoundMiddleware(string $error): void
    {
        if ($this->getContainer()->has(ThrowableMiddleware::class)) {
            $this->prependMiddleware(
                $this->getContainer()->get(ThrowableMiddleware::class)(new NotFoundException($error))
            );
            return;
        }

        $this->prependMiddleware((new ThrowableMiddleware())(new NotFoundException($error)));
    }

    /**
     * Not allowed method for route
     * @param array $allowed
     * @return void
     */
    protected function setMethodNotAllowedMiddleware(array $allowed)
    {
        if ($this->getContainer()->has(ThrowableMiddleware::class)) {
            $this->prependMiddleware(
                $this->getContainer()->get(ThrowableMiddleware::class)(new MethodNotAllowedException($allowed))
            );
            return;
        }

        $this->prependMiddleware((new ThrowableMiddleware())(new MethodNotAllowedException($allowed)));
    }
}
