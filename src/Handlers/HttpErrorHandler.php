<?php


namespace Atom\ErrorHandling\Handlers;

use Atom\DI\Exceptions\CircularDependencyException;
use Atom\DI\Exceptions\ContainerException;
use Atom\DI\Exceptions\NotFoundException;
use Atom\DI\Exceptions\StorageNotFoundException;
use Atom\ErrorHandling\Contracts\ErrorHandlerContract;
use Atom\ErrorHandling\Contracts\HttpErrorRendererContract;
use Atom\ErrorHandling\DefaultHttpErrorRenderer;
use Atom\ErrorHandling\Exceptions\HttpAbortException;
use Atom\ErrorHandling\Exceptions\HttpResponseException;
use Atom\Web\WebApp;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class HttpErrorHandler implements ErrorHandlerContract
{

    public function support(WebApp $app, ServerRequestInterface $request, $error): bool
    {
        return ($error instanceof HttpAbortException) || ($error instanceof HttpResponseException);
    }

    /**
     * @param WebApp $app
     * @param ServerRequestInterface $request
     * @param $error
     * @return ResponseInterface|null
     * @throws CircularDependencyException
     * @throws ContainerException
     * @throws HttpResponseException
     * @throws NotFoundException
     * @throws StorageNotFoundException
     */
    public function handle(WebApp $app, ServerRequestInterface $request, $error): ?ResponseInterface
    {
        if ($error instanceof HttpAbortException) {
            $this->handleAbortException($app, $request, $error);
            return null;
        }
        return $this->handleResponseException($error);
    }

    public function shouldStop(): bool
    {
        return false;
    }

    /**
     * @param WebApp $app
     * @param ServerRequestInterface $request
     * @param HttpAbortException $error
     * @throws HttpResponseException
     * @throws StorageNotFoundException
     * @throws CircularDependencyException
     * @throws ContainerException
     * @throws NotFoundException
     */
    private function handleAbortException(WebApp $app, ServerRequestInterface $request, HttpAbortException $error)
    {
        if ($app->container()->has(HttpErrorRendererContract::class)) {
            /**
             * @var HttpErrorRendererContract $renderer
             */
            $renderer = $app->container()->get(HttpErrorRendererContract::class);
        } else {
            $renderer = new DefaultHttpErrorRenderer();
        }
        throw new HttpResponseException($renderer->render($app, $request, $error));
    }

    private function handleResponseException(HttpResponseException $error): ResponseInterface
    {
        return $error->getResponse();
    }
}
