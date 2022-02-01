<?php

declare(strict_types=1);

namespace Spiral\Queue;

use Spiral\Core\InvokerInterface;
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

    /** @var InvokerInterface */
    protected $invoker;

    public function __construct(InvokerInterface $invoker)
    {
        $this->invoker = $invoker;
    }

    /**
     * @inheritdoc
     */
    public function handle(string $name, string $id, array $payload): void
    {
        try {
            $this->invoker->invoke(
                [$this, $this->getHandlerMethod()],
                \array_merge(['payload' => $payload, 'id' => $id], $payload)
            );
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
