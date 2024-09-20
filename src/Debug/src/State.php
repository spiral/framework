<?php

declare(strict_types=1);

namespace Spiral\Debug;

use Spiral\Debug\Exception\StateException;
use Spiral\Logger\Event\LogEvent;

/**
 * Describes current process state.
 */
final class State implements StateInterface
{
    private array $tags = [];
    private array $extras = [];
    private array $logEvents = [];

    /**
     * @param array<array-key, string> $tags
     */
    public function setTags(array $tags): void
    {
        $setTags = [];
        foreach ($tags as $key => $value) {
            if (!\is_string($value)) {
                throw new StateException(\sprintf(
                    'Invalid tag value, string expected got %s',
                    \get_debug_type($value)
                ));
            }

            $setTags[$key] = $value;
        }

        $this->tags = $setTags;
    }

    public function setTag(string $key, string $value): void
    {
        $this->tags[$key] = $value;
    }

    /**
     * Get current key-value description.
     */
    public function getTags(): array
    {
        return $this->tags;
    }

    public function setVariables(array $extras): void
    {
        $this->extras = $extras;
    }

    public function setVariable(string $key, mixed $value): void
    {
        $this->extras[$key] = $value;
    }

    /**
     * Get current state metadata. Arbitrary array form.
     */
    public function getVariables(): array
    {
        return $this->extras;
    }

    public function addLogEvent(LogEvent ...$events): void
    {
        $this->logEvents = \array_merge($this->logEvents, $events);
    }

    /**
     * @return LogEvent[]
     */
    public function getLogEvents(): array
    {
        return $this->logEvents;
    }

    /**
     * Reset the state.
     */
    public function reset(): void
    {
        $this->tags = [];
        $this->extras = [];
        $this->logEvents = [];
    }
}
