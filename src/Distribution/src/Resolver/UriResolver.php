<?php

declare(strict_types=1);

namespace Spiral\Distribution\Resolver;

use Spiral\Distribution\UriResolverInterface;

abstract class UriResolver implements UriResolverInterface
{
    protected function concat(string $file, ?string $prefix): string
    {
        if ($prefix === null) {
            return $file;
        }

        return \trim($prefix, '/') . '/' . \trim($file, '/');
    }
}
