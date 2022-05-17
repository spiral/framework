<?php

declare(strict_types=1);

namespace Spiral\Filters\Attribute\Input;

use Psr\Http\Message\UploadedFileInterface;
use Spiral\Attributes\NamedArgumentConstructor;
use Spiral\Filters\InputInterface;

/**
 * Sets property value from the request [files] bag.
 */
#[\Attribute(\Attribute::TARGET_PROPERTY), NamedArgumentConstructor]
class File extends AbstractInput
{
    /**
     * @param non-empty-string|null $key
     */
    public function __construct(
        public readonly ?string $key = null,
    ) {
    }

    /**
     * @see \Spiral\Http\Request\InputManager::file() from {@link https://github.com/spiral/http}
     */
    public function getValue(InputInterface $input, \ReflectionProperty $property): ?UploadedFileInterface
    {
        return $input->getValue('file', $this->getKey($property));
    }

    public function getSchema(\ReflectionProperty $property): string
    {
        return 'file:' . $this->getKey($property);
    }
}
