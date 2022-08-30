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

    public function getTime(): \DateTimeInterface
    {
        return $this->time;
    }

    public function getChannel(): string
    {
        return $this->channel;
    }

    public function getLevel(): string
    {
        return $this->level;
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    public function getContext(): array
    {
        return $this->context;
    }
}
