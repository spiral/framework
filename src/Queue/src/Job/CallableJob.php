<?php

declare(strict_types=1);

namespace Spiral\Queue\Job;

use Spiral\Core\ResolverInterface;
use Spiral\Queue\HandlerInterface;
use Spiral\Queue\Exception\InvalidArgumentException;

final class CallableJob implements HandlerInterface
{
    /** @var ResolverInterface */
    private $resolver;

    public function __construct(ResolverInterface $resolver)
    {
        $this->resolver = $resolver;
    }

    public function handle(string $name, string $id, array $payload): void
    {
        if (!isset($payload['callback'])) {
            throw new InvalidArgumentException('Payload `callback` key is required.');
        }

        if (!$payload['callback'] instanceof \Closure) {
            throw new InvalidArgumentException('Payload `callback` key value type should be a closure.');
        }

        $callback = $payload['callback'];

        $reflection = new \ReflectionFunction($callback);

        $reflection->invokeArgs(
            $this->resolver->resolveArguments($reflection, [
                'name' => $name,
                'id' => $id,
            ])
        );
    }
}
