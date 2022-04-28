<?php

declare(strict_types=1);

namespace Spiral\Filters\Attribute\Input;

use Psr\Http\Message\UriInterface;
use Spiral\Attributes\NamedArgumentConstructor;
use Spiral\Filters\InputInterface;

#[\Attribute(\Attribute::TARGET_PROPERTY), NamedArgumentConstructor]
final class Server extends Input
{
    /**
     * @param non-empty-string|null $key
     */
    public function __construct(
        public readonly ?string $key = null,
    ) {
    }

    /**
     * @return UriInterface
     */
    public function getValue(InputInterface $input, \ReflectionProperty $property): mixed
    {
        return $input->getValue('server', $this->key ?? $property->getName());
    }

    public function getSchema(\ReflectionProperty $property): string
    {
        return 'server:' . ($this->key ?? $property->getName());
    }
}
