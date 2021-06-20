<?php

declare(strict_types=1);

namespace Limbo\Routing\Exception;

use Exception;

/**
 * If method not allowed called this exception
 * Class MethodNotAllowedException
 * @package Limbo\Routing\Exception
 */
class MethodNotAllowedException extends HttpException
{
    /**
     * MethodNotAllowedException constructor.
     * @param array $allowed
     * @param string $message
     * @param Exception|null $previous
     * @param int $code
     */
    public function __construct(
        array $allowed = [],
        string $message = 'Method Not Allowed',
        ?Exception $previous = null,
        int $code = 0
    ) {
        $headers = [
            'Allow' => implode(', ', $allowed)
        ];
        parent::__construct(405, $message, $previous, $headers, $code);
    }
}
