<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Jobs;

interface SerializerRegistryInterface
{
    /**
     * @param string $jobType
     * @return SerializerInterface
     */
    public function getSerializer(string $jobType): SerializerInterface;
}
