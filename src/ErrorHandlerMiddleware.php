<?php


namespace Atom\ErrorHandling;

use Atom\DI\Exceptions\CircularDependencyException;
use Atom\DI\Exceptions\ContainerException;
use Atom\DI\Exceptions\NotFoundException;
use Atom\DI\Exceptions\StorageNotFoundException;
use Atom\Web\Request;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Throwable;

class ErrorHandlerMiddleware implements MiddlewareInterface
{
    /**
     * @var ErrorManager
     */
    private $manager;

    public function __construct(ErrorManager $manager)
    {
        $this->manager = $manager;
    }

    /**
     * @param ServerRequestInterface|Request $request
     * @param RequestHandlerInterface $handler
     * @return ResponseInterface
     * @throws CircularDependencyException
     * @throws ContainerException
     * @throws NotFoundException
     * @throws StorageNotFoundException|Throwable
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        try {
            return $handler->handle($request);
        } catch (Throwable $exception) {
            $this->manager->handle($request, $exception);
            throw $exception;
        }
    }
}
