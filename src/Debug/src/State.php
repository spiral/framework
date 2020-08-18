<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Debug;

use Spiral\Debug\Exception\StateException;
use Spiral\Logger\Event\LogEvent;

/**
 * Describes current process state.
 */
final class State implements StateInterface
{
    /** @var array */
    private $tags = [];

    /** @var array */
    private $extras = [];

    /** @var array */
    private $logEvents = [];

    /**
     * @param array $tags
     */
    public function setTags(array $tags): void
    {
        $setTags = [];
        foreach ($tags as $key => $value) {
            if (!is_string($value)) {
                throw new StateException(sprintf(
                    'Invalid tag value, string expected got %s',
                    is_object($value) ? get_class($value) : gettype($value)
                ));
            }

            $setTags[(string)$key] = $value;
        }

        $this->tags = $setTags;
    }

    /**
     * @param string $key
     * @param string $value
     */
    public function setTag(string $key, string $value): void
    {
        $this->tags[$key] = $value;
    }

    /**
     * Get current key-value description.
     *
     * @return array
     */
    public function getTags(): array
    {
        return $this->tags;
    }

    /**
     * @param array $extras
     */
    public function setVariables(array $extras): void
    {
        $this->extras = $extras;
    }

    /**
     * @param string $key
     * @param        $value
     */
    public function setVariable(string $key, $value): void
    {
        $this->extras[$key] = $value;
    }

    /**
     * Get current state metadata. Arbitrary array form.
     *
     * @return array
     */
    public function getVariables(): array
    {
        return $this->extras;
    }

    /**
     * @param LogEvent ...$events
     */
    public function addLogEvent(LogEvent ...$events): void
    {
        $this->logEvents = array_merge($this->logEvents, $events);
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
