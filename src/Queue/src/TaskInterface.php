<?php

declare(strict_types=1);

namespace Spiral\Queue;

interface TaskInterface
{
    /**
     * Returns payload of the task/job.
     */
    public function getPayload(): mixed;

    /**
     * Returns the name of the task/job.
     *
     * @return non-empty-string
     */
    public function getName(): string;

    /**
     * Returns headers of the task/job.
     *
     * @return array<non-empty-string, array<string>>
     */
    public function getHeaders(): array;

    /**
     * Returns the name of the queue.
     *
     * @return non-empty-string
     */
    public function getQueue(): string;

    /**
     * Returns the unique identifier of the task in the queue.
     *
     * @return non-empty-string
     */
    public function getId(): string;
}
