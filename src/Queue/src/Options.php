<?php

declare(strict_types=1);

namespace Spiral\Queue;

class Options implements OptionsInterface, \JsonSerializable
{
    /**
     * @var 0|positive-int
     */
    private int $priority = 0;
    private ?int $delay = null;
    private ?string $queue = null;
    private bool $autoAck = false;
    /**
     * @var array<non-empty-string, array<string>>
     */
    private array $headers = [];

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

    public function withPriority(int $priority): self
    {
        $options = clone $this;
        $options->priority = $priority;

        return $options;
    }

    public function getPriority(): int
    {
        return $this->priority;
    }

    public function autoAck(bool $autoAck = true): self
    {
        $options = clone $this;
        $options->autoAck = $autoAck;

        return $options;
    }

    public function isAutoAck(): bool
    {
        return $this->autoAck;
    }

    /**
     * @return array<non-empty-string, array<string>>
     */
    public function getHeaders(): array
    {
        return $this->headers;
    }

    /**
     * @param non-empty-string $name Header field name.
     */
    public function hasHeader(string $name): bool
    {
        return isset($this->headers[$name]) && \count($this->headers[$name]) > 0;
    }

    /**
     * @param non-empty-string $name
     * @return array<string>
     */
    public function getHeader(string $name): array
    {
        return $this->headers[$name] ?? [];
    }

    /**
     * @param non-empty-string $name
     */
    public function getHeaderLine(string $name): string
    {
        return \implode(',', $this->getHeader($name));
    }

    /**
     * @param non-empty-string $name
     * @param non-empty-string|iterable<non-empty-string> $value
     */
    public function withHeader(string $name, $value): self
    {
        \assert($name !== '', 'Precondition [name !== ""] failed');

        $value = \is_iterable($value) ? $value : [$value];

        $self = clone $this;
        $self->headers[$name] = [];

        foreach ($value as $item) {
            $self->headers[$name][] = (string)$item;
        }

        return $self;
    }

    /**
     * @param non-empty-string $name
     * @param non-empty-string|iterable<non-empty-string> $value
     */
    public function withAddedHeader(string $name, $value): self
    {
        \assert($name !== '', 'Precondition [name !== ""] failed');

        /** @var iterable<non-empty-string> $value */
        $value = \is_iterable($value) ? $value : [$value];

        /** @var array<non-empty-string> $headers */
        $headers = $this->headers[$name] ?? [];

        foreach ($value as $item) {
            $headers[] = $item;
        }

        return $this->withHeader($name, $headers);
    }

    /**
     * @param non-empty-string $name
     */
    public function withoutHeader(string $name): self
    {
        \assert($name !== '', 'Precondition [name !== ""] failed');

        if (!isset($this->headers[$name])) {
            return $this;
        }

        $self = clone $this;
        unset($self->headers[$name]);
        return $self;
    }

    public function jsonSerialize(): array
    {
        return [
            'delay' => $this->delay,
            'queue' => $this->queue,
            'priority' => $this->priority,
            'autoAck' => $this->autoAck,
            'headers' => $this->headers,
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
