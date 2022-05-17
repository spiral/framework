<?php

declare(strict_types=1);

namespace Spiral\Filters\Attribute\Input;

use Psr\Http\Message\UriInterface;
use Spiral\Attributes\NamedArgumentConstructor;
use Spiral\Filters\InputInterface;

/**
 * Sets property value with current page Uri in a form of Psr\Http\Message\UriInterface
 */
#[\Attribute(\Attribute::TARGET_PROPERTY), NamedArgumentConstructor]
final class Uri extends AbstractInput
{
    /**
     * @see \Spiral\Http\Request\InputManager::uri() from {@link https://github.com/spiral/http}
     */
    public function getValue(InputInterface $input, \ReflectionProperty $property): UriInterface
    {
        return $input->getValue($this->getSchema($property));
    }

    public function getSchema(\ReflectionProperty $property): string
    {
        return 'uri';
    }
}
