<?php

declare(strict_types=1);

namespace Spiral\Queue\Interceptor\Push;

use Spiral\Core\CoreInterface;
use Spiral\Queue\OptionsInterface;
use Spiral\Queue\QueueInterface;

/**
 * @psalm-type TParameters = array{options: ?OptionsInterface, payload: array}
 */
final class Core implements CoreInterface
{
    public function __construct(
        private readonly QueueInterface $connection
    ) {
    }

    /**
     * @param-assert TParameters $parameters
     */
    public function callAction(
        string $controller,
        string $action,
        array $parameters = ['options' => null, 'payload' => []]
    ): string {
        \assert($parameters['options'] === null || $parameters['options'] instanceof OptionsInterface);
        \assert(\is_array($parameters['payload']));

        return $this->connection->push(
            name: $controller,
            payload: $parameters['payload'],
            options: $parameters['options']
        );
    }
}
