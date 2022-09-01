<?php

declare(strict_types=1);

namespace Spiral\Queue\Interceptor\Consume;

use Spiral\Core\CoreInterface;
use Spiral\Queue\HandlerRegistryInterface;

/**
 * @psalm-type TParameters = array{
 *     driver: non-empty-string,
 *     queue: non-empty-string,
 *     id: non-empty-string,
 *     payload: array
 * }
 */
final class Core implements CoreInterface
{
    public function __construct(
        private readonly HandlerRegistryInterface $registry
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

        $this->registry
            ->getHandler($controller)
            ->handle($controller, $parameters['id'], $parameters['payload']);

        return null;
    }
}
