<?php

declare(strict_types=1);

namespace Spiral\Queue;

use Spiral\Core\ResolverInterface;
use Spiral\Queue\Exception\JobException;

/**
 * Handler which can invoke itself.
 */
abstract class JobHandler implements HandlerInterface
{
    /**
     * Default function with method injection.
     *
     * @var string
     */
    protected const HANDLE_FUNCTION = 'invoke';

    /**
     * @var ResolverInterface
     */
    protected $resolver;

    public function __construct(ResolverInterface $resolver)
    {
        $this->resolver = $resolver;
    }

    /**
     * @inheritdoc
     */
    public function handle(string $name, string $id, array $payload): void
    {
        $method = new \ReflectionMethod($this, $this->getHandlerMethod());
        $method->setAccessible(true);

        try {
            $parameters = \array_merge(['payload' => $payload, 'id' => $id], $payload);
            $method->invokeArgs($this, $this->resolver->resolveArguments($method, $parameters));
        } catch (\Throwable $e) {
            $message = \sprintf('[%s] %s', \get_class($this), $e->getMessage());
            throw new JobException($message, (int)$e->getCode(), $e);
        }
    }

    protected function getHandlerMethod(): string
    {
        return static::HANDLE_FUNCTION;
    }
}
