<?php

declare(strict_types=1);

namespace Limbo\Routing\Exception;

use Exception;
use RuntimeException;
use Psr\Http\Message\ResponseInterface as Response;

/**
 * Class HttpException
 * @package Limbo\Routing\Exception
 */
class HttpException extends RuntimeException
{
    /**
     * Response headers
     * @var array
     */
    protected array $headers = [];

    /**
     * Response status code
     * @var int
     */
    protected int $status;

    /**
     * HttpException constructor.
     * @param int $status
     * @param string|null $message
     * @param Exception|null $previous
     * @param array $headers
     * @param int $code
     */
    public function __construct(
        int $status,
        ?string $message = null,
        ?Exception $previous = null,
        array $headers = [],
        int $code = 0
    ) {
        $this->headers = $headers;
        $this->message = $message;
        $this->status = $status;

        parent::__construct($message, $code, $previous);
    }

    /**
     * Convert exception response to json format
     * @param Response $response
     * @return Response
     */
    public function toJson(Response $response): Response
    {
        $this->headers['content-type'] = 'application/json; charset=utf8';
        foreach ($this->headers as $key => $value) {
            $response = $response->withAddedHeader($key, $value);
        }

        if ($response->getBody()->isWritable()) {
            $response->getBody()->write(json_encode([
                'status_code' => $this->status,
                'reason_phrase' => $this->message
            ]));
        }

        return $response->withStatus($this->status, $this->message);
    }

    /**
     * Get response headers from this exception
     * @return array
     */
    public function getHeaders(): array
    {
        return $this->headers;
    }

    /**
     * Get response status code from this exception
     * @return int
     */
    public function getStatusCode(): int
    {
        return $this->status;
    }
}
