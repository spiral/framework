<?php

declare(strict_types=1);

namespace Spiral\App\Attribute;

use Spiral\Filters\Attribute\Setter;

#[\Attribute(\Attribute::TARGET_PROPERTY | \Attribute::IS_REPEATABLE)]
final class ExtendedSetter extends Setter
{
    public function __construct()
    {
        parent::__construct(static fn(mixed $value): int => (int) $value + 5);
    }
}
