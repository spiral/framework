<?php

declare(strict_types=1);

namespace Spiral\Filters\Attribute\Input;

use Spiral\Attributes\NamedArgumentConstructor;
use Spiral\Filters\InputInterface;

/**
 * Sets property value true/false if X-Requested-With set as xmlhttprequest
 */
#[\Attribute(\Attribute::TARGET_PROPERTY), NamedArgumentConstructor]
final class IsAjax extends AbstractInput
{
    /**
     * @see \Spiral\Http\Request\InputManager::isAjax() from {@link https://github.com/spiral/http}
     */
    public function getValue(InputInterface $input, \ReflectionProperty $property): bool
    {
        return $input->getValue($this->getSchema($property));
    }

    public function getSchema(\ReflectionProperty $property): string
    {
        return 'isAjax';
    }
}
