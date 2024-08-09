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
     * @param class-string<HandlerInterface>|non-empty-string $name
     * @param non-empty-string $id
     */
    public function handle(string $name, string $id, array $payload): void;
}
