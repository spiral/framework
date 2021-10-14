<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Jobs;

use Spiral\Core\ResolverInterface;
use Spiral\Jobs\Exception\JobException;

/**
 * Handler which can invoke itself.
 */
abstract class JobHandler implements HandlerInterface, SerializerInterface
{
    // default function with method injection
    protected const HANDLE_FUNCTION = 'invoke';

    /** @var ResolverInterface */
    protected $resolver;

    /**
     * @param ResolverInterface $resolver
     */
    public function __construct(ResolverInterface $resolver)
    {
        $this->resolver = $resolver;
    }

    /**
     * @inheritdoc
     */
    public function handle(string $jobType, string $jobID, string $payload): void
    {
        $payloadData = $this->unserialize($jobType, $payload);

        $method = new \ReflectionMethod($this, static::HANDLE_FUNCTION);
        $method->setAccessible(true);

        try {
            $parameters = array_merge(['payload' => $payloadData, 'id' => $jobID], $payloadData);
            $method->invokeArgs($this, $this->resolver->resolveArguments($method, $parameters));
        } catch (\Throwable $e) {
            throw new JobException(
                sprintf('[%s] %s', get_class($this), $e->getMessage()),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * @inheritdoc
     */
    public function serialize(string $jobType, array $payload): string
    {
        return json_encode($payload);
    }

    /**
     * @param string $jobType
     * @param string $payload
     * @return array
     */
    public function unserialize(string $jobType, string $payload): array
    {
        return json_decode($payload, true);
    }
}
