<?php

declare(strict_types=1);

namespace Spiral\Queue\Job;

use Spiral\Core\ResolverInterface;
use Spiral\Queue\HandlerInterface;
use Spiral\Queue\Exception\InvalidArgumentException;

final class ObjectJob implements HandlerInterface
{
    /** @var ResolverInterface  */
    private $resolver;

    public function __construct(ResolverInterface $resolver)
    {
        $this->resolver = $resolver;
    }

    public function handle(string $name, string $id, array $payload): void
    {
        if (! isset($payload['object'])) {
            throw new InvalidArgumentException('Payload `object` key is required.');
        }

        if (! is_object($payload['object'])) {
            throw new InvalidArgumentException('Payload `object` key value type should be an object.');
        }

        $job = $payload['object'];
        $handler = new \ReflectionClass($job);

        $method = $handler->getMethod(
            $handler->hasMethod('handle') ? 'handle' : '__invoke'
        );

        $args = $this->resolver->resolveArguments($method, [
            'name' => $name,
            'id' => $id,
        ]);

        $method->invokeArgs($job, $args);
    }
}
