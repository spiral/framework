<?php

declare(strict_types=1);

namespace Spiral\Filters\Attribute\Input;

use Spiral\Attributes\NamedArgumentConstructor;
use Spiral\Filters\InputInterface;

/**
 * If X-Requested-With set as xmlhttprequest
 */
#[\Attribute(\Attribute::TARGET_PROPERTY), NamedArgumentConstructor]
final class IsAjax extends Input
{
    public function getValue(InputInterface $input, \ReflectionProperty $property): bool
    {
        return $input->getValue('isAjax');
    }

    public function getSchema(\ReflectionProperty $property): string
    {
        return 'isAjax';
    }
}
