<?php

declare(strict_types=1);

namespace Spiral\Filters\Attribute\Input;

use Spiral\Attributes\NamedArgumentConstructor;
use Spiral\Filters\InputInterface;

/**
 * Sets property value with the current page path.
 */
#[\Attribute(\Attribute::TARGET_PROPERTY), NamedArgumentConstructor]
final class Path extends AbstractInput
{
    /**
     * @see \Spiral\Http\Request\InputManager::path() from {@link https://github.com/spiral/http}
     */
    public function getValue(InputInterface $input, \ReflectionProperty $property): string
    {
        return $input->getValue($this->getSchema($property));
    }

    public function getSchema(\ReflectionProperty $property): string
    {
        return 'path';
    }
}
