<?php

declare(strict_types=1);

namespace Spiral\Telemetry;

use Spiral\Core\FactoryInterface;

final class TracerFactory implements TracerFactoryInterface
{
    public function __construct(
        private readonly FactoryInterface $factory
    ) {
    }

    public function fromContext(mixed $context): TracerInterface
    {
        return $this->factory->make(TracerInterface::class)
            ->withContext($context);
    }
}
