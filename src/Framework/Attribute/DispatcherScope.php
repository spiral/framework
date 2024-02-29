<?php

declare(strict_types=1);

namespace Spiral\Attribute;

/**
 * @internal
 */
#[\Attribute(\Attribute::TARGET_CLASS)]
final class DispatcherScope
{
    public function __construct(
        public readonly string|\BackedEnum $scope
    ) {
    }
}
