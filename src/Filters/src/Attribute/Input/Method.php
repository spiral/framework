<?php

declare(strict_types=1);

namespace Spiral\Filters\Attribute\Input;

use Spiral\Attributes\NamedArgumentConstructor;
use Spiral\Filters\InputInterface;

/**
 * Http method (GET, POST, ...)
 */
#[\Attribute(\Attribute::TARGET_PROPERTY), NamedArgumentConstructor]
final class Method extends Input
{
    public function getValue(InputInterface $input, \ReflectionProperty $property): mixed
    {
        return $input->getValue('method');
    }

    public function getSchema(\ReflectionProperty $property): string
    {
        return 'method';
    }
}
