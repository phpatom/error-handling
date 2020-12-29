<?php


namespace Atom\ErrorHandling;

use Atom\App\App;
use Atom\DI\Exceptions\CircularDependencyException;
use Atom\DI\Exceptions\ContainerException;
use Atom\DI\Exceptions\NotFoundException;
use Atom\DI\Exceptions\StorageNotFoundException;
use Atom\ErrorHandling\Contracts\ErrorHandlerContract;
use Atom\Web\WebApp;
use Exception;
use Psr\Http\Message\ServerRequestInterface;

class ErrorManager
{
    /**
     * @var ErrorHandlerContract[]
     */
    private $handlers;
    /**
     * @var App
     */
    private $app;

    public function __construct(WebApp $app, $handlers = [])
    {
        foreach ($handlers as $handler) {
            $this->addHandler($handler);
        }
        $this->app = $app;
    }

    public function addHandler(ErrorHandlerContract $handler)
    {
        $this->handlers[] = $handler;
    }

    /**
     * @param ServerRequestInterface $request
     * @param Exception $exception
     * @return void
     * @throws CircularDependencyException
     * @throws ContainerException
     * @throws NotFoundException
     * @throws StorageNotFoundException
     * @throws Exception
     */
    public function handle(ServerRequestInterface $request, Exception $exception)
    {
        $response = null;
        try {
            foreach ($this->handlers as $handler) {
                if (!$handler->support($this->app, $request, $exception)) {
                    continue;
                }

                $response = $handler->handle($this->app, $request, $exception);
                if ($handler->shouldStop()) {
                    break;
                }
            }
        } catch (Exception $err) {
            $this->handle($request, $err);
        }
        if (!is_null($response)) {
            return $this->app->requestHandler()->emit($response);
        }
        throw $exception;
    }
}
