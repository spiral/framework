<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Logger\Event;

final class LogEvent
{
    /** @var \DateTimeInterface */
    private $time;

    /** @var string */
    private $channel;

    /** @var string */
    private $level;

    /** @var string */
    private $message;

    /** @var array */
    private $context;

    /**
     * @param \DateTimeInterface $time
     * @param string             $channel
     * @param string             $level
     * @param string             $message
     * @param array              $context
     */
    public function __construct(
        \DateTimeInterface $time,
        string $channel,
        string $level,
        string $message,
        array $context = []
    ) {
        $this->time = $time;
        $this->channel = $channel;
        $this->level = $level;
        $this->message = $message;
        $this->context = $context;
    }

    /**
     * @return \DateTimeInterface
     */
    public function getTime(): \DateTimeInterface
    {
        return $this->time;
    }

    /**
     * @return string
     */
    public function getChannel(): string
    {
        return $this->channel;
    }

    /**
     * @return string
     */
    public function getLevel(): string
    {
        return $this->level;
    }

    /**
     * @return string
     */
    public function getMessage(): string
    {
        return $this->message;
    }

    /**
     * @return array
     */
    public function getContext(): array
    {
        return $this->context;
    }
}
