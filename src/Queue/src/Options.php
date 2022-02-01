<?php

declare(strict_types=1);

namespace Spiral\Queue;

final class Options implements OptionsInterface, \JsonSerializable
{
    /** @var int|null */
    private $delay;

    /** @var string|null */
    private $queue;

    public function withQueue(string $queue): self
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

    /**
     * @return array|mixed
     */
    #[\ReturnTypeWillChange]
    public function jsonSerialize()
    {
        return [
            'delay' => $this->delay,
            'queue' => $this->queue,
        ];
    }

    public static function delayed(int $delay): Options
    {
        $options = new self();
        $options->delay = $delay;

        return $options;
    }

    public static function onQueue(string $queue): Options
    {
        $options = new self();
        $options->queue = $queue;

        return $options;
    }
}
