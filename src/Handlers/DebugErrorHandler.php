<?php


namespace Atom\ErrorHandling\Handlers;

use Atom\ErrorHandling\Contracts\ErrorHandlerContract;
use Atom\Web\Request;
use Atom\Web\Response;
use Atom\Web\Application;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Whoops\Handler\JsonResponseHandler;
use Whoops\Handler\PlainTextHandler;
use Whoops\Handler\PrettyPageHandler;
use Whoops\Run;
use Whoops\Util\Misc;

class DebugErrorHandler implements ErrorHandlerContract
{

    public function support(Application $app, ServerRequestInterface $request, $error): bool
    {
        return $app->env()->isDev() || $app->env()->get("APP_DEBUG", false);
    }

    public function handle(Application $app, ServerRequestInterface $request, $error): ?ResponseInterface
    {
        $request = Request::convert($request);
        $whoops = new Run;
        $whoops->allowQuit(false);
        if (Misc::isCommandLine()) {
            $whoops->writeToOutput(true);
            $whoops->pushHandler(new PlainTextHandler());
            $whoops->handleException($error);
            return null;
        }
        $whoops->writeToOutput(false);
        if ($request->expectsJson()) {
            $whoops->pushHandler(
                (new JsonResponseHandler())
                    ->addTraceToOutput(true)
            );
            $json = $whoops->handleException($error);
            return Response::jsonString($json, 500);
        }
        $whoops->pushHandler(new PrettyPageHandler);
        $html = $whoops->handleException($error);
        return Response::html($html, 500);
    }

    public function shouldStop(): bool
    {
        return true;
    }
}
