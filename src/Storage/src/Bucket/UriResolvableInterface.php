<?php

declare(strict_types=1);

namespace Spiral\Storage\Bucket;

use Spiral\Distribution\UriResolverInterface;

interface UriResolvableInterface
{
    public function getUriResolver(): ?UriResolverInterface;

    public function withUriResolver(?UriResolverInterface $resolver): self;
}
