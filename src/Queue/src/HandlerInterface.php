<?php

declare(strict_types=1);

namespace Spiral\Queue;

/**
 * Handles incoming jobs.
 */
interface HandlerInterface
{
    /**
     * Handle incoming job.
     *
     * @param class-string<HandlerInterface> $name
     * @param non-empty-string $id
     * @param array $payload
     */
    public function handle(string $name, string $id, array $payload): void;
}
