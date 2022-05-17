<?php

declare(strict_types=1);

namespace Spiral\Filters\Attribute\Input;

use Spiral\Attributes\NamedArgumentConstructor;
use Spiral\Filters\InputInterface;

/**
 * Sets property value with client ip address.
 */
#[\Attribute(\Attribute::TARGET_PROPERTY), NamedArgumentConstructor]
final class RemoteAddress extends AbstractInput
{
    /**
     * @see \Spiral\Http\Request\InputManager::remoteAddress() from {@link https://github.com/spiral/http}
     */
    public function getValue(InputInterface $input, \ReflectionProperty $property): ?string
    {
        return $input->getValue($this->getSchema($property));
    }

    public function getSchema(\ReflectionProperty $property): string
    {
        return 'remoteAddress';
    }
}
