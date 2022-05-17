<?php

declare(strict_types=1);

namespace Spiral\Filters\Attribute\Input;

use Spiral\Attributes\NamedArgumentConstructor;
use Spiral\Filters\InputInterface;

/**
 * Sets property value from the request [data] bag.
 */
#[\Attribute(\Attribute::TARGET_PROPERTY), NamedArgumentConstructor]
final class Post extends AbstractInput
{
    /**
     * @param non-empty-string|null $key
     */
    public function __construct(
        public readonly ?string $key = null,
    ) {
    }

    /**
     * @see \Spiral\Http\Request\InputManager::post() from {@link https://github.com/spiral/http}
     */
    public function getValue(InputInterface $input, \ReflectionProperty $property): mixed
    {
        return $input->getValue('post', $this->getKey($property));
    }

    public function getSchema(\ReflectionProperty $property): string
    {
        return 'post:' . $this->getKey($property);
    }
}
