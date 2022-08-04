<?php

declare(strict_types=1);

namespace Spiral\Queue;

use Spiral\Serializer\SerializerInterface;

interface SerializerRegistryInterface
{
    public function getSerializer(?string $jobType = null): SerializerInterface;
}
