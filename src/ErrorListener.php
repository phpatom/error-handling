<?php


namespace Atom\ErrorHandling;

use Atom\DI\Exceptions\CircularDependencyException;
use Atom\DI\Exceptions\ContainerException;
use Atom\DI\Exceptions\NotFoundException;
use Atom\DI\Exceptions\StorageNotFoundException;
use Atom\Event\AbstractEventListener;
use Atom\Web\Events\AppFailed;
use Atom\Web\Events\ServiceProviderFailed;
use Atom\Web\Request;

class ErrorListener extends AbstractEventListener
{
    /**
     * @var ErrorManager
     */
    private $errorManager;

    public function __construct(ErrorManager $errorManager)
    {
        $this->errorManager = $errorManager;
    }

    /**
     * @param $event
     * @throws CircularDependencyException
     * @throws ContainerException
     * @throws NotFoundException
     * @throws StorageNotFoundException
     */
    public function on($event): void
    {
        if ($event instanceof AppFailed) {
            $this->errorManager->handle($event->getRequest(), $event->getException());
        }
        if ($event instanceof ServiceProviderFailed) {
            $this->errorManager->handle(Request::incoming(), $event->getException());
        }
    }
}
