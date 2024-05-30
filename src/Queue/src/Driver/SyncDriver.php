<?php

declare(strict_types=1);

namespace Spiral\Queue\Driver;

use Ramsey\Uuid\Uuid;
use Spiral\Queue\ExtendedOptionsInterface;
use Spiral\Queue\Interceptor\Consume\Handler;
use Spiral\Queue\OptionsInterface;
use Spiral\Queue\QueueInterface;
use Spiral\Queue\QueueTrait;

/**
 * Runs all the jobs in the same process.
 */
final class SyncDriver implements QueueInterface
{
    use QueueTrait;

    public function __construct(
        private readonly Handler $coreHandler
    ) {
    }

    /** @inheritdoc */
    public function push(string $name, mixed $payload = [], OptionsInterface $options = null): string
    {
        if ($options !== null && $options->getDelay()) {
            \sleep($options->getDelay());
        }

        $id = (string)Uuid::uuid4();

        $this->coreHandler->handle(
            name: $name,
            driver: 'sync',
            queue: 'default',
            id: $id,
            payload: $payload,
            headers: $options instanceof ExtendedOptionsInterface ? $options->getHeaders() : [],
        );

        return $id;
    }
}
