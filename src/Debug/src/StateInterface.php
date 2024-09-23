<?php

declare(strict_types=1);

namespace Spiral\Debug;

use Spiral\Logger\Event\LogEvent;

interface StateInterface
{
    /**
     * @param array<array-key, string> $tags
     */
    public function setTags(array $tags): void;

    public function setTag(string $key, string $value): void;

    /**
     * Get current key-value description.
     */
    public function getTags(): array;

    public function setVariables(array $extras): void;

    public function setVariable(string $key, mixed $value): void;

    /**
     * Get current state metadata. Arbitrary array form.
     */
    public function getVariables(): array;

    public function addLogEvent(LogEvent ...$events): void;

    /**
     * @return LogEvent[]
     */
    public function getLogEvents(): array;

    /**
     * Reset the state.
     */
    public function reset(): void;
}
