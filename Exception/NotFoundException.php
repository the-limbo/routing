<?php

declare(strict_types=1);

namespace Limbo\Routing\Exception;

use Exception;

/**
 * If route not found called this exception
 * Class NotFoundException
 * @package Limbo\Routing\Exception
 */
class NotFoundException extends HttpException
{
    /**
     * NotFoundException constructor.
     * @param string $message
     * @param Exception|null $previous
     * @param int $code
     */
    public function __construct(string $message = 'Not Found', ?Exception $previous = null, int $code = 0)
    {
        parent::__construct(404, $message, $previous, [], $code);
    }
}
