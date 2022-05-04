<?php

declare(strict_types=1);

namespace Spiral\Filters\Attribute\Input;

use Spiral\Attributes\NamedArgumentConstructor;
use Spiral\Filters\InputInterface;

#[\Attribute(\Attribute::TARGET_PROPERTY), NamedArgumentConstructor]
final class BearerToken extends Input
{

    public function getValue(InputInterface $input, \ReflectionProperty $property): mixed
    {
        return $input->getValue($this->getSchema($property));
    }

    public function getSchema(\ReflectionProperty $property): string
    {
        return 'bearerToken';
    }
}
