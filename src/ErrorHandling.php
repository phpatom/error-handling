<?php


namespace Atom\ErrorHandling;

use Atom\App\App;
use Atom\App\Contracts\ServiceProviderContract;
use Atom\DI\Exceptions\CircularDependencyException;
use Atom\DI\Exceptions\ContainerException;
use Atom\DI\Exceptions\NotFoundException;
use Atom\DI\Exceptions\StorageNotFoundException;
use Atom\Event\Exceptions\ListenerAlreadyAttachedToEvent;
use Atom\Web\Events\AppFailed;
use Atom\Web\Events\ServiceProviderFailed;
use Atom\Web\Exceptions\RequestHandlerException;
use Atom\ErrorHandling\Contracts\ErrorHandlerContract;
use Atom\ErrorHandling\Contracts\HttpErrorRendererContract;
use Atom\ErrorHandling\Handlers\DebugErrorHandler;
use Atom\ErrorHandling\Handlers\ErrorHandler;
use Atom\ErrorHandling\Handlers\HttpErrorHandler;
use Atom\Web\WebApp;
use InvalidArgumentException;

class ErrorHandling implements ServiceProviderContract
{

    /**
     * @var array
     */
    private $handlers;

    /**
     * @var HttpErrorRendererContract
     */
    private $errorRenderer;
    /**
     * @var ErrorHandler
     */
    private $defaultHandler;

    public function __construct(array $handlers = [])
    {
        $this->handlers = $handlers;
    }

    /**
     * @param App $app
     * @throws CircularDependencyException
     * @throws ContainerException
     * @throws NotFoundException
     * @throws StorageNotFoundException
     * @throws ListenerAlreadyAttachedToEvent
     * @throws RequestHandlerException
     */
    public function register(App $app)
    {
        if (!($app instanceof WebApp)) {
            throw new InvalidArgumentException("Error handling can only be used with Web App");
        }
        $errorManager = new ErrorManager($app, $this->handlers);
        $app->container()->singletons()->bindInstance($errorManager);
        $app->eventDispatcher()->addEventListener(AppFailed::class, $listener = new ErrorListener($errorManager));
        $app->eventDispatcher()->addEventListener(ServiceProviderFailed::class, $listener);

        $app->requestHandler()->add(new ErrorHandlerMiddleware($errorManager));
        if (!is_null($this->defaultHandler)) {
            $app->container()->singletons()
                ->bindInstance($this->defaultHandler);
        }
        if (!is_null($this->errorRenderer)) {
            $app->container()->singletons()
                ->store(
                    HttpErrorRendererContract::class,
                    $app->container()->as()->object($this->errorRenderer)
                );
            $app->container()->singletons()
                ->bindInstance($this->errorRenderer);
        }
    }

    public function withDebugHandler(): ErrorHandling
    {
        $this->with(new DebugErrorHandler());
        return $this;
    }

    public function withHttpHandler(): ErrorHandling
    {
        $this->with(new HttpErrorHandler());
        return $this;
    }

    public function withDefaultErrorHandler(): ErrorHandling
    {
        $this->defaultHandler = ErrorHandler::create();
        return $this->with($this->defaultHandler);
    }


    public static function default(): ErrorHandling
    {
        return (new self())
            ->withDebugHandler()
            ->withHttpHandler()
            ->withDefaultErrorHandler();
    }

    public function withErrorRenderer(HttpErrorRendererContract $renderer): ErrorHandling
    {
        $this->errorRenderer = $renderer;
        return $this;
    }

    public function with(ErrorHandlerContract $handler): ErrorHandling
    {
        $this->handlers[] = $handler;
        return $this;
    }

    public static function create(): ErrorHandling
    {
        return (new self())
            ->withDebugHandler()
            ->withHttpHandler();
    }
}
