<?php


namespace Atom\ErrorHandling;

use Atom\DI\Exceptions\CircularDependencyException;
use Atom\DI\Exceptions\ContainerException;
use Atom\DI\Exceptions\NotFoundException;
use Atom\DI\Exceptions\StorageNotFoundException;
use Atom\ErrorHandling\Contracts\ErrorHandlerContract;
use Atom\Web\Application;
use Psr\Http\Message\ServerRequestInterface;
use Throwable;

class ErrorManager
{
    /**
     * @var ErrorHandlerContract[]
     */
    private $handlers;
    /**
     * @var Application
     */
    private $app;

    public function __construct(Application $app, $handlers = [])
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
     * @param Throwable $exception
     * @return void
     * @throws CircularDependencyException
     * @throws ContainerException
     * @throws NotFoundException
     * @throws StorageNotFoundException
     * @throws Throwable
     */
    public function handle(ServerRequestInterface $request, Throwable $exception)
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
        } catch (Throwable $err) {
            $this->handle($request, $err);
        }
        if (!is_null($response)) {
            $this->app->requestHandler()->emit($response);
            return;
        }
        throw $exception;
    }
}
