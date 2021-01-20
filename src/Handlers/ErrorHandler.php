<?php


namespace Atom\ErrorHandling\Handlers;

use Atom\ErrorHandling\Contracts\ErrorHandlerContract;
use Atom\ErrorHandling\Exceptions\HttpAbortException;
use Atom\ErrorHandling\Exceptions\HttpResponseException;
use Atom\Routing\Exceptions\MethodNotAllowedException;
use Atom\Routing\Exceptions\RouteNotFoundException;
use Atom\Web\Application;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use RuntimeException;

class ErrorHandler implements ErrorHandlerContract
{
    /**
     * @var array
     */
    private $map;

    public function __construct(array $map = [])
    {
        if (count($map) === 0) {
            $this->map = [];
            return;
        }
        foreach ($map as $key => $item) {
            $this->map(
                $key,
                $item["status_code"] ?? 500,
                $item["headers"] ?? [],
                $item["message"] ?? ""
            );
        }
    }

    public function support(Application $app, ServerRequestInterface $request, $error): bool
    {
        return ($this->supportError($error) || $this->hasDefault()) &&
            !($error instanceof HttpAbortException) &&
            !($error instanceof HttpResponseException);
    }

    public function supportError($error): bool
    {
        return array_key_exists(get_class($error), $this->map ?? []);
    }

    public function hasDefault(): bool
    {
        return array_key_exists("*", $this->map ?? []);
    }

    public function map(
        string $className,
        int $statusCode = 500,
        $headers = [],
        string $message = ""
    ): ErrorHandler
    {
        $this->map[$className] = [
            "status_code" => $statusCode,
            "headers" => $headers,
            "message" => $message
        ];
        return $this;
    }

    public function default(
        int $statusCode = 500,
        $headers = [],
        string $message = ""
    ): ErrorHandler
    {
        $this->map["*"] = [
            "status_code" => $statusCode,
            "headers" => $headers,
            "message" => $message
        ];
        return $this;
    }

    /**
     * @param Application $app
     * @param ServerRequestInterface $request
     * @param $error
     * @return ResponseInterface|null
     * @throws HttpAbortException
     */
    public function handle(Application $app, ServerRequestInterface $request, $error): ?ResponseInterface
    {
        if ($this->supportError($error)) {
            $data = $this->map[get_class($error)];
        } else {
            if (!$this->hasDefault()) {
                throw new RuntimeException("no default response was set");
            }
            $data = $this->map["*"];
        }
        throw new HttpAbortException($data["status_code"], $data["message"], $data["headers"], $error);
    }

    public function shouldStop(): bool
    {
        return true;
    }

    public static function create(): ErrorHandler
    {
        return (new self())
            ->map(RouteNotFoundException::class, 404)
            ->map(MethodNotAllowedException::class, 405)
            ->default();
    }
}
