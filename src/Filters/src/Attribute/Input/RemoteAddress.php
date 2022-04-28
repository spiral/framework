<?php

declare(strict_types=1);

namespace Spiral\Filters\Attribute\Input;

use Spiral\Attributes\NamedArgumentConstructor;
use Spiral\Filters\InputInterface;

/**
 * User ip address.
 */
#[\Attribute(\Attribute::TARGET_PROPERTY), NamedArgumentConstructor]
final class RemoteAddress extends Input
{

    public function getValue(InputInterface $input, \ReflectionProperty $property): mixed
    {
        return $input->getValue('remoteAddress');
    }

    public function getSchema(\ReflectionProperty $property): string
    {
        return 'remoteAddress';
    }
}
