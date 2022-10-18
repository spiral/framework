<?php

declare(strict_types=1);

namespace Spiral\Events\Interceptor;

use Psr\EventDispatcher\EventDispatcherInterface;
use Spiral\Core\CoreInterface;

/**
 * @psalm-type TParameters = array{event: object}
 */
final class Core implements CoreInterface
{
    public function __construct(
        private readonly EventDispatcherInterface $dispatcher
    ) {
    }

    /**
     * @param string $controller event name
     * @param-assert TParameters $parameters
     */
    public function callAction(string $controller, string $action, array $parameters = []): object
    {
        \assert(\is_object($parameters['event']));

        return $this->dispatcher->dispatch($parameters['event']);
    }
}
