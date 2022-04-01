<?php

declare(strict_types=1);

namespace Spiral\Logger\Event;

final class LogEvent
{
    public function __construct(
        private readonly \DateTimeInterface $time,
        private readonly string $channel,
        private readonly string $level,
        private readonly string $message,
        private readonly array $context = []
    ) {
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
