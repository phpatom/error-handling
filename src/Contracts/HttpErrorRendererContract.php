<?php


namespace Atom\ErrorHandling\Contracts;

use Atom\ErrorHandling\Exceptions\HttpAbortException;
use Atom\Web\Application;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

interface HttpErrorRendererContract
{
    public function render(
        Application $app,
        ServerRequestInterface $request,
        HttpAbortException $exception
    ): ResponseInterface;
}
