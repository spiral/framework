<?php

declare(strict_types=1);

namespace Spiral\Queue;

final class Task implements TaskInterface
{
    /**
     * @param non-empty-string $id Unique identifier of the task in the queue.
     * @param non-empty-string $queue broker queue name.
     * @param non-empty-string $name name of the task/job.
     * @param mixed $payload payload of the task/job.
     * @param array<non-empty-string, array<string>> $headers headers of the task/job.
     */
    public function __construct(
        private readonly string $id,
        private readonly string $queue,
        private readonly string $name,
        private readonly mixed $payload,
        private readonly array $headers,
    ) {
    }

    public function getPayload(): mixed
    {
        return $this->payload;
    }

    /**
     * @return non-empty-string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return array<non-empty-string, array<string>>
     */
    public function getHeaders(): array
    {
        return $this->headers;
    }

    /**
     * @return non-empty-string
     */
    public function getQueue(): string
    {
        return $this->queue;
    }

    /**
     * @return non-empty-string
     */
    public function getId(): string
    {
        return $this->id;
    }
}
