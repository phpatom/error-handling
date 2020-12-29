<?php


namespace Atom\ErrorHandling;

use Atom\DI\Exceptions\CircularDependencyException;
use Atom\DI\Exceptions\ContainerException;
use Atom\DI\Exceptions\NotFoundException;
use Atom\DI\Exceptions\StorageNotFoundException;
use Atom\Web\Request;
use Exception;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

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
     * @throws StorageNotFoundException
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        try {
            return $handler->handle($request);
        } catch (Exception $exception) {
            $this->manager->handle($request, $exception);
            throw $exception;
        }
    }
}
