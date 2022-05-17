<?php

declare(strict_types=1);

namespace Spiral\Filters\Attribute\Input;

use Spiral\Attributes\NamedArgumentConstructor;
use Spiral\Filters\InputInterface;

/**
 * Sets property value from the request [headers] bag with key [Authorization].
 */
#[\Attribute(\Attribute::TARGET_PROPERTY), NamedArgumentConstructor]
final class BearerToken extends AbstractInput
{
    /**
     * @see \Spiral\Http\Request\InputManager::bearerToken() from {@link https://github.com/spiral/http}
     */
    public function getValue(InputInterface $input, \ReflectionProperty $property): mixed
    {
        return $input->getValue($this->getSchema($property));
    }

    public function getSchema(\ReflectionProperty $property): string
    {
        return 'bearerToken';
    }
}
