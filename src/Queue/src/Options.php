<?php

declare(strict_types=1);

namespace Spiral\Queue;

class Options implements OptionsInterface, \JsonSerializable
{
    private ?int $delay = null;
    private ?string $queue = null;
    private array $context = [];

    public function withContext(array $context): self
    {
        $options = clone $this;
        $options->context = $context;

        return $options;
    }

    public function withQueue(?string $queue): self
    {
        $options = clone $this;
        $options->queue = $queue;

        return $options;
    }

    public function getQueue(): ?string
    {
        return $this->queue;
    }

    public function withDelay(?int $delay): self
    {
        $options = clone $this;
        $options->delay = $delay;

        return $options;
    }

    public function getDelay(): ?int
    {
        return $this->delay;
    }

    public function getContext(): array
    {
        return $this->context;
    }

    public function jsonSerialize(): array
    {
        return [
            'delay' => $this->delay,
            'queue' => $this->queue,
            'context' => $this->context,
        ];
    }

    public static function delayed(int $delay): Options
    {
        $options = new self();
        $options->delay = $delay;

        return $options;
    }

    public static function onQueue(?string $queue): Options
    {
        $options = new self();
        $options->queue = $queue;

        return $options;
    }
}
