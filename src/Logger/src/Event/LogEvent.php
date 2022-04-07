<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Logger\Event;

use DateTimeInterface;
final class LogEvent
{
    private DateTimeInterface $time;

    private string $channel;

    private string $level;

    private string $message;

    private array $context;

    public function __construct(
        DateTimeInterface $time,
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

    public function getTime(): DateTimeInterface
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
