<?php

declare(strict_types=1);

namespace Spiral\Queue\Job;

use Spiral\Core\InvokerInterface;
use Spiral\Queue\Exception\InvalidArgumentException;
use Spiral\Queue\HandlerInterface;

final class ObjectJob implements HandlerInterface
{
    /** @var InvokerInterface */
    private $invoker;

    public function __construct(InvokerInterface $invoker)
    {
        $this->invoker = $invoker;
    }

    public function handle(string $name, string $id, array $payload): void
    {
        if (!isset($payload['object'])) {
            throw new InvalidArgumentException('Payload `object` key is required.');
        }

        if (!is_object($payload['object'])) {
            throw new InvalidArgumentException('Payload `object` key value type should be an object.');
        }

        $job = $payload['object'];
        $handler = new \ReflectionClass($job);
        $this->invoker->invoke(
            [$job, $handler->hasMethod('handle') ? 'handle' : '__invoke'],
            [
                'name' => $name,
                'id' => $id,
            ]
        );
    }
}
