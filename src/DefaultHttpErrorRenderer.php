<?php


namespace Atom\ErrorHandling;

use Atom\ErrorHandling\Contracts\HttpErrorRendererContract;
use Atom\ErrorHandling\Exceptions\HttpAbortException;
use Atom\Web\Request;
use Atom\Web\Response;
use Atom\Web\WebApp;
use InvalidArgumentException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class DefaultHttpErrorRenderer implements HttpErrorRendererContract
{
    const STATUS_CODE_MESSAGE_INFORMATIONAL = "Informational";
    const STATUS_CODE_MESSAGE_SUCCESS = "Success";
    const STATUS_CODE_MESSAGE_REDIRECTION = "Redirection";
    const STATUS_CODE_MESSAGE_CLIENT_ERROR = "Client Error";
    const STATUS_CODE_MESSAGE_SERVER_ERROR = "Server Error";

    const STATUS_CODE_MESSAGE = [
        100 => "Continue",
        101 => "Switching Protocols",
        102 => "Processing",
        200 => "OK",
        201 => "Created",
        202 => "Accepted",
        203 => "Non-authoritative Information",
        204 => "No Content",
        205 => "Reset Content",
        206 => "Partial Content",
        207 => "Multi-Status",
        208 => "Already Reported",
        226 => "IM Used",
        300 => "Multiple Choices",
        301 => "Moved Permanently",
        302 => "Found",
        303 => "See Other",
        304 => "Not Modified",
        305 => "Use Proxy",
        307 => "Temporary Redirect",
        308 => "Permanent Redirect",
        400 => "Bad Request",
        401 => "Unauthorized",
        402 => "Payment Required",
        403 => "Forbidden",
        404 => "Not Found",
        405 => "Method Not Allowed",
        406 => "Not Acceptable",
        407 => "Proxy Authentication Required",
        408 => "Request Timeout",
        409 => "Conflict",
        410 => "Gone",
        411 => "Length Required",
        412 => "Precondition Failed",
        413 => "Payload Too Large",
        414 => "Request-URI Too Long",
        415 => "Unsupported Media Type",
        416 => "Requested Range Not Satisfiable",
        417 => "Expectation Failed",
        418 => "I'm a teapot",
        421 => "Misdirected Request",
        422 => "Unprocessable Entity",
        423 => "Locked",
        424 => "Failed Dependency",
        426 => "Upgrade Required",
        428 => "Precondition Required",
        429 => "Too Many Requests",
        431 => "Request Header Fields Too Large",
        444 => "Connection Closed Without Response",
        451 => "Unavailable For Legal Reasons",
        499 => "Client Closed Request",
        500 => "Internal Server Error",
        501 => "Not Implemented",
        502 => "Bad Gateway",
        503 => "Service Unavailable",
        504 => "Gateway Timeout",
        505 => "HTTP Version Not Supported",
        506 => "Variant Also Negotiates",
        507 => "Insufficient Storage",
        508 => "Loop Detected",
        510 => "Not Extended",
        511 => "Network Authentication Required",
        599 => "Network Connect Timeout Error"
    ];

    public function render(
        WebApp $app,
        ServerRequestInterface $request,
        HttpAbortException $exception
    ): ResponseInterface {
        $request = Request::convert($request);
        $statusCode = $exception->getStatusCode();
        $statusCodeDescription = $this->getStatusCodeMessage($statusCode);
        if ($request->expectsJson()) {
            return Response::json([
                "message" => $statusCodeDescription,
                "code" => $statusCode
            ], $statusCode, $exception->getHeaders());
        }
        ob_start();
        require_once "error.stub.php";
        $html = ob_get_clean();
        return Response::html($html, $statusCode, $exception->getHeaders());
    }

    protected function getStatusCodeMessage(int $statusCode): string
    {
        if (array_key_exists($statusCode, self::STATUS_CODE_MESSAGE)) {
            return self::STATUS_CODE_MESSAGE[$statusCode];
        }
        if (100 <= $statusCode && $statusCode < 200) {
            return self::STATUS_CODE_MESSAGE_INFORMATIONAL;
        }
        if (200 <= $statusCode && $statusCode < 300) {
            return self::STATUS_CODE_MESSAGE_SUCCESS;
        }
        if (300 <= $statusCode && $statusCode < 300) {
            return self::STATUS_CODE_MESSAGE_REDIRECTION;
        }
        if (400 <= $statusCode && $statusCode < 500) {
            return self::STATUS_CODE_MESSAGE_CLIENT_ERROR;
        }
        if (500 <= $statusCode && $statusCode < 600) {
            return self::STATUS_CODE_MESSAGE_SERVER_ERROR;
        }
        throw new InvalidArgumentException("invalid status code");
    }
}
