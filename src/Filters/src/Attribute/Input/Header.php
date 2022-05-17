<?php

declare(strict_types=1);

namespace Spiral\Filters\Attribute\Input;

use Spiral\Attributes\NamedArgumentConstructor;
use Spiral\Filters\InputInterface;

/**
 * Sets property value from the request [headers] bag.
 */
#[\Attribute(\Attribute::TARGET_PROPERTY), NamedArgumentConstructor]
final class Header extends AbstractInput
{
    /**
     * @param non-empty-string|null $key
     */
    public function __construct(
        public readonly ?string $key = null,
    ) {
    }

    /**
     * @see \Spiral\Http\Request\InputManager::header() from {@link https://github.com/spiral/http}
     */
    public function getValue(InputInterface $input, \ReflectionProperty $property): mixed
    {
        return $input->getValue('header', $this->getKey($property));
    }

    public function getSchema(\ReflectionProperty $property): string
    {
        return 'header:' . $this->getKey($property);
    }
}
