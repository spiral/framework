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

    /**
     * @see \Spiral\Http\Request\InputManager::isJsonExpected() from {@link https://github.com/spiral/http}
     */
    public function getValue(InputInterface $input, \ReflectionProperty $property): bool
    {
        return $input->getValue($this->getSchema($property));
    }

    public function getSchema(\ReflectionProperty $property): string
    {
        return 'isJsonExpected';
    }
}
