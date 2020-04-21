<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Jobs;

class JobRegistry implements HandlerRegistryInterface, SerializerRegistryInterface
{
    public function getHandler(string $jobType): HandlerInterface
    {
        // TODO: Implement getHandler() method.
    }

    public function getSerializer(string $jobType): SerializerInterface
    {
        // TODO: Implement getSerializer() method.
    }
}
