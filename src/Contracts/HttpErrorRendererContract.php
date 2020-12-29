<?php


namespace Atom\ErrorHandling\Contracts;

use Atom\ErrorHandling\Exceptions\HttpAbortException;
use Atom\Web\WebApp;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

interface HttpErrorRendererContract
{
    public function render(
        WebApp $app,
        ServerRequestInterface $request,
        HttpAbortException $exception
    ): ResponseInterface;
}
