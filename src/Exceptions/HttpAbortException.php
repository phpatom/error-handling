<?php


namespace Atom\ErrorHandling\Exceptions;

use Exception;
use Throwable;

class HttpAbortException extends Exception
{
    /**
     * @var int
     */
    private $statusCode;
    /**
     * @var array
     */
    private $headers;
    /**
     * @var mixed|Throwable
     */
    private $originalException;


    public function __construct(
        int $statusCode = 500,
        string $message = "",
        array $headers = [],
        $originalException = null
    ) {
        $this->statusCode = $statusCode;
        $this->message = $message;
        $this->headers = $headers;
        $this->originalException = $originalException;
        parent::__construct($message);
    }

    /**
     * @return int
     */
    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    /**
     * @return array
     */
    public function getHeaders(): array
    {
        return $this->headers;
    }

    /**
     * @return mixed|Throwable
     */
    public function getOriginalException(): ?Throwable
    {
        return $this->originalException;
    }

    /**
     * @param mixed|Throwable $originalException
     */
    public function setOriginalException(?Throwable $originalException): void
    {
        $this->originalException = $originalException;
    }
}
