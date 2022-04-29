<?php

declare(strict_types=1);

namespace Spiral\Queue;

use Spiral\Queue\Exception\JobException;

interface HandlerRegistryInterface
{
    /**
     * Get handler for the given job type.
     *
     * @throws JobException
     */
    public function getHandler(string $jobType): HandlerInterface;
}
