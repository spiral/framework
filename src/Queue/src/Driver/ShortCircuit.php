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
final class ShortCircuit implements QueueInterface
{
    use QueueTrait;

    /** @var HandlerRegistryInterface */
    private $registry;
    /** @var FailedJobHandlerInterface*/
    private $failedJobHandler;

    public function __construct(
        HandlerRegistryInterface $registry,
        FailedJobHandlerInterface $failedJobHandler
    ) {
        $this->registry = $registry;
        $this->failedJobHandler = $failedJobHandler;
    }

    /** @inheritdoc */
    public function push(string $name, array $payload = [], OptionsInterface $options = null): string
    {
        if ($options !== null && $options->getDelay()) {
            sleep($options->getDelay());
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
