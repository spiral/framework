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

interface QueueInterface
{
    /**
     * Schedule job of a given type.
     *
     * @param string       $jobType
     * @param array        $payload
     * @param Options|null $options
     * @return string
     *
     * @throws JobException
     */
    public function push(string $jobType, array $payload = [], Options $options = null): string;
}
