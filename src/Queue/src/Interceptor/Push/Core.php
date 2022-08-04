<?php

declare(strict_types=1);

namespace Spiral\Queue\Interceptor\Push;

use Spiral\Core\CoreInterface;
use Spiral\Queue\OptionsInterface;
use Spiral\Queue\QueueInterface;

final class Core implements CoreInterface
{
    public function __construct(
        private readonly QueueInterface $connection
    ) {
    }

    /**
     * @param array{options: ?OptionsInterface, payload: array} $parameters
     *
     * @psalm-suppress ParamNameMismatch
     */
    public function callAction(
        string $name,
        string $action,
        array $parameters = ['options' => null, 'payload' => []]
    ): string {
        return $this->connection->push(
            name: $name,
            payload: $parameters['payload'],
            options: $parameters['options']
        );
    }
}
