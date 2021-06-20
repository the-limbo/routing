<?php

declare(strict_types=1);

namespace Limbo\Routing;

use Psr\Http\Server\RequestHandlerInterface;
use Limbo\Container\ContainerAwareInterface;
use Limbo\Routing\Middleware\MiddlewareAwareInterface;

/**
 * Interface DispatcherInterface
 * @package Limbo\Routing
 */
interface DispatcherInterface extends
    RequestHandlerInterface,
    MiddlewareAwareInterface,
    ContainerAwareInterface
{

}
