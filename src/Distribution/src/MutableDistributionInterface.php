<?php

declare(strict_types=1);

namespace Spiral\Distribution;

interface MutableDistributionInterface extends DistributionInterface
{
    public function add(string $name, UriResolverInterface $resolver): void;
}
