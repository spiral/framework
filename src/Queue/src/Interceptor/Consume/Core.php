<?php

declare(strict_types=1);

namespace Spiral\Queue\Interceptor\Consume;

use Spiral\Core\CoreInterface;
use Spiral\Queue\HandlerRegistryInterface;

final class Core implements CoreInterface
{
    public function __construct(
        private readonly HandlerRegistryInterface $registry
    ) {
    }

    /**
     * @param array{driver: non-empty-string, queue: non-empty-string, id: non-empty-string, payload: array}|array<empty, empty> $parameters
     *
     * @psalm-suppress ParamNameMismatch
     */
    public function callAction(string $name, string $action, array $parameters = []): mixed
    {
        $this->registry
            ->getHandler($name)
            ->handle($name, $parameters['id'], $parameters['payload']);

        return null;
    }
}
