<?php


namespace Atom\ErrorHandling\Exceptions;

use Exception;
use Psr\Http\Message\ResponseInterface;

class HttpResponseException extends Exception
{
    /**
     * @var ResponseInterface
     */
    private $response;

    public function __construct(ResponseInterface $response, ?string $message = null)
    {
        if (is_null($message)) {
            $message = "Http exception {$response->getStatusCode()}: {$response->getReasonPhrase()}";
        }
        parent::__construct($message);
        $this->response = $response;
    }

    /**
     * @return ResponseInterface
     */
    public function getResponse(): ResponseInterface
    {
        return $this->response;
    }
}
