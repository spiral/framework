<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Jobs;

/**
 * Handles incoming jobs.
 */
interface HandlerInterface
{
    /**
     * Handle incoming job.
     *
     * @param string $jobType
     * @param string $jobID
     * @param string $payload
     */
    public function handle(string $jobType, string $jobID, string $payload): void;
}
