<?php


namespace Atom\ErrorHandling\Contracts;

use Atom\Web\Application;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

interface ErrorHandlerContract
{
    public function support(Application $app, ServerRequestInterface $request, $error): bool;

    public function handle(Application $app, ServerRequestInterface $request, $error): ?ResponseInterface;

    public function shouldStop(): bool;
}
