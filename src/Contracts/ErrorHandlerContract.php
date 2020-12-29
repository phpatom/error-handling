<?php


namespace Atom\ErrorHandling\Contracts;

use Atom\Web\WebApp;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

interface ErrorHandlerContract
{
    public function support(WebApp $app, ServerRequestInterface $request, $error): bool;

    public function handle(WebApp $app, ServerRequestInterface $request, $error): ?ResponseInterface;

    public function shouldStop(): bool;
}
