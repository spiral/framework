<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Jobs;

use Spiral\Jobs\Exception\JobException;

/**
 * Resolves handler for a given job type.
 */
interface HandlerRegistryInterface
{
    /**
     * Get handler for the given job type.
     *
     * @param string $jobType
     * @return HandlerInterface
     *
     * @throws JobException
     */
    public function getHandler(string $jobType): HandlerInterface;
}
