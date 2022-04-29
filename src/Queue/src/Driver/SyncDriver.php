<?php

declare(strict_types=1);

namespace Spiral\Queue\Driver;

use Ramsey\Uuid\Uuid;
use Spiral\Queue\Failed\FailedJobHandlerInterface;
use Spiral\Queue\HandlerRegistryInterface;
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
        private readonly HandlerRegistryInterface $registry,
        private readonly FailedJobHandlerInterface $failedJobHandler
    ) {
    }

    /** @inheritdoc */
    public function push(string $name, array $payload = [], OptionsInterface $options = null): string
    {
        if ($options !== null && $options->getDelay()) {
            \sleep($options->getDelay());
        }

        $id = (string)Uuid::uuid4();

        try {
            $this->registry->getHandler($name)->handle($name, $id, $payload);
        } catch (\Throwable $e) {
            $this->failedJobHandler->handle(
                'sync',
                'default',
                $name,
                $payload,
                $e
            );
        }

        return $id;
    }
}
