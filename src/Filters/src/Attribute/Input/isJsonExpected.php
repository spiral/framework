<?php

declare(strict_types=1);

namespace Spiral\Filters\Attribute\Input;

use Spiral\Attributes\NamedArgumentConstructor;
use Spiral\Filters\InputInterface;

/**
 * When client expects application/json
 */
#[\Attribute(\Attribute::TARGET_PROPERTY), NamedArgumentConstructor]
final class IsJsonExpected extends Input
{
    public function __construct(
        public readonly bool $softMatch = false
    ) {
    }

    public function getValue(InputInterface $input, \ReflectionProperty $property): bool
    {
        return $input->getValue('isJsonExpected', $this->softMatch);
    }

    public function getSchema(\ReflectionProperty $property): string
    {
        return 'isJsonExpected';
    }
}
