<?php

declare(strict_types=1);

namespace Spiral\Filters\Attribute\Input;

use Psr\Http\Message\UploadedFileInterface;
use Spiral\Attributes\NamedArgumentConstructor;
use Spiral\Filters\InputInterface;

#[\Attribute(\Attribute::TARGET_PROPERTY), NamedArgumentConstructor]
class File extends Input
{
    /**
     * @param non-empty-string|null $key
     */
    public function __construct(
        public readonly ?string $key = null,
    ) {
    }

    public function getValue(InputInterface $input, \ReflectionProperty $property): ?UploadedFileInterface
    {
        return $input->getValue('file', $this->getKey($property));
    }

    public function getSchema(\ReflectionProperty $property): string
    {
        return 'file:' . $this->getKey($property);
    }
}
