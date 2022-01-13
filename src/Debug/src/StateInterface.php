<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Debug;

use Spiral\Logger\Event\LogEvent;

interface StateInterface
{
    public function setTags(array $tags): void;

    public function setTag(string $key, string $value): void;

    /**
     * Get current key-value description.
     */
    public function getTags(): array;

    public function setVariables(array $extras): void;

    /**
     * @param        $value
     */
    public function setVariable(string $key, $value): void;

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
