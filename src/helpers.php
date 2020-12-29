<?php

use Atom\ErrorHandling\Exceptions\HttpAbortException;

if (!function_exists("abort")) {
    /**
     * @param int $statusCode
     * @param string $message
     * @param array $headers
     * @param Throwable|null $exception
     * @throws HttpAbortException
     */
    function abort(int $statusCode = 500, string $message = "", $headers = [], Throwable $exception = null)
    {
        throw new HttpAbortException($statusCode, $message, $headers, $exception);
    }
}