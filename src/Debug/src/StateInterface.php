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
    /**
     * @param array $tags
     */
    public function setTags(array $tags): void;

    /**
     * @param string $key
     * @param string $value
     */
    public function setTag(string $key, string $value): void;

    /**
     * Get current key-value description.
     *
     * @return array
     */
    public function getTags(): array;

    /**
     * @param array $extras
     */
    public function setVariables(array $extras): void;

    /**
     * @param string $key
     * @param        $value
     */
    public function setVariable(string $key, $value): void;

    /**
     * Get current state metadata. Arbitrary array form.
     *
     * @return array
     */
    public function getVariables(): array;

    /**
     * @param LogEvent ...$events
     */
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
