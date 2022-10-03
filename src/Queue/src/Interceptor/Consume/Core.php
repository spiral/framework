<?php

declare(strict_types=1);

namespace Spiral\Queue\Interceptor\Consume;

use Psr\EventDispatcher\EventDispatcherInterface;
use Spiral\Core\CoreInterface;
use Spiral\Queue\Event\JobProcessed;
use Spiral\Queue\Event\JobProcessing;
use Spiral\Queue\HandlerRegistryInterface;

/**
 * @psalm-type TParameters = array{
 *     driver: non-empty-string,
 *     queue: non-empty-string,
 *     id: non-empty-string,
 *     payload: array,
 *     context: array
 * }
 */
final class Core implements CoreInterface
{
    public function __construct(
        private readonly HandlerRegistryInterface $registry,
        private readonly ?EventDispatcherInterface $dispatcher = null
    ) {
    }

    /**
     * @param-assert TParameters $parameters
     */
    public function callAction(string $controller, string $action, array $parameters = []): mixed
    {
        \assert(\is_string($parameters['driver']));
        \assert(\is_string($parameters['queue']));
        \assert(\is_string($parameters['id']));
        \assert(\is_array($parameters['payload']));

        $this->dispatchEvent(JobProcessing::class, $controller, $parameters);

        /** @psalm-suppress TooManyArguments */
        $this->registry
            ->getHandler($controller)
            ->handle($controller, $parameters['id'], $parameters['payload'], $parameters['context'] ?? []);

        $this->dispatchEvent(JobProcessed::class, $controller, $parameters);

        return null;
    }

    /**
     * @param class-string $event
     * @param-assert TParameters $parameters
     */
    private function dispatchEvent(string $event, string $name, array $parameters): void
    {
        $this->dispatcher?->dispatch(new $event(
            name: $name,
            driver: $parameters['driver'],
            queue: $parameters['queue'],
            id: $parameters['id'],
            payload: $parameters['payload'],
            context: $parameters['context'] ?? []
        ));
    }
}
